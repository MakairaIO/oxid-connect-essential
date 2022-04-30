<?php

namespace Makaira\OxidConnectEssential;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Makaira\Import\Changes;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Makaira\OxidConnectEssential\Repository\ProductRepository;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function get_object_vars;

/**
 * Class Repository
 *
 * @package Makaira\Connect
 * @SuppressWarnings(PHPMD)
 */
class Repository
{
    /**
     * @var string
     */
    protected string $cleanupQuery = '
        DELETE FROM
          makaira_connect_changes
        WHERE
          changed < DATE_SUB(NOW(), INTERVAL 1 DAY);
    ';

    /**
     * @var string
     */
    protected string $selectQuery = '
        SELECT
            makaira_connect_changes.sequence,
            makaira_connect_changes.oxid AS `id`,
            makaira_connect_changes.type
        FROM
            makaira_connect_changes
        WHERE
            makaira_connect_changes.sequence > :since
        ORDER BY
            sequence ASC
        LIMIT :limit
    ';

    /**
     * @var string
     */
    protected string $touchQuery = '
        REPLACE INTO
          makaira_connect_changes
        (OXID, TYPE, CHANGED)
          VALUES
        (:id, :type, NOW());
    ';

    /**
     * @var array
     */
    private array $parentProducts = [];

    /**
     * @var array
     */
    private array $parentAttributes = [];

    /**
     * @var array
     */
    private array $propsExclude = [
        'attributes',
        'attributeStr',
        'attributeInt',
        'attributeFloat',
        'tmpAttributeStr',
        'tmpAttributeInt',
        'tmpAttributeFloat',
    ];

    /**
     * @var array
     */
    private array $propsInclude = [
        'OXISSEARCH',
    ];

    /**
     * @var array
     */
    private array $propsDoNotClone = [
        'attributes',
        'tmpAttributeStr',
        'tmpAttributeInt',
        'tmpAttributeFloat',
    ];

    /**
     * @var array
     */
    private array $propsNullValues = [null, '', []];

    /**
     * @var array
     */
    private array $propsSpecial = [];

    /**
     * @var array
     */
    private array $repositoryMapping = [];

    private Connection $database;

    private ?bool $parentsPurchasable;

    /**
     * Repository constructor.
     *
     * @param Connection                   $database
     * @param EventDispatcherInterface     $dispatcher
     * @param iterable<AbstractRepository> $repositories
     * @param bool                         $parentsPurchasable
     */
    public function __construct(
        Connection $database,
        EventDispatcherInterface $dispatcher,
        iterable $repositories,
        ?bool $parentsPurchasable
    ) {
        $this->database           = $database;
        $this->parentsPurchasable = (bool) $parentsPurchasable;

        foreach ($repositories as $repository) {
            $this->repositoryMapping[$repository->getType()] = $repository;
        }

        $dispatcher->dispatch('makaira.connect.repository', new Event\RepositoryCollectEvent($this));
    }

    /**
     * @param AbstractRepository $repository
     *
     * @return void
     */
    public function addRepositoryMapping(AbstractRepository $repository): void
    {
        $this->repositoryMapping[$repository->getType()] = $repository;
    }

