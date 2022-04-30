<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

class ArticleAttribute extends AbstractModelDataExtractor
{
    /**
     * @var Statement|null
     */
    private ?Statement $statement = null;

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
     * @param BaseModel $model
     *
     * @return array<Revision>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function extract(BaseModel $model): array
    {
        if (null === $this->statement) {
            $productView     = $this->viewNameGenerator->getViewName('oxarticles');
            $this->statement = $this->connection->prepare(
                "SELECT `OXPARENTID` FROM `{$productView}` WHERE `OXID` = ?"
            );
        }

        /** @var string $productId */
        $productId = $model->getRawFieldData('oxobjectid');

        $this->statement->execute([$productId]);

        $parentId = $this->statement->fetchOne();

        return $this->buildRevistion($parentId ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT, $productId);
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return 'oxobject2attribute' === $model->getCoreTableName();
    }
}
