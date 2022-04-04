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

    public function removeAttr()
    {
        $this->isRemove = true;
        parent::removeAttr();
    }

    /**
     * Method is used to bind to attribute and article relation change action.
     *
     * @param string $articleId
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function onArticleAttributeRelationChange($articleId)
    {
        if ($this->isRemove) {
            /** @var ContainerInterface $container */
            $container = $this->getSymfonyContainer();

            /** @var RevisionRepository $revisionRepo */
            $revisionRepo = $container->get(RevisionRepository::class);
            $revisionRepo->touchProduct($articleId);

            $this->isRemove = false;
        }
    }
}
