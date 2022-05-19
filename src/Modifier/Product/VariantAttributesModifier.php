<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Exception as ConnectException;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;

/**
 * Class AttributeModifier
 *
 * @package Makaira\OxidConnectEssential\Type\ProductRepository
 */
class VariantAttributesModifier extends Modifier
{
    public string $selectVariantNameQuery = '
                        SELECT
                            oxvarname
                        FROM
                            oxarticles
                        WHERE
                            oxid = :productId
                        ';

    public string $selectVariantDataQuery = '
                        SELECT
                            oxid as `id`,
                            oxvarselect as `value`
                        FROM
                            oxarticles
                        WHERE
                            oxparentid = :productId
                            AND {{activeSnippet}}
                        ';

    public string $selectVariantAttributesQuery = '
                        SELECT
                            oxattribute.oxid as `id`,
                            oxobject2attribute.oxvalue as `value`
                        FROM
                            oxobject2attribute
                            JOIN oxattribute ON oxobject2attribute.oxattrid = oxattribute.oxid
                        WHERE
                            oxobject2attribute.oxvalue != \'\'
                            AND oxobject2attribute.oxobjectid in (:productId, :variantId)
                        ';

    private Connection $database;

    private string $activeSnippet;

    private ModuleSettingsProvider $moduleSettings;

    /**
     * @param Connection             $database
     * @param string                 $activeSnippet
     * @param ModuleSettingsProvider $moduleSettings
     */
    public function __construct(
        Connection $database,
        string $activeSnippet,
        ModuleSettingsProvider $moduleSettings
    ) {
        $this->activeSnippet  = $activeSnippet;
        $this->database       = $database;
        $this->moduleSettings = $moduleSettings;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type\Variant\Variant $product
     *
     * @return Type
     * @throws ConnectException
     * @throws DBALException
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function apply(Type $product)
    {
        if (!$product->id) {
            throw new ConnectException("Cannot fetch attributes without a product ID.");
        }

        $product->attributes = [];

        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery($this->selectVariantNameQuery, ['productId' => $product->id]);

        /** @var string $variantName */
        $variantName = $resultStatement->fetchOne();
        $single      = ($variantName === '');

        $hashArray = [];

        if (!$single) {
            $titleArray = array_map('trim', explode('|', $variantName));
            $hashArray  = array_map('md5', $titleArray);

            $query = str_replace('{{activeSnippet}}', $this->activeSnippet, $this->selectVariantDataQuery);

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery($query, ['productId' => $product->id]);

            /** @var array<array<string, string>> $variants */
            $variants = $resultStatement->fetchAllAssociative();
        } else {
            $variants = [['id' => '']];
        }

        /** @var array<string> $integerAttributes */
        $integerAttributes = $this->moduleSettings->get('makaira_attribute_as_int');

        /** @var array<string> $floatAttributes */
        $floatAttributes = $this->moduleSettings->get('makaira_attribute_as_float');

        foreach ($variants as $variant) {
            $variantAttributes = [];

            $id = $variant['id'];
            if ($id) {
                $valueArray = array_map('trim', explode('|', $variant['value']));

                foreach ($hashArray as $index => $hash) {
                    if (in_array($hash, $integerAttributes, true)) {
                        $variantAttributes[ $hash ] = (int) $valueArray[ $index ];
                    } elseif (in_array($hash, $floatAttributes, true)) {
                        $variantAttributes[ $hash ] = (float) $valueArray[ $index ];
                    } else {
                        $variantAttributes[ $hash ] = (string) $valueArray[ $index ];
                    }
                }
            }

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery(
                $this->selectVariantAttributesQuery,
                [
                    'productId' => $product->id,
                    'variantId' => $id,
                ]
            );

            $attributes = $resultStatement->fetchAllAssociative();

            foreach ($attributes as $attribute) {
                /** @var string $hash */
                $hash  = $attribute['id'];
                /** @var string|int|float $value */
                $value = $attribute['value'];

                if (in_array($hash, $integerAttributes)) {
                    $variantAttributes[ $hash ] = (int) $value;
                } elseif (in_array($hash, $floatAttributes)) {
                    $variantAttributes[ $hash ] = (float) $value;
                } else {
                    $variantAttributes[ $hash ] = (string) $value;
                }
            }

            if (!empty($variantAttributes)) {
                $product->attributes[] = $variantAttributes;
            }
        }

        return $product;
    }
}
