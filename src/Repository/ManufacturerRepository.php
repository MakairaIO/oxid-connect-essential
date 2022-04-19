<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Type\Manufacturer\Manufacturer;

class ManufacturerRepository extends AbstractRepository
{
    /**
     * Get TYPE of repository.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'manufacturer';
    }

    /**
     * Get an instance of current type.
     *
     * @return Manufacturer
     */
    protected function getInstance($id): Manufacturer
    {
        return new Manufacturer($id);
    }

    protected function getSelectQuery(): string
    {
        return "
          SELECT
            oxmanufacturers.OXID as `id`,
            oxmanufacturers.oxtimestamp AS `timestamp`,
            oxmanufacturers.*
          FROM
            oxmanufacturers
          WHERE
            oxmanufacturers.oxid = :id
        ";
    }

    protected function getAllIdsQuery(): string
    {
        return "
          SELECT
           OXID
          FROM
           oxmanufacturers;
        ";
    }

    protected function getParentIdQuery(): ?string
    {
        return null;
    }
}
