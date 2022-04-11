<?php

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Exception as ConnectException;

/**
 * Class AttributeModifier
 *
 * @package Makaira\OxidConnectEssential\Type\ProductRepository
 */
class VariantAttributesModifier extends Modifier
{
    private string $selectVariantNameQuery = '
                        SELECT
                            oxvarname
                        FROM
                            oxarticles
                        WHERE
                            oxid = :productId
                        ';

    private string $selectVariantDataQuery = '
                        SELECT
                            oxid as `id`,
                            oxvarselect as `value`
                        FROM
                            oxarticles
                        WHERE
                            oxparentid = :productId
                            AND {{activeSnippet}}
                        ';

    private string $selectVariantAttributesQuery = '
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

    /**
     * @param Connection $database
     * @param string     $activeSnippet
     * @param array      $attributeInt
     * @param array      $attributeFloat
     */
    public function __construct(
        private Connection $database,
        private string $activeSnippet,
        private array $attributeInt,
        private array $attributeFloat
    ) {
        $this->attributeInt   = array_unique((array) $attributeInt);
        $this->attributeFloat = array_unique((array) $attributeFloat);
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $product
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

        $variantName = $this->database
            ->executeQuery($this->selectVariantNameQuery, ['productId' => $product->id])
            ->fetchAllAssociative();
        $single      = ($variantName[0]['oxvarname'] === '');

        if (!$single) {
            $titleArray = array_map('trim', explode('|', $variantName[0]['oxvarname']));
            $hashArray  = array_map('md5', $titleArray);

            $query    = str_replace('{{activeSnippet}}', $this->activeSnippet, $this->selectVariantDataQuery);
            $variants = $this->database
                ->executeQuery($query, ['productId' => $product->id])
                ->fetchAllAssociative();
        } else {
            $variants = [['id' => '']];
        }

        foreach ($variants as $variant) {
            $id = $variant['id'];
            if ($id) {
                $valueArray        = array_map('trim', explode('|', $variant['value']));
                $variantAttributes = [];

                foreach ($hashArray as $index => $hash) {
                    if (in_array($hash, $this->attributeInt)) {
                        $variantAttributes[ $hash ] = (int) $valueArray[ $index ];
                    } elseif (in_array($hash, $this->attributeFloat)) {
                        $variantAttributes[ $hash ] = (float) $valueArray[ $index ];
                    } else {
                        $variantAttributes[ $hash ] = (string) $valueArray[ $index ];
                    }
                }
            }

            $attributes = $this->database
                ->executeQuery(
                    $this->selectVariantAttributesQuery, [
                        'productId' => $product->id,
                        'variantId' => $id,
                    ]
                )
                ->fetchAllAssociative();

            foreach ($attributes as $attribute) {
                $hash  = $attribute['id'];
                $value = $attribute['value'];

                if (in_array($hash, $this->attributeInt)) {
                    $variantAttributes[ $hash ] = (int) $value;
                } elseif (in_array($hash, $this->attributeFloat)) {
                    $variantAttributes[ $hash ] = (float) $value;
                } else {
                    $variantAttributes[ $hash ] = (string) $value;
                }
            }

            if ($variantAttributes) {
                $product->attributes[] = $variantAttributes;
            }
        }

        return $product;
    }
}
