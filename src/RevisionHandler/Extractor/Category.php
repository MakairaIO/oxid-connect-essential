<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Application\Model\Category as CategoryModel;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

class Category extends AbstractModelDataExtractor
{
    private Connection $connection;

    private TableViewNameGenerator $viewNameGenerator;

    /**
     * @param Connection             $connection
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(Connection $connection, TableViewNameGenerator $viewNameGenerator)
    {
        $this->viewNameGenerator = $viewNameGenerator;
        $this->connection        = $connection;
    }

    /**
     * @param CategoryModel $model
     *
     * @return array<Revision>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function extract(BaseModel $model): array
    {
        $revisions           = [$this->buildRevision(Revision::TYPE_CATEGORY, $model->getId())];
        $articleCategoryView = $this->viewNameGenerator->getViewName('oxobject2category');
        $articleView         = $this->viewNameGenerator->getViewName('oxarticles');

        $query = "SELECT o2c.OXOBJECTID, a.OXPARENTID FROM `{$articleCategoryView}` o2c ";
        $query .= "LEFT JOIN `{$articleView}` a ON a.`OXID` = o2c.`OXOBJECTID` WHERE o2c.`OXCATNID` = ?";

        $statement = $this->connection->prepare($query);
        $statement->execute([$model->getId()]);

        /** @var array<array<string, string>> $parentIds */
        $parentIds = $statement->fetchAllKeyValue();
        foreach ($parentIds as $productId => $parentId) {
            $type        = $parentId ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT;
            $revisions[] = $this->buildRevision($type, $productId);
        }

        return array_replace(...$revisions);
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return $model instanceof CategoryModel;
    }
}
