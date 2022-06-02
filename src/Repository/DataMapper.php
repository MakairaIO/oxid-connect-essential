<?php

namespace Makaira\OxidConnectEssential\Repository;

use Closure;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Category\Category;
use Makaira\OxidConnectEssential\Type\Manufacturer\Manufacturer;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Type\Variant\Variant;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

use function array_change_key_case;
use function array_merge;
use function get_class;

use const CASE_UPPER;

class DataMapper
{
    private const COMMON_FIELD_MAPPING = [
        'OXID'        => 'id',
        'OXTIMESTAMP' => 'timestamp',
        'OXACTIVE'    => 'active',
    ];

    private const PRODUCT_FIELD_MAPPING = [
        'OXSEARCHKEYS'     => 'searchkeys',
        'OXHIDDEN'         => 'hidden',
        'OXSORT'           => 'sort',
        'OXSHORTDESC'      => 'shortdesc',
        'OXLONGDESC'       => 'longdesc',
        'OXMANUFACTURERID' => 'manufacturerid',
        'OXPRICE'          => 'price',
        'OXINSERT'         => 'insert',
        'OXSOLDAMOUNT'     => 'soldamount',
        'OXRATING'         => 'rating',
        'OXISSEARCH'       => 'searchable',
        'OXARTNUM'         => 'ean',
        'OXTITLE'          => 'title',
    ];

    private const CATEGORY_FIELD_MAPPING = [
        'OXSORT'     => 'sort',
        'OXDESC'     => 'shortdesc',
        'OXLONGDESC' => 'longdesc',
        'OXHIDDEN'   => 'hidden',
        'OXTITLE'    => 'category_title',
    ];

    private const MANUFACTURER_FIELD_MAPPING = [
        'OXSHORTDESC' => 'shortdesc',
        'OXTITLE'     => 'manufacturer_title',
    ];

    private static array $dataTypes = [];

    /**
     * @param Category|Product|Manufacturer|Variant $entity
     * @param array                                 $dbResult
     * @param string                                $docType
     *
     * @return void
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function map(Type $entity, array $dbResult, string $docType): void
    {
        $mappingFields = [];

        switch ($docType) {
            case "product":
            case "variant":
                $mappingFields = self::PRODUCT_FIELD_MAPPING;
                break;

            case "category":
                $mappingFields = self::CATEGORY_FIELD_MAPPING;
                break;

            case "manufacturer":
                $mappingFields = self::MANUFACTURER_FIELD_MAPPING;
                break;

            default:
                break;
        }

        $mappingFields = array_merge(self::COMMON_FIELD_MAPPING, $mappingFields);

        $fieldDataTypes = $this->getFieldDataTypes($entity);

        $entity->additionalData = $dbResult;

        foreach ($mappingFields as $dbField => $mappedField) {
            if (isset($entity->additionalData[$dbField])) {
                $typeValue = $entity->additionalData[$dbField];
                if (isset($fieldDataTypes[$mappedField])) {
                    $c = $fieldDataTypes[$mappedField];
                    $typeValue = $c($mappedField);
                }

                $entity->{$mappedField} = $typeValue;
                unset($entity->additionalData[$dbField]);
            }

            unset($entity->additionalData[$mappedField]);
        }
    }

    /**
     * @param Type $entity
     *
     * @return array<string, Closure>
     */
    private function getFieldDataTypes(Type $entity): array
    {
        $entityClass = get_class($entity);

        if (!isset(self::$dataTypes[$entityClass])) {
            self::$dataTypes[$entityClass] = [];

            $reflection = new ReflectionClass($entity);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                if (
                    null !== ($propertyType = $property->getType()) &&
                    $propertyType instanceof ReflectionNamedType &&
                    !('string' === $propertyType->getName() && $propertyType->allowsNull())
                ) {
                    $converter = eval("return static fn (\$value) => ({$propertyType->getName()}) \$value;");

                    self::$dataTypes[$entityClass][$property->getName()] = $converter;
                }
            }
        }

        return self::$dataTypes[$entityClass];
    }
}
