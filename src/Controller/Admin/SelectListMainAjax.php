<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Psr\Container\ContainerInterface;

use function array_map;
use function implode;

class SelectListMainAjax extends SelectListMainAjax_parent
{
    use PSR12WrapperTrait;
    use SymfonyContainerTrait;

    /**
     * Removes article from Selection list
     */
    public function removeArtFromSel()
    {
        /** @var ContainerInterface $container */
        $container             = $this->getSymfonyContainer();
        $db                    = $container->get(Connection::class);
        $articleSelectListView = $this->callPSR12Incompatible('_getViewName', 'oxobject2selectlist');
        $productView           = $this->callPSR12Incompatible('_getViewName', 'oxarticles');

        if (Registry::getRequest()->getRequestParameter('all')) {
            $oxidQuery = $this->callPSR12Incompatible('_getQuery');
            $query     = "SELECT {$articleSelectListView}.`OXOBJECTID`, {$productView}.`OXPARENTID` {$oxidQuery}";

            $changedProducts = $db->executeQuery($query)->fetchAllAssociative();
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
                    FROM `{$articleSelectListView}` o2a
                    LEFT JOIN `{$productView}` a ON a.`OXID` = o2a.`OXOBJECTID`
                    WHERE o2a.`OXID` IN ({$sqlEntryIds})";

                $changedProducts = $db->executeQuery($query)->fetchAllAssociative();
            }
        }

        parent::removeArtFromSel();

        if (!empty($changedProducts)) {
            $revisionRepository = $container->get(RevisionRepository::class);
            $revisionRepository->storeRevisions(
                array_map(
                    static fn($changedProduct) => new Revision(
                        $changedProduct['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT,
                        $changedProduct['OXOBJECTID']
                    ),
                    $changedProducts
                )
            );
        }
    }
}