    /**
     * Fetch and serialize changes.
     *
     * @param int $since Sequence offset
     * @param int $limit Fetch limit
     *
     * @return array
     * @throws OutOfBoundsException
     * @throws DBALDriverException
     * @throws DBALException
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    public function getChangesSince(int $since, int $limit = 50): array
    {
        $prepared = $this->database->prepare($this->selectQuery);
        $prepared->bindValue('since', $since, ParameterType::INTEGER);
        $prepared->bindValue('limit', $limit, ParameterType::INTEGER);
        $prepared->execute();

        return $this->getChangesFromList($prepared->fetchAllAssociative(), $since, $limit);
    }

    /**
     * Fetch and serialize changes from list.
     *
     * @param array $result
     * @param int   $since Sequence offset
     * @param int   $limit Fetch limit
     *
     * @return array
     * @throws OutOfBoundsException
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    public function getChangesFromList(array $result, int $since, int $limit = 50): array
    {
        $changes           = [];
        /** @var ProductRepository $productRepository */
        $productRepository = $this->getRepositoryForType('product');
        $typeProduct       = $productRepository->getType();
        $variantRepository = $this->getRepositoryForType('variant');
        $typeVariant       = $variantRepository->getType();
        foreach ($result as $row) {
            try {
                $type     = $row['type'];
                $sequence = (int) $row['sequence'];
                $id       = (string) $row['id'];
                $parentId = null;

                if ($typeVariant === $type) {
                    $parentId = $productRepository->getParentId($id);

                    if ($parentId && !isset($this->parentProducts[ $parentId ])) {
                        $change = $productRepository->get($parentId);
                        $this->setParentCache($parentId, $change);
                        unset($change);
                    }
                }

                $change           = $this->getRepositoryForType($type)->get($id);
                $change->sequence = $sequence;

                if ($typeVariant === $type && $parentId && $change->data instanceof Type) {
                    $dataKeys = get_object_vars($change->data);
                    foreach ($dataKeys as $key) {
                        if (in_array($key, $this->propsExclude, false)) {
                            continue;
                        }
                        $data = $change->data->{$key};
                        $nullValues = $this->propsSpecial[$key] ?? $this->propsNullValues;
                        if (in_array($key, $this->propsInclude, false) || in_array($data, $nullValues, true)) {
                            $change->data->{$key} = $this->parentProducts[$parentId]->data->{$key};
                        }
                    }
                    if ($change->data instanceof Product) {
                        $change->data->attributeStr   = array_merge(
                            (array) $this->parentAttributes[$parentId]['attributeStr'],
                            $change->data->attributeStr
                        );
                        $change->data->attributeInt   = array_merge(
                            (array) $this->parentAttributes[$parentId]['attributeInt'],
                            $change->data->attributeInt
                        );
                        $change->data->attributeFloat = array_merge(
                            (array) $this->parentAttributes[$parentId]['attributeFloat'],
                            $change->data->attributeFloat
                        );
                        unset(
                            $change->data->tmpAttributeStr,
                            $change->data->tmpAttributeInt,
                            $change->data->tmpAttributeFloat
                        );
                    }
                }

                if ($typeProduct === $type) {
                    if (
                        true === $change->deleted ||
                        (isset($change->data->OXVARCOUNT) && 0 === $change->data->OXVARCOUNT) ||
                        $this->parentsPurchasable
                    ) {
                        $pChange = clone $change;

                        if (is_null($pChange->data)) {
                            $pChange->data = new Product();
                        }

                        /** @var Product $productType */
                        $productType = $pChange->data;

                        $productType->isPseudo  = true;
                        $productType->isVariant = true;

                        foreach ($this->propsDoNotClone as $_props) {
                            if (isset($productType->$_props)) {
                                unset($productType->$_props);
                            }
                        }
                        $productType->parent = $id;
                        if (isset($pChange->data->OXPARENTID)) {
                            $productType->additionalData['OXPARENTID'] = $id;
                        }
                        $pChange->id     = md5($id . '.variant.new');
                        $productType->id = $pChange->id;

                        $pChange->sequence = $sequence;
                        $pChange->type     = $typeVariant;

                        $changes[] = $pChange;
                        unset($pChange);
                    } else {
                        $this->setParentCache($id, $change);
                    }
                    unset(
                        $change->data->tmpAttributeStr,
                        $change->data->tmpAttributeInt,
                        $change->data->tmpAttributeFloat
                    );
                }

                $changes[] = $change;
                unset($change);
            } catch (OutOfBoundsException $e) {
                // catch no repository found exception
            }
        }

        return [
            'since'          => $since,
            'count'          => count($changes),
            'requestedCount' => $limit,
            'changes'        => $changes,
        ];
    }

    protected function setParentCache(string $parentId, Change $parentData): void
    {
        /** @var Product $changeData */
        $changeData = $parentData->data;

        $this->parentAttributes[$parentId] = [
            'attributeStr'   => $changeData->tmpAttributeStr,
            'attributeInt'   => $changeData->tmpAttributeInt,
            'attributeFloat' => $changeData->tmpAttributeFloat,
        ];
        $this->parentProducts[$parentId] = $parentData;
    }

    public function countChangesSince(int $since): int
    {
        /** @var Result $result */
        $result = $this->database->executeQuery(
            'SELECT
                COUNT(*) count
            FROM
                makaira_connect_changes
            WHERE
                makaira_connect_changes.sequence > :since',
            ['since' => $since ?: 0]
        );

        /** @var string $count */
        $count = $result->fetchOne();

        return (int) $count;
    }

    protected function getRepositoryForType(string $type): AbstractRepository
    {
        if (!isset($this->repositoryMapping[$type])) {
            throw new OutOfBoundsException("No repository defined for type " . $type);
        }

        return $this->repositoryMapping[$type];
    }

    /**
     * Mark an object as updated.
     *
     * @param string $type
     * @param string $id
     */
    public function touch(string $type, string $id): void
    {
        if (!$id) {
            return;
        }
        $this->database->executeQuery($this->touchQuery, ['type' => $type, 'id' => $id]);
    }

    /**
     * Clean up changes list.
     *
     * @ignoreCodeCoverage
     */
    public function cleanup(): void
    {
        $this->database->executeQuery($this->cleanupQuery);
    }

    /**
     * Add all items to the changes list.
     */
    public function touchAll(int $shopId = null): void
    {
        $this->cleanUp();

        /**
         * @var string              $type
         * @var AbstractRepository $repository
         */
        foreach ($this->repositoryMapping as $type => $repository) {
            foreach ($repository->getAllIds($shopId) as $id) {
                $this->touch($type, $id);
            }
        }
    }
}
