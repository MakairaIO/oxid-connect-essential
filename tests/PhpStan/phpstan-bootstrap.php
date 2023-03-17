<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\ArticleAttributeAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleAttributeAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\ArticleAttributeAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\ArticleAttributeAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleAttributeAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\ArticleCrosssellingAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleCrosssellingAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\ArticleCrosssellingAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\ArticleCrosssellingAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleCrossSellingAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\ArticleExtendAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleExtendAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\ArticleExtendAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\ArticleExtendAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleExtendAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\ArticleSelectionAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleSelectionAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\ArticleSelectionAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\ArticleSelectionAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleSelectionAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\AttributeMainAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\AttributeMainAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\AttributeMainAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\AttributeMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\AttributeMainAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\CategoryOrderAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\CategoryOrderAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\CategoryOrderAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\CategoryOrderAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\CategoryOrderAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\ManufacturerMainAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\ManufacturerMainAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\ManufacturerMainAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\ManufacturerMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ManufacturerMainAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Application\Controller\Admin\SelectListMainAjax::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Application\Controller\Admin\SelectListMainAjax::class,
        OxidEsales\Eshop\Application\Controller\Admin\SelectListMainAjax::class
    );
}

class_alias(
    OxidEsales\Eshop\Application\Controller\Admin\SelectListMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\SelectListMainAjax_parent::class
);

if (!class_exists(OxidEsales\Eshop\Core\Output::class)) {
    class_alias(
        OxidEsales\EshopCommunity\Core\Output::class,
        OxidEsales\Eshop\Core\Output::class
    );
}

class_alias(
    OxidEsales\Eshop\Core\Output::class,
    Makaira\OxidConnectEssential\Oxid\Core\MakairaConnectOutput_parent::class
);
