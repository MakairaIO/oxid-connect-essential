<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Type;

use ReflectionClass;

use function array_keys;
use function array_values;
use function get_class;

class DataMapper
{
    private array $commonFieldMapping = [
        'OXID'        => 'id',
        'OXTIMESTAMP' => 'timestamp',
        'OXACTIVE'    => 'active',
    ];

    private array $productFieldMapping = [
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

    private array $categoryFieldMapping = [
        'OXSORT'     => 'sort',
        'OXDESC'     => 'shortdesc',
        'OXLONGDESC' => 'longdesc',
        'OXHIDDEN'   => 'hidden',
        'OXTITLE'    => 'category_title',
    ];

    private array $manufacturerFieldMapping = [
        'OXSHORTDESC' => 'shortdesc',
        'OXTITLE'     => 'manufacturer_title',
    ];

    private static array $dataTypes = [];

    /**
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function map(Type $entity, array $dbResult, string $docType)
    {
        $mappingFields = [];

        switch ($docType) {
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

        $fieldDataTypes = $this->getFieldDataTypes($entity);

        $entity->additionalData = $dbResult;

        // Map database columns to fields
        foreach ($entity->additionalData as $column => $value) {
            if (isset($mappingFields[$column])) {
                $typeValue = $value;
                $field = $mappingFields[$column];
                if (isset($fieldDataTypes[$field])) {
                    $c = $fieldDataTypes[$field];
                    $typeValue = $c($value);
                }

                $entity->{$field} = $typeValue;
                unset($entity->additionalData[$column]);
            }
        }

        // Remove mapped fields from additional data
        foreach ($mappingFields as $field) {
            if (isset($entity->additionalData[$field])) {
                unset($entity->additionalData[$field]);
            }
        }
    }

    /**
     * @param Type $entity
     *
     * @return array<string, string>
     */
    private function getFieldDataTypes(Type $entity): array
    {
        $entityClass = get_class($entity);

        if (!isset(self::$dataTypes[$entityClass])) {
            self::$dataTypes[$entityClass] = [];

            $reflection = new ReflectionClass($entity);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                if (
                    null !== ($propertyType = $property->getType()) &&
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
