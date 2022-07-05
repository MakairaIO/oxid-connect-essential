<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;

use function array_map;

class ArticleExtendAjax extends ArticleExtendAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * Sets selected category as a default
     */
    public function setAsDefault(): void
    {
        parent::setAsDefault();

        /** @var string $productId */
        $productId = Registry::getRequest()->getRequestParameter("oxid");

        /** @var ContainerInterface $container */
        $container = $this->getSymfonyContainer();

        /** @var RevisionRepository $revisionRepo */
        $revisionRepo = $container->get(RevisionRepository::class);
        $revisionRepo->touchProduct($productId);
    }

    /**
     * Method is used for overloading to do additional actions.
     *
     * @param array<string> $categoriesToRemove
     * @param string        $productId
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function onCategoriesRemoval($categoriesToRemove, $productId): void
    {
        parent::onCategoriesRemoval($categoriesToRemove, $productId);

        $container = $this->getSymfonyContainer();

        /** @var RevisionRepository $revisionRepo */
        $revisionRepo = $container->get(RevisionRepository::class);
        $revisionRepo->touchProduct($productId);

        $revisionRepo->storeRevisions(
            array_map(
                static fn ($categoryId) => new Revision(Revision::TYPE_CATEGORY, $categoryId),
                $categoriesToRemove
            )
        );
    }
}
