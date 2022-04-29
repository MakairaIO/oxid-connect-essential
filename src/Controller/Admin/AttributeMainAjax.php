<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\ParameterType;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Psr\Container\ContainerInterface;

use function array_map;

class AttributeMainAjax extends AttributeMainAjax_parent
{
    use PSR12WrapperTrait;
    use SymfonyContainerTrait;

    public function removeAttrArticle(): void
    {
        /** @var ContainerInterface $container */
        $container = $this->getSymfonyContainer();

        /** @var Connection $db */
        $db = $container->get(Connection::class);

        /** @var string $attributeView */
        $attributeView = $this->callPSR12Incompatible('_getViewName', 'oxobject2attribute');

        /** @var string $productView */
        $productView = $this->callPSR12Incompatible('_getViewName', 'oxarticles');

        if (Registry::getRequest()->getRequestParameter('all')) {
            /** @var string $oxidQuery */
            $oxidQuery = $this->callPSR12Incompatible('_getQuery');
            $query     = "SELECT {$attributeView}.`OXOBJECTID`, {$productView}.`OXPARENTID` {$oxidQuery}";

            /** @var Result $resultStatement */
            $resultStatement = $db->executeQuery($query);
            $changedProducts = $resultStatement->fetchAllAssociative();
        } else {
            $entryIds = (array) $this->callPSR12Incompatible('_getActionIds', 'oxobject2attribute.oxid');
            if (!empty($entryIds)) {
                $sqlEntryIds = implode(
                    ', ',
                    array_map(
                        static fn($entryId) => $db->quote($entryId, ParameterType::STRING),
                        $entryIds
                    )
                );

                $query = "SELECT o2a.OXOBJECTID, a.OXPARENTID
                    FROM `{$attributeView}` o2a
                    LEFT JOIN `{$productView}` a ON a.`OXID` = o2a.`OXOBJECTID`
                    WHERE o2a.`OXID` IN ({$sqlEntryIds})";

                /** @var Result $resultStatement */
                $resultStatement = $db->executeQuery($query);
                $changedProducts = $resultStatement->fetchAllAssociative();
            }
        }

        parent::removeAttrArticle();

        if (!empty($changedProducts)) {
            /** @var RevisionRepository $revisionRepository */
            $revisionRepository = $container->get(RevisionRepository::class);
            $revisionRepository->storeRevisions(
                array_map(
                    static fn($changedProduct) => new Revision(
                        $changedProduct['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT,
                        (string) $changedProduct['OXOBJECTID']
                    ),
                    $changedProducts
                )
            );
        }
    }
}
