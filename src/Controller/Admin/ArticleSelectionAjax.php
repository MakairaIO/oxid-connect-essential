<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use Makaira\OxidConnectEssential\Entity\RevisionRepository;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArticleSelectionAjax extends ArticleSelectionAjax_parent
{
    use SymfonyContainerTrait;

    /**
     * @var bool
     */
    private bool $isRemove = false;

    /**
     * @return void
     */
    public function removeSel(): void
    {
        $this->isRemove = true;
        parent::removeSel();
        $this->isRemove = false;
    }

    /**
     * Method is used to bind to article selection list change.
     *
     * @param string $articleId
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function onArticleSelectionListChange($articleId): void
    {
        parent::onArticleSelectionListChange($articleId);

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
