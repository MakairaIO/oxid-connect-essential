<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Application\Model\Manufacturer as ManufacturerModel;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

use function array_replace;

class Manufacturer extends AbstractModelDataExtractor
{
    /**
     * @param Connection             $connection
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(private Connection $connection, private TableViewNameGenerator $viewNameGenerator)
    {
    }

    /**
     * @param ManufacturerModel $model
     *
     * @return array<Revision>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function extract(BaseModel $model): array
    {
        $revisions   = [$this->buildRevistion(Revision::TYPE_MANUFACTURER, $model->getId())];
        $articleView = $this->viewNameGenerator->getViewName('oxarticles');

        $statement = $this->connection->prepare(
            "SELECT a.OXID, a.OXPARENTID
            FROM `{$articleView}` a
            WHERE a.`OXMANUFACTURERID` = ?"
        );
        $statement->execute([$model->getId()]);

        foreach ($statement->fetchAssociative() as $product) {
            $type        = $product['OXPARENTID'] ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT;
            $revisions[] = $this->buildRevistion($type, $product['OXID']);
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
        return $model instanceof ManufacturerModel;
    }

}
