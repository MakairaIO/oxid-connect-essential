<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArticleCrossSellingAjax extends ArticleCrossSellingAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * @return void
     */
    public function removeArticleCross()
    {
        parent::removeArticleCross();

        $productId = Registry::getRequest()->getRequestParameter('oxid');

        /** @var ContainerInterface $container */
        $container = $this->getSymfonyContainer();

        /** @var RevisionRepository $revisionRepo */
        $revisionRepo = $container->get(RevisionRepository::class);
        $revisionRepo->touchProduct($productId);
    }
}
