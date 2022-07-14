<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL;
use Psr\Container;

use function array_map;
use function array_values;

class ArticleExtendAjax extends ArticleExtendAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * Sets selected category as a default
     */
    public function setAsDefault(): void
    {
        parent::setAsDefault();

        /** @var string|null $productId */
        $productId = Registry::getRequest()->getRequestParameter("oxid");

        if (null !== $productId) {
            /** @var ContainerInterface $container */
            $container = $this->getSymfonyContainer();

            /** @var RevisionRepository $revisionRepo */
            $revisionRepo = $container->get(RevisionRepository::class);
            $revisionRepo->touchProduct($productId);
        }
    }

    /**
     * Method is used for overloading to do additional actions.
     *
     * @param array<string> $categoriesToRemove
     * @param string        $productId
     *
     * @throws Container\ContainerExceptionInterface
     * @throws Container\NotFoundExceptionInterface
     * @throws DBAL\ConnectionException
     * @throws DBAL\Driver\Exception
     * @throws DBAL\Exception
     */
    public function onCategoriesRemoval($categoriesToRemove, $productId): void
    {
        parent::onCategoriesRemoval($categoriesToRemove, $productId);

        $container = $this->getSymfonyContainer();

        /** @var RevisionRepository $revisionRepo */
        $revisionRepo = $container->get(RevisionRepository::class);
        if (null !== $productId) {
            $revisionRepo->touchProduct($productId);
        }

        $revisionRepo->storeRevisions(
            array_map(
                static fn ($categoryId) => new Revision(Revision::TYPE_CATEGORY, $categoryId),
                array_values(array_filter((array) $categoriesToRemove))
            )
        );
    }
}
