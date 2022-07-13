<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL;

class ArticleCrossSellingAjax extends ArticleCrossSellingAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * @return void
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    public function removeArticleCross()
    {
        parent::removeArticleCross();

        /** @var string|null $productId */
        $productId = Registry::getRequest()->getRequestParameter('oxid');

        if (null !== $productId) {
            /** @var ContainerInterface $container */
            $container = $this->getSymfonyContainer();

            /** @var RevisionRepository $revisionRepo */
            $revisionRepo = $container->get(RevisionRepository::class);
            $revisionRepo->touchProduct($productId);
        }
    }
}
