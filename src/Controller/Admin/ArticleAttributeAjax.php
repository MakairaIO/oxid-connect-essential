<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArticleAttributeAjax extends ArticleAttributeAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * Trigger touch only for remove.
     *
     * @var bool
     */
    private bool $isRemove = false;

    public function removeAttr(): void
    {
        $this->isRemove = true;
        parent::removeAttr();
    }

    /**
     * Method is used to bind to attribute and article relation change action.
     *
     * @param ?string $articleId
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function onArticleAttributeRelationChange($articleId): void
    {
        parent::onArticleAttributeRelationChange($articleId);

        if (null !== $articleId && $this->isRemove) {
            /** @var ContainerInterface $container */
            $container = $this->getSymfonyContainer();

            /** @var RevisionRepository $revisionRepo */
            $revisionRepo = $container->get(RevisionRepository::class);
            $revisionRepo->touchProduct($articleId);

            $this->isRemove = false;
        }
    }
}
