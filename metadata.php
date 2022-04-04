<?php

use Makaira\OxidConnectEssential\Controller\Admin as ModuleAdminController;
use Makaira\OxidConnectEssential\Controller\CartController;
use Makaira\OxidConnectEssential\Controller\ReviewController;
use Makaira\OxidConnectEssential\Controller\UserController;
use Makaira\OxidConnectEssential\Module\Events;
use OxidEsales\Eshop\Application\Controller\Admin as OxidAdminController;

$sMetadataVersion = '2.1';

$aModule = [
    'id'          => 'makaira_oxid-connect-essential',
    'title'       => 'Makaira Connect Essential',
    'description' => 'This module provides required endpoints to import product data into Makaira.',
    'thumbnail'   => 'makaira.jpg',
    'version'     => '1.1.0',
    'author'      => 'Makaira GmbH',
    'controllers' => [
        "MakairaReviewController" => ReviewController::class,
        "MakairaUserController"   => UserController::class,
        "MakairaCartController"   => CartController::class,
    ],
    'extend'      => [
        OxidAdminController\ArticleAttributeAjax::class    => ModuleAdminController\ArticleAttributeAjax::class,
        OxidAdminController\ArticleCrosssellingAjax::class => ModuleAdminController\ArticleCrossSellingAjax::class,
        OxidAdminController\ArticleExtendAjax::class       => ModuleAdminController\ArticleExtendAjax::class,
        OxidAdminController\ArticleSelectionAjax::class    => ModuleAdminController\ArticleSelectionAjax::class,
        OxidAdminController\AttributeMainAjax::class       => ModuleAdminController\AttributeMainAjax::class,
        OxidAdminController\CategoryOrderAjax::class       => ModuleAdminController\CategoryOrderAjax::class,
        OxidAdminController\ManufacturerMainAjax::class    => ModuleAdminController\ManufacturerMainAjax::class,
        OxidAdminController\SelectListMainAjax::class      => ModuleAdminController\SelectListMainAjax::class,
    ],
    'events'      => [
        'onActivate' => Events::class . '::onActivate',
    ],
];
