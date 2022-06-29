<?php

namespace Makaira\OxidConnectEssential\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Utils\TableTranslator;

class ProductRepository extends AbstractRepository
{
    /**
     * Get TYPE of repository.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'product';
    }

    /**
     * Get an instance of current type.
     *
     * @param string $objectId
     *
     * @return Product
     */
    protected function getInstance(string $objectId): Product
    {
        return new Product(['id' => $objectId]);
    }

    protected function getSelectQuery(): string
    {
        return "
            SELECT
                oxarticles.OXID as `id`,
                oxarticles.oxtimestamp AS `timestamp`,
                oxarticles.*,
                oxartextends.*,
                oxartextends.oxlongdesc AS `OXLONGDESC`,
                oxmanufacturers.oxtitle AS manufacturer_title
            FROM
                oxarticles
                LEFT JOIN oxartextends ON oxarticles.oxid = oxartextends.oxid
                LEFT JOIN oxmanufacturers ON oxarticles.oxmanufacturerid = oxmanufacturers.oxid
            WHERE
                oxarticles.oxid = :id
                AND oxarticles.oxparentid = ''
        ";
    }

    protected function getAllIdsQuery(): string
    {
        return "SELECT OXID FROM oxarticles WHERE OXPARENTID = '' ORDER BY OXID";
    }

    protected function getParentIdQuery(): string
    {
        return "SELECT OXPARENTID FROM oxarticles WHERE oxarticles.oxid = :id";
    }

    public function getParentId(string $productId): ?string
    {
        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery($this->getParentIdQuery(), ['id' => $productId]);

        /** @var string $parentID */
        $parentID = $resultStatement->fetchOne();

        return $parentID;
    }
}
