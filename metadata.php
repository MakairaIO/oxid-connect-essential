<?php

use Makaira\OxidConnectEssential\Controller\Admin as ModuleAdminController;
use Makaira\OxidConnectEssential\Oxid\Core as ModuleOxidCore;
use Makaira\OxidConnectEssential\Core as ModuleCore;
use OxidEsales\Eshop\Core as OxidCore;
use Makaira\OxidConnectEssential\Controller as ModuleController;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use OxidEsales\Eshop\Application\Controller\Admin as OxidAdminController;

$sMetadataVersion = '2.1';

$aModule = [
    'id'          => 'makaira_oxid-connect-essential',
    'title'       => 'Makaira Connect Essential',
    'description' => 'This module provides required endpoints to import product data into Makaira.',
    'thumbnail'   => 'makaira.jpg',
    'version'     => '1.4.5',
    'author'      => 'Makaira GmbH',
    'url'         => 'https://www.makaira.io/',
    'email'       => 'support@makaira.io',
    'controllers' => [
        "MakairaReviewController"  => ModuleController\ReviewController::class,
        "MakairaUserController"    => ModuleController\UserController::class,
        "MakairaCartController"    => ModuleController\CartController::class,
        "makaira_connect_endpoint" => ModuleController\Endpoint::class,
    ],
    'extend'      => [
        OxidCore\Output::class                             => ModuleOxidCore\MakairaConnectOutput::class,
        OxidAdminController\ArticleAttributeAjax::class    => ModuleAdminController\ArticleAttributeAjax::class,
        OxidAdminController\ArticleCrosssellingAjax::class => ModuleAdminController\ArticleCrossSellingAjax::class,
        OxidAdminController\ArticleExtendAjax::class       => ModuleAdminController\ArticleExtendAjax::class,
        OxidAdminController\ArticleSelectionAjax::class    => ModuleAdminController\ArticleSelectionAjax::class,
        OxidAdminController\AttributeMainAjax::class       => ModuleAdminController\AttributeMainAjax::class,
        OxidAdminController\CategoryOrderAjax::class       => ModuleAdminController\CategoryOrderAjax::class,
        OxidAdminController\ManufacturerMainAjax::class    => ModuleAdminController\ManufacturerMainAjax::class,
        OxidAdminController\SelectListMainAjax::class      => ModuleAdminController\SelectListMainAjax::class,
    ],
    'settings'    => [
        ['name' => 'makaira_connect_secret', 'group' => 'SETTINGS', 'type' => 'str'],
        ['name' => 'makaira_connect_category_inheritance', 'group' => 'SETTINGS', 'type' => 'bool', 'value' => 0],
        [
            'name'  => 'makaira_field_blacklist_product',
            'group' => 'IMPORTFIELDSANDATTS',
            'type'  => 'arr',
            'value' => [
                'OXAMITEMID',
                'OXAMTASKID',
                'OXBUNDLEID',
                'OXEXTURL',
                'OXFOLDER',
                'OXNOSTOCKTEXT',
                'OXPIC8',
                'OXPIC9',
                'OXPIC10',
                'OXPIC11',
                'OXPIC12',
                'OXQUESTIONEMAIL',
                'OXREMINDACTIVE',
                'OXREMINDAMOUNT',
                'OXSHOWCUSTOMAGREEMENT',
                'OXSKIPDISCOUNTS',
                'OXSTOCKTEXT',
                'OXSUBCLASS',
                'OXTEMPLATE',
                'OXUPDATEPRICE',
                'OXUPDATEPRICEA',
                'OXUPDATEPRICEB',
                'OXUPDATEPRICEC',
                'OXUPDATEPRICETIME',
                'OXURLDESC',
                'OXURLIMG',
                'OXPIXIEXPORT',
                'OXPIXIEXPORTED',
                'OXORDERINFO',
                'OXVPE',
            ],
        ],
        [
            'name'  => 'makaira_field_blacklist_category',
            'group' => 'IMPORTFIELDSANDATTS',
            'type'  => 'arr',
            'value' => [
                'OXVAT',
                'OXSKIPDISCOUNTS',
            ],
        ],
        [
            'name'  => 'makaira_field_blacklist_manufacturer',
            'group' => 'IMPORTFIELDSANDATTS',
            'type'  => 'arr',
            'value' => [],
        ],
        [
            'name'  => 'makaira_attribute_as_int',
            'group' => 'IMPORTFIELDSANDATTS',
            'type'  => 'arr',
            'value' => [],
        ],
        [
            'name'  => 'makaira_attribute_as_float',
            'group' => 'IMPORTFIELDSANDATTS',
            'type'  => 'arr',
            'value' => [],
        ],
        [
            'group' => 'TRACKING_PRIVACY',
            'name'  => 'makaira_tracking_page_id',
            'type'  => 'str',
            'value' => '',
        ],
    ],
    'events'      => [
        'onActivate' => ModuleCore\ModuleEvents::class . '::onActivate',
    ],
];
