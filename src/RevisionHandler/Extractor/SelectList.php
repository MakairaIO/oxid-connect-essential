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

class SelectList extends AbstractModelDataExtractor
{
    private TableViewNameGenerator $viewNameGenerator;

    private Connection $connection;

    /**
     * @param Connection             $connection
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(Connection $connection, TableViewNameGenerator $viewNameGenerator)
    {
        $this->connection        = $connection;
        $this->viewNameGenerator = $viewNameGenerator;
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

        $statement = $this->connection->prepare(
            "SELECT `o2sl`.`OXOBJECTID`, `a`.`OXPARENTID`
            FROM `{$articleSelectListView}` `o2sl`
            LEFT JOIN `oxarticles` `a` ON `a`.`OXID` = `o2sl`.`OXOBJECTID`
            WHERE `o2sl`.`OXSELNID` = ?"
        );
        $statement->execute([$model->getId()]);

        $revisions = [];
        foreach ($statement->fetchAssociative() as $product) {
            $type        = $product['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT;
            $revisions[] = $this->buildRevistion($type, $product['OXOBJECTID']);
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
        return $model instanceof SelectListModel;
    }
}
