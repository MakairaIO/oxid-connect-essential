<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Repository\ProductRepository;
use Makaira\OxidConnectEssential\Type\Variant\Variant;

class VariantRepository extends ProductRepository
{
    /**
     * Get TYPE of repository.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'variant';
    }

    /**
     * Get an instance of current type.
     *
     * @return Variant
     */
    protected function getInstance($id): Variant
    {
        return new Variant($id);
    }

    protected function getSelectQuery(): string
    {
        return "
            SELECT
                oxarticles.OXID as `id`,
                oxarticles.oxparentid AS `parent`,
                oxarticles.oxtimestamp AS `timestamp`,
                oxarticles.*,
                oxartextends.oxlongdesc AS `OXLONGDESC`,
                oxartextends.oxtags AS `OXTAGS`
            FROM
                oxarticles
                LEFT JOIN oxartextends ON oxarticles.oxid = oxartextends.oxid
            WHERE
                oxarticles.oxid = :id
                AND oxarticles.oxparentid != ''
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
            OXPARENTID != ''
        ";
    }
}
