<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Application\Model\SelectList as SelectListModel;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

use function array_replace;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class SelectList extends AbstractModelDataExtractor
{
    /**
     * @param Connection             $connection
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(private Connection $connection, private TableViewNameGenerator $viewNameGenerator)
    {
    }

    /**
     * @param SelectListModel $model
     *
     * @return array<Revision>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function extract(BaseModel $model): array
    {
        $articleSelectListView = $this->viewNameGenerator->getViewName('oxobject2selectlist');
        $productView = $this->viewNameGenerator->getViewName('oxarticles');

        $sql = "SELECT `o2sl`.`OXOBJECTID`, `a`.`OXPARENTID` FROM `{$articleSelectListView}` `o2sl` ";
        $sql .= "LEFT JOIN `{$productView}` `a` ON `a`.`OXID` = `o2sl`.`OXOBJECTID` WHERE `o2sl`.`OXSELNID` = ?";

        $statement = $this->connection->prepare($sql);
        $result = $statement->executeQuery([$model->getId()]);

        $revisions = [];
        /** @var array<array<string, string>> $parentIds */
        $parentIds = $result->fetchAllKeyValue();
        foreach ($parentIds as $productId => $parentId) {
            $type        = $parentId ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT;
            $revisions[] = $this->buildRevision($type, $productId);
        }

        return array_replace([], ...$revisions);
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return $model instanceof SelectListModel && null !== $model->getId();
    }
}
