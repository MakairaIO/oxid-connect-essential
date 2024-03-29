<?php

namespace Makaira\OxidConnectEssential\RevisionHandler\Extractor;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;
use Makaira\OxidConnectEssential\Domain\Revision;
use Makaira\OxidConnectEssential\RevisionHandler\AbstractModelDataExtractor;
use OxidEsales\Eshop\Application\Model\Object2Category as Object2CategoryModel;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

class ArticleCategory extends AbstractModelDataExtractor
{
    /**
     * @var Statement|null
     */
    private ?Statement $statement = null;

    /**
     * @param Connection             $connection
     * @param TableViewNameGenerator $viewNameGenerator
     */
    public function __construct(private Connection $connection, private TableViewNameGenerator $viewNameGenerator)
    {
    }

    /**
     * @param Object2CategoryModel $model
     *
     * @return array<Revision>
     * @throws Exception
     * @throws DBALException
     */
    public function extract(BaseModel $model): array
    {
        if (null === $this->statement) {
            $productView = $this->viewNameGenerator->getViewName('oxarticles');
            $this->statement = $this->connection->prepare(
                "SELECT `OXPARENTID` FROM `{$productView}` WHERE `OXID` = ?"
            );
        }

        $productId = $model->getProductId();

        $result = $this->statement->executeQuery([$productId]);

        $parentId = $result->fetchOne();

        return $this->buildRevision($parentId ? Revision::TYPE_VARIANT : Revision::TYPE_PRODUCT, $productId);
    }

    /**
     * @param BaseModel $model
     *
     * @return bool
     */
    public function supports(BaseModel $model): bool
    {
        return $model instanceof Object2CategoryModel && null !== $model->getProductId();
    }
}
