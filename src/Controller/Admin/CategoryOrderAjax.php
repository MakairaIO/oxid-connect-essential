<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use Psr\Container;
use Doctrine\DBAL;

use function array_map;

class CategoryOrderAjax extends CategoryOrderAjax_parent
{
    use PSR12WrapperTrait;
    use SymfonyContainerTrait;

    /**
     * @var bool
     */
    private bool $isRemove = false;

    /**
     * @return null
     */
    public function remNewOrder()
    {
        $this->isRemove = true;
        parent::remNewOrder();
        $this->isRemove = false;

        return null;
    }

    /**
     * @param string $categoryId
     *
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\ConnectionException
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    protected function onCategoryChange($categoryId): void
    {
        parent::onCategoryChange($categoryId);

        if (null !== $categoryId) {
            $container = $this->getSymfonyContainer();

            /** @var Connection $connection */
            $connection = $this->getSymfonyContainer()->get(QueryBuilderFactoryInterface::class)
                ->create()
                ->getConnection();

            /** @var RevisionRepository $revisionRepository */
            $revisionRepository = $container->get(RevisionRepository::class);

            if ($this->isRemove) {
                $revisionRepository->touchCategory($categoryId);
            }

            /** @var string $categoryView */
            $categoryView = $this->callPSR12Incompatible('_getViewName', 'oxobject2category');

            /** @var string $productView */
            $productView = $this->callPSR12Incompatible('_getViewName', 'oxarticles');

            $query = "SELECT `o2c`.`OXOBJECTID`, `a`.`OXPARENTID`
            FROM `{$categoryView}` `o2c`
            LEFT JOIN `{$productView}` `a` ON `a`.`OXID` = `o2c`.`OXOBJECTID`
            WHERE `o2c`.`OXCATNID` = ?";

            /** @var Result $resultStatement */
            $resultStatement = $connection->executeQuery($query, [$categoryId]);
            $changedProducts = $resultStatement->fetchAllAssociative();

            /**
             * @param array<string> $changedProduct
             *
             * @return Revision
             */
            $buildRevision = static fn(array $changedProduct) => new Revision(
                $changedProduct['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT,
                $changedProduct['OXOBJECTID']
            );

            $revisionRepository->storeRevisions(
                array_map(
                    $buildRevision,
                    $changedProducts
                )
            );
        }
    }
}
