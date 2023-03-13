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

use function get_class;

class DataMapper
{
    private const COMMON_FIELD_MAPPING = [
        'OXID'     => 'id',
        'OXACTIVE' => 'active',
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
        'OXTIMESTAMP' => 'timestamp',
        'OXSORT'      => 'sort',
        'OXDESC'      => 'shortdesc',
        'OXLONGDESC'  => 'longdesc',
        'OXHIDDEN'    => 'hidden',
        'OXTITLE'     => 'category_title',
    ];

    private const MANUFACTURER_FIELD_MAPPING = [
        'OXTIMESTAMP' => 'timestamp',
        'OXSHORTDESC' => 'shortdesc',
        'OXTITLE'     => 'manufacturer_title',
    ];

    /**
     * @var array<string, array<string, string>>
     */
    private static ?array $fieldMappings = null;

    /**
     * @var array<array<string, Closure>>
     */
    private static array $dataTypes = [];

    /**
     *
     */
    public function __construct()
    {
        if (null === self::$fieldMappings) {
            self::$fieldMappings[Product::class] = array_replace(
                self::COMMON_FIELD_MAPPING,
                self::PRODUCT_FIELD_MAPPING
            );

            self::$fieldMappings[Variant::class] = array_replace(
                self::COMMON_FIELD_MAPPING,
                self::PRODUCT_FIELD_MAPPING
            );

            self::$fieldMappings[Category::class] = array_replace(
                self::COMMON_FIELD_MAPPING,
                self::CATEGORY_FIELD_MAPPING
            );

            self::$fieldMappings[Manufacturer::class] = array_replace(
                self::COMMON_FIELD_MAPPING,
                self::MANUFACTURER_FIELD_MAPPING
            );
        }
    }

    /**
     * @param Type  $entity
     * @param array $dbResult
     *
     * @return void
     */
    public function map(Type $entity, array $dbResult): void
    {
        $mappingFields = self::$fieldMappings[get_class($entity)] ?? self::COMMON_FIELD_MAPPING;

        $fieldDataTypes = $this->getFieldDataTypes($entity);

        $entity->additionalData = $dbResult;

        foreach ($mappingFields as $dbField => $mappedField) {
            if (isset($entity->additionalData[$dbField])) {
                $typeValue = $entity->additionalData[$dbField];
                if (isset($fieldDataTypes[$mappedField])) {
                    $convertClosure = $fieldDataTypes[$mappedField];
                    $typeValue = $convertClosure($typeValue);
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
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    private function getFieldDataTypes(Type $entity): array
    {
        $entityClass = get_class($entity);

        if (!isset(self::$dataTypes[$entityClass])) {
            self::$dataTypes[$entityClass] = [];

            $reflection = new ReflectionClass($entity);
            $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($properties as $property) {
                $propertyType = $property->getType();
                if (
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
