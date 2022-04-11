<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Common\AssignedTypedAttribute;
use Makaira\OxidConnectEssential\Type\Common\BaseProduct;
use Makaira\OxidConnectEssential\Exception as ConnectException;

/**
 * Class AttributeModifier
 *
 * @package Makaira\OxidConnectEssential\Type\ProductRepository
 * @SuppressWarnings(PHPMD)
 */
class AttributeModifier extends Modifier
{
    public string $selectAttributesQuery = '
                        SELECT
                            oxattribute.oxid as `id`,
                            oxattribute.oxtitle as `title`,
                            oxobject2attribute.oxvalue as `value`
                        FROM
                            oxobject2attribute
                            JOIN oxattribute ON oxobject2attribute.oxattrid = oxattribute.oxid
                        WHERE
                            oxobject2attribute.oxobjectid = :productId
                            AND oxobject2attribute.oxvalue != \'\'
                        ';

    public string $selectVariantsQuery = '
                        SELECT
                            parent.oxvarname as `title`,
                            variant.oxvarselect as `value`
                        FROM
                            oxarticles parent
                            JOIN oxarticles variant ON parent.oxid = variant.oxparentid
                        WHERE
                            variant.oxparentid = :productId
                        ';

    public string $selectVariantsNameQuery = '
                        SELECT
                            parent.oxvarname as `title`
                        FROM
                            oxarticles parent
                            JOIN oxarticles variant ON parent.oxid = variant.oxparentid
                        WHERE
                            variant.oxparentid = :productId
                        ';

    public string $selectVariantsAttributesQuery = '
                        SELECT
                            oxattribute.oxid as `id`,
                            oxattribute.oxtitle as `title`,
                            oxobject2attribute.oxvalue as `value`
                        FROM
                            oxarticles
                            JOIN oxobject2attribute ON (oxarticles.oxid = oxobject2attribute.oxobjectid)
                            JOIN oxattribute ON oxobject2attribute.oxattrid = oxattribute.oxid
                        WHERE
                            oxarticles.oxparentid = :productId
                            AND oxobject2attribute.oxvalue != \'\'
                            AND {{activeSnippet}}
                        ';

    public string $selectVariantQuery = '
                        SELECT
                            parent.oxvarname as `title`,
                            variant.oxvarselect as `value`
                        FROM
                            oxarticles parent
                            JOIN oxarticles variant ON parent.oxid = variant.oxparentid
                        WHERE
                            variant.oxid = :productId
                        ';

    public string $selectVariantNameQuery = '
                        SELECT
                            parent.oxvarname as `title`
                        FROM
                            oxarticles parent
                            JOIN oxarticles variant ON parent.oxid = variant.oxparentid
                        WHERE
                            variant.oxid = :productId
                        ';

    /**
     * @var array
     */
    private array $attributeInt;

    /**
     * @var array
     */
    private array $attributeFloat;

    /**
     * @param Connection $database
     * @param string     $activeSnippet
     * @param array|null $attributeInt
     * @param array|null $attributeFloat
     */
    public function __construct(
        private Connection $database,
        private string $activeSnippet,
        ?array $attributeInt = null,
        ?array $attributeFloat = null
    ) {
        $this->attributeInt   = array_unique((array) $attributeInt);
        $this->attributeFloat = array_unique((array) $attributeFloat);
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $product
     *
     * @return BaseProduct|Type
     * @throws ConnectException
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $product)
    {
        if (!$product->id) {
            throw new ConnectException("Cannot fetch attributes without a product ID.");
        }

        $attributes = $this->database
            ->executeQuery($this->selectAttributesQuery, ['productId' => $product->id,])
            ->fetchAllAssociative();

        if (false === $product->isVariant) {
            $product->tmpAttributeStr   = [];
            $product->tmpAttributeInt   = [];
            $product->tmpAttributeFloat = [];
            foreach ($attributes as $attributeData) {
                $attribute                = new AssignedTypedAttribute($attributeData);
                $product->tmpAttributeStr[] = $attribute;

                $attributeId = $attributeData['id'];
                if (in_array($attributeId, $this->attributeInt)) {
                    $product->tmpAttributeInt[] = $attribute;
                }
                if (in_array($attributeId, $this->attributeFloat)) {
                    $product->tmpAttributeFloat[] = $attribute;
                }
            }
            $product->attributeStr   = $product->tmpAttributeStr;
            $product->attributeInt   = $product->tmpAttributeInt;
            $product->attributeFloat = $product->tmpAttributeFloat;

            $query = str_replace('{{activeSnippet}}', $this->activeSnippet, $this->selectVariantsAttributesQuery);

            $rawAttributes = $this->database
                ->executeQuery($query, ['productId' => $product->id])
                ->fetchAllAssociative();
            $attributes              = [];
            foreach ($rawAttributes as $attributeData) {
                $id                = $attributeData['id'] . $attributeData['value'];
                $attributes[ $id ] = $attributeData;
            }
        } else {
            $product->attributeStr   = [];
            $product->attributeInt   = [];
            $product->attributeFloat = [];
        }

        foreach ($attributes as $attributeData) {
            $attribute               = new AssignedTypedAttribute($attributeData);
            $product->attributeStr[] = $attribute;

            $attributeId = $attributeData['id'];
            if (in_array($attributeId, $this->attributeInt, true)) {
                $product->attributeInt[] = $attribute;
            }
            if (in_array($attributeId, $this->attributeFloat, true)) {
                $product->attributeFloat[] = $attribute;
            }
        }

        if (false === $product->isVariant) {
            $variantsName = $this->database
                ->executeQuery($this->selectVariantsNameQuery, ['productId' => $product->id])
                ->fetchAllAssociative();
            $variants = $this->database
                ->executeQuery($this->selectVariantsQuery, ['productId' => $product->id])
                ->fetchAllAssociative();
        } else {
            $variantsName = $this->database
                ->executeQuery($this->selectVariantNameQuery, ['productId' => $product->id])
                ->fetchAllAssociative();
            $variants = $this->database
                ->executeQuery($this->selectVariantQuery, ['productId' => $product->id])
                ->fetchAllAssociative();
        }

        if ($variants) {
            $hashArray = array_map('md5', array_map('trim', explode('|', $variantsName[0]['title'])));

            $allVariants = [];
            foreach ($variants as $variantData) {
                $titleArray = array_map('trim', explode('|', $variantData['title']));
                $valueArray = array_map('trim', explode('|', $variantData['value']));

                foreach ($titleArray as $index => $title) {
                    $title                         = "{$title}  (VarSelect)";
                    $allVariants[ $title ][]       = $valueArray[ $index ];
                    $allVariants[ $title ]["hash"] = $hashArray[ $index ];
                }
            }

            foreach ($allVariants as $title => $values) {
                $hashTitle = $values["hash"];
                unset($values["hash"]);

                $uniqueValues = array_unique($values);

                foreach ($uniqueValues as $value) {
                    $product->attributeStr[] = new AssignedTypedAttribute(
                        [
                            'id'    => $hashTitle,
                            'title' => $title,
                            'value' => $value,
                        ]
                    );
                }
            }
        }

        return $product;
    }
}
