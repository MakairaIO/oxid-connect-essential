<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Type\Common\AssignedTypedAttribute;
use Makaira\OxidConnectEssential\Exception as ConnectException;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use Makaira\OxidConnectEssential\Utils\TableTranslator;
use OxidEsales\Eshop\Core\Exception\SystemComponentException;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\UtilsObject;

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

    private ModuleSettingsProvider $moduleSettings;

    private Connection $database;

    private ?BaseModel $model = null;

    private ?string $activeSnippet = null;

    private string $modelClass;

    private UtilsObject $utilsObject;

    private TableTranslator $tableTranslator;

    /**
     * @param Connection             $database
     * @param string                 $activeSnippet
     * @param ModuleSettingsProvider $moduleSettings
     */
    public function __construct(
        Connection $database,
        string $modelClass,
        ModuleSettingsProvider $moduleSettings,
        UtilsObject $utilsObject,
        TableTranslator $tableTranslator
    ) {
        $this->modelClass      = $modelClass;
        $this->database        = $database;
        $this->moduleSettings  = $moduleSettings;
        $this->utilsObject     = $utilsObject;
        $this->tableTranslator = $tableTranslator;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type\Product\Product $product
     *
     * @return Type\Product\Product
     * @throws ConnectException
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $product): Type\Product\Product
    {
        $this->safeGuard();

        /** @var Result $resultStatement */
        $resultStatement = $this->database->executeQuery(
            $this->tableTranslator->translate($this->selectAttributesQuery),
            ['productId' => $product->id,]
        );
        $attributes      = $resultStatement->fetchAllAssociative();

        /** @var array<string> $integerAttributes */
        $integerAttributes = $this->moduleSettings->get('makaira_attribute_as_int');

        /** @var array<string> $floatAttributes */
        $floatAttributes   = $this->moduleSettings->get('makaira_attribute_as_float');

        if (false === $product->isVariant) {
            $product->tmpAttributeStr   = [];
            $product->tmpAttributeInt   = [];
            $product->tmpAttributeFloat = [];
            foreach ($attributes as $attributeData) {
                $attribute                = new AssignedTypedAttribute($attributeData);
                $product->tmpAttributeStr[] = $attribute;

                $attributeId = $attributeData['id'];
                if (in_array($attributeId, $integerAttributes)) {
                    $product->tmpAttributeInt[] = $attribute;
                }
                if (in_array($attributeId, $floatAttributes)) {
                    $product->tmpAttributeFloat[] = $attribute;
                }
            }
            $product->attributeStr   = $product->tmpAttributeStr;
            $product->attributeInt   = $product->tmpAttributeInt;
            $product->attributeFloat = $product->tmpAttributeFloat;

            $query = str_replace(
                '{{activeSnippet}}',
                $this->activeSnippet,
                $this->selectVariantsAttributesQuery
            );

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery(
                $this->tableTranslator->translate($query),
                ['productId' => $product->id]
            );
            $rawAttributes   = $resultStatement->fetchAllAssociative();
            $attributes      = [];
            foreach ($rawAttributes as $attributeData) {
                $id              = $attributeData['id'] . $attributeData['value'];
                $attributes[$id] = $attributeData;
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
            if (in_array($attributeId, $integerAttributes, true)) {
                $product->attributeInt[] = $attribute;
            }
            if (in_array($attributeId, $floatAttributes, true)) {
                $product->attributeFloat[] = $attribute;
            }
        }

        if (false === $product->isVariant) {
            /** @var Result $resultStatement */
            $resultStatement =
                $this->database->executeQuery(
                    $this->tableTranslator->translate($this->selectVariantsNameQuery),
                    ['productId' => $product->id]
                );

            $variantsName = $resultStatement->fetchAllAssociative();

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery(
                $this->tableTranslator->translate($this->selectVariantsQuery),
                ['productId' => $product->id]
            );

            $variants = $resultStatement->fetchAllAssociative();
        } else {
            /** @var Result $resultStatement */
            $resultStatement =
                $this->database->executeQuery(
                    $this->tableTranslator->translate($this->selectVariantNameQuery),
                    ['productId' => $product->id]
                );

            $variantsName = $resultStatement->fetchAllAssociative();

            /** @var Result $resultStatement */
            $resultStatement = $this->database->executeQuery(
                $this->tableTranslator->translate($this->selectVariantQuery),
                ['productId' => $product->id]
            );

            $variants = $resultStatement->fetchAllAssociative();
        }

        if ($variants) {
            /** @var string $variantTitle */
            $variantTitle = $variantsName[0]['title'];
            $hashArray    = array_map('md5', array_map('trim', explode('|', $variantTitle)));

            $allVariants = [];
            foreach ($variants as $variantData) {
                /** @var string $variantDataTitle */
                $variantDataTitle = $variantData['title'];
                $titleArray       = array_map('trim', explode('|', $variantDataTitle));

                /** @var string $variantDataValue */
                $variantDataValue = $variantData['value'];
                $valueArray       = array_map('trim', explode('|', $variantDataValue));

                foreach ($titleArray as $index => $title) {
                    $title                       = "{$title}  (VarSelect)";
                    $allVariants[$title][]       = $valueArray[$index];
                    $allVariants[$title]["hash"] = $hashArray[$index];
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

    protected function safeGuard(): void
    {
        if (!($this->model instanceof BaseModel)) {
            $this->model = $this->utilsObject->oxNew($this->modelClass);
        }
        if (!$this->activeSnippet) {
            $this->activeSnippet = $this->model->getSqlActiveSnippet(true);
        }
    }
}
