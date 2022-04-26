<?php

namespace Makaira\OxidConnectEssential;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use Makaira\Import\Changes;
use Makaira\OxidConnectEssential\Repository\AbstractRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function addRepositoryMapping(AbstractRepository $repository)
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
        $changes           = array();
        $productRepository = $this->getRepositoryForType('product');
        $typeProduct       = $productRepository->getType();
        $variantRepository = $this->getRepositoryForType('variant');
        $typeVariant       = $variantRepository->getType();
        foreach ($result as $row) {
            try {
                $type     = $row['type'];
                $sequence = $row['sequence'];
                $id       = $row['id'];
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

                if ($typeVariant === $type && $parentId && $change->data) {
                    foreach ($change->data as $_key => $_data) {
                        if (in_array($_key, $this->propsExclude, false)) {
                            continue;
                        }
                        $nullValues =
                            isset($this->propsSpecial[ $_key ]) ? $this->propsSpecial[ $_key ] : $this->propsNullValues;
                        if (in_array($_key, $this->propsInclude, false) || in_array($_data, $nullValues, true)) {
                            $change->data->$_key = $this->parentProducts[ $parentId ]->data->$_key;
                        }
                    }
                    $change->data->attributeStr   = array_merge(
                        (array) $this->parentAttributes[ $parentId ]['attributeStr'],
                        $change->data->attributeStr
                    );
                    $change->data->attributeInt   = array_merge(
                        (array) $this->parentAttributes[ $parentId ]['attributeInt'],
                        $change->data->attributeInt
                    );
                    $change->data->attributeFloat = array_merge(
                        (array) $this->parentAttributes[ $parentId ]['attributeFloat'],
                        $change->data->attributeFloat
                    );
                    unset(
                        $change->data->tmpAttributeStr,
                        $change->data->tmpAttributeInt,
                        $change->data->tmpAttributeFloat
                    );
                }

                if ($typeProduct === $type) {
                    if (
                        true === $change->deleted ||
                        (isset($change->data->OXVARCOUNT) && 0 === $change->data->OXVARCOUNT) ||
                         $this->parentsPurchasable
                    ) {
                        $pChange = clone $change;

                        if (is_null($pChange->data)) {
                            $pChange->data = new \stdClass();
                        }

                        $pChange->data->isPseudo  = true;
                        $pChange->data->isVariant = true;

                        foreach ($this->propsDoNotClone as $_props) {
                            if (isset($pChange->data->$_props)) {
                                unset($pChange->data->$_props);
                            }
                        }
                        $pChange->data->parent = $id;
                        if (isset($pChange->data->OXPARENTID)) {
                            $pChange->data->OXPARENTID = $id;
                        }
                        $pChange->id       = md5($id . '.variant.new');
                        $pChange->data->id = $pChange->id;

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

    protected function setParentCache($parentId, $parentData)
    {
        $this->parentAttributes[ $parentId ] = [
            'attributeStr'   => $parentData->data->tmpAttributeStr,
            'attributeInt'   => $parentData->data->tmpAttributeInt,
            'attributeFloat' => $parentData->data->tmpAttributeFloat,
        ];
        $this->parentProducts[ $parentId ] = $parentData;
    }

    public function countChangesSince($since)
    {
        $result = $this->database->query(
            'SELECT
                COUNT(*) count
            FROM
                makaira_connect_changes
            WHERE
                makaira_connect_changes.sequence > :since',
            ['since' => $since ?: 0]
        );

        return $result[0]['count'];
    }

    protected function getRepositoryForType($type)
    {
        if (!isset($this->repositoryMapping[ $type ])) {
            throw new OutOfBoundsException("No repository defined for type " . $type);
        }

        return $this->repositoryMapping[ $type ];
    }

    /**
     * Mark an object as updated.
     *
     * @param string $type
     * @param string $id
     */
    public function touch($type, $id)
    {
        if (!$id) {
            return;
        }
        $this->database->execute($this->touchQuery, ['type' => $type, 'id' => $id]);
    }

    /**
     * Clean up changes list.
     *
     * @ignoreCodeCoverage
     */
    public function cleanup()
    {
        $this->database->execute($this->cleanupQuery);
    }

    /**
     * Add all items to the changes list.
     */
    public function touchAll($shopId = null)
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
