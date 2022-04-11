<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class DefaultProperties extends Modifier
{
    private array $commonFieldMapping = [
        'id'               => 'OXID',
        'es_id'            => '',
        'timestamp'        => 'OXTIMESTAMP',
        'url'              => '',
        'active'           => 'OXACTIVE',
        'meta_keywords'    => '',
        'meta_description' => '',
    ];

    private array $productFieldMapping = [
        'searchkeys'     => 'OXSEARCHKEYS',
        'hidden'         => 'OXHIDDEN',
        'sort'           => 'OXSORT',
        'shortdesc'      => 'OXSHORTDESC',
        'longdesc'       => 'OXLONGDESC',
        'manufacturerid' => 'OXMANUFACTURERID',
        'price'          => 'OXPRICE',
        'insert'         => 'OXINSERT',
        'soldamount'     => 'OXSOLDAMOUNT',
        'rating'         => 'OXRATING',
        'searchable'     => 'OXISSEARCH',
        'ean'            => 'OXARTNUM',
        'stock'          => 1,
        'onstock'        => true,
        'title'          => 'OXTITLE',
    ];

    private array $categoryFieldMapping = [
        'sort'           => 'OXSORT',
        'shortdesc'      => 'OXDESC',
        'longdesc'       => 'OXLONGDESC',
        'hidden'         => 'OXHIDDEN',
        'category_title' => 'OXTITLE',

    ];

    private array $manufacturerFieldMapping = [
        'shortdesc'          => 'OXSHORTDESC',
        'manufacturer_title' => 'OXTITLE',
    ];

    private array $boolFields = [
        'searchable',
        'hidden'
    ];

    /**
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function apply(Type $entity)
    {
        $mappingFields = [];

        switch ($this->getDocType()) {
            case "product":
            case "variant":
                $mappingFields = $this->productFieldMapping;
                break;

            case "category":
                $mappingFields = $this->categoryFieldMapping;
                break;

            case "manufacturer":
                $mappingFields = $this->manufacturerFieldMapping;
                break;

            default:
                break;
        }

        $mappingFields = array_merge($this->commonFieldMapping, $mappingFields);

        foreach ($mappingFields as $target => $source) {
            if ($source && isset($entity->$source)) {
                $entity->$target = $entity->$source;
            } elseif ($target && !isset($entity->$target)) {
                $entity->$target = $source;
            }
        }

        foreach ($this->boolFields as $boolField) {
            if (isset($entity->$boolField)) {
                $entity->$boolField = (bool) $entity->$boolField;
            }
        }

        return $entity;
    }
}
