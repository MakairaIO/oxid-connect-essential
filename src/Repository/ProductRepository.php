<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Type\Product\Product;

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
     * @param $id
     *
     * @return Product
     */
    protected function getInstance($id): Product
    {
        return new Product($id);
    }

    protected function getSelectQuery(): string
    {
        return "
            SELECT
                oxarticles.OXID as `id`,
                oxarticles.oxtimestamp AS `timestamp`,
                oxarticles.*,
                oxartextends.oxlongdesc AS `OXLONGDESC`,
                oxartextends.oxtags AS `OXTAGS`,
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
        return "
          SELECT
            OXID
          FROM
            oxarticles
          WHERE
            OXPARENTID = ''
        ";
    }

    protected function getParentIdQuery(): string
    {
        return "
          SELECT
            OXPARENTID
          FROM
            oxarticles
          WHERE
            oxarticles.oxid = :id
        ";
    }

    public function getParentId($id): ?string
    {
        return (string) $this->database->executeQuery($this->getParentIdQuery(), ['id' => $id])->fetchOne();
    }
}
