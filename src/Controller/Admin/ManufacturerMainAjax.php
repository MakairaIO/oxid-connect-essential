<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Doctrine\DBAL;
use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Psr\Container;

use function array_map;
use function implode;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class ManufacturerMainAjax extends ManufacturerMainAjax_parent
{
    use PSR12WrapperTrait;
    use SymfonyContainerTrait;

    /**
     * @return void
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\ConnectionException
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    public function addManufacturer()
    {
        $productIds = (array) $this->callPSR12Incompatible('_getActionIds', 'oxarticles.oxid');

        /** @var string $manufacturerId */
        $manufacturerId = Registry::getRequest()->getRequestParameter('synchoxid');

        if (Registry::getRequest()->getRequestParameter('all')) {
            /** @var string $productView */
            $productView = $this->callPSR12Incompatible('_getViewName', 'oxarticles');
            $changedIds  = (array) $this->callPSR12Incompatible(
                '_getAll',
                $this->callPSR12Incompatible(
                    '_addFilter',
                    "SELECT {$productView}.OXID, {$productView}.OXPARENTID " . $this->callPSR12Incompatible('_getQuery')
                )
            );
        } elseif (!empty($productIds)) {
            $changedIds = $this->addParentIds($productIds);
        }

        parent::addManufacturer();

        if (!empty($changedIds)) {
            $this->executeTouches($changedIds, $manufacturerId);
        }
    }

    /**
     * @param array $productIds
     *
     * @return array<string>
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    private function addParentIds(array $productIds): array
    {
        /** @var Connection $connection */
        $connection = $this->getSymfonyContainer()->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        /** @var string $productView */
        $productView = $this->callPSR12Incompatible('_getViewName', 'oxarticles');

        $sqlProductIds = implode(
            ',',
            array_map(static fn($objectId) => $connection->quote($objectId, DBAL\ParameterType::STRING), $productIds)
        );

        $query = "SELECT a.OXID, a.OXPARENTID FROM {$productView} a WHERE a.OXID IN ($sqlProductIds)";

        /** @var DBAL\Result $resultStatement */
        $resultStatement = $connection->executeQuery($query);

        /** @var array<string> $result */
        $result = $resultStatement->fetchAllAssociative();

        return $result;
    }

    /**
     * @param array       $productIds
     * @param string|null $manufacturerId
     *
     * @return void
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\ConnectionException
     */
    private function executeTouches(array $productIds, ?string $manufacturerId = null): void
    {
        $container = $this->getSymfonyContainer();

        /** @var RevisionRepository $revisionRepository */
        $revisionRepository = $container->get(RevisionRepository::class);

        if (!empty($productIds) && array_key_exists('OXID', $productIds)) {
            $revisionRepository->storeRevisions(
                array_map(
                    static fn($productIds) => new Revision(
                        $productIds['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT,
                        $productIds['OXID']
                    ),
                    $productIds
                )
            );
        }

        if (null !== $manufacturerId) {
            $revisionRepository->touchManufacturer($manufacturerId);
        }
    }

    /**
     * @return void
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\ConnectionException
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    public function removeManufacturer(): void
    {
        /** @var string $manufacturerId */
        $manufacturerId = Registry::getRequest()->getRequestParameter('oxid');

        /** @var Connection $connection */
        $connection = $this->getSymfonyContainer()->get(QueryBuilderFactoryInterface::class)
            ->create()
            ->getConnection();

        /** @var string $productView */
        $productView = $this->callPSR12Incompatible('_getViewName', 'oxarticles');

        if (Registry::getRequest()->getRequestParameter('all')) {
            /** @var string $oxidQuery */
            $oxidQuery = $this->callPSR12Incompatible('_getQuery');
            $query     = "SELECT {$productView}.OXID, {$productView}.OXPARENTID {$oxidQuery}";

            /** @var DBAL\Result $resultStatement */
            $resultStatement = $connection->executeQuery($query);
            $changedIds      = $resultStatement->fetchAllAssociative();
        } else {
            $productIds = (array) $this->callPSR12Incompatible('_getActionIds', 'oxarticles.oxid');
            $changedIds = $this->addParentIds($productIds);
        }

        parent::removeManufacturer();

        $this->executeTouches($changedIds, $manufacturerId);
    }
}
