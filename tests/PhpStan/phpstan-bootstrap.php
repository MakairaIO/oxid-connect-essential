<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleAttributeAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleAttributeAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleCrosssellingAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleCrossSellingAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleExtendAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleExtendAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\ArticleSelectionAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ArticleSelectionAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\AttributeMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\AttributeMainAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\CategoryOrderAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\CategoryOrderAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\ManufacturerMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\ManufacturerMainAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Application\Controller\Admin\SelectListMainAjax::class,
    Makaira\OxidConnectEssential\Controller\Admin\SelectListMainAjax_parent::class
);

class_alias(
    OxidEsales\EshopCommunity\Core\Output::class,
    Makaira\OxidConnectEssential\Oxid\Core\MakairaConnectOutput_parent::class
);
