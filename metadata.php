<?php

use Makaira\OxidConnectEssential\Controller\ReviewController;
use Makaira\OxidConnectEssential\Oxid\Core\MakairaConnectOutput;
use Makaira\OxidConnectEssential\Oxid\Core\MakairaConnectViewConfig;
use OxidEsales\Eshop\Core\Output;
use OxidEsales\Eshop\Core\ViewConfig;
use Makaira\OxidConnectEssential\Controller\UserController;
use Makaira\OxidConnectEssential\Controller\CartController;

$sMetadataVersion = '2.1';

$aModule          = [
    'id'          => 'makaira_oxid-connect-essential',
    'title'       => 'Makaira Connect Essential',
    'description' => 'This module provides required endpoints to import product data into Makaira.',
    'thumbnail'   => 'makaira.jpg',
    'version'     => '1.1.0',
    'author'      => 'Makaira GmbH',
    'controllers' => [
        "MakairaReviewController" => ReviewController::class,
        "MakairaUserController" => UserController::class,
        "MakairaCartController" => CartController::class,
    ],
    'extend' => [
        Output::class => MakairaConnectOutput::class,
        ViewConfig::class => MakairaConnectViewConfig::class,
    ],
    'settings'    => [
        [
            'group' => 'TRACKING_PRIVACY',
            'name'  => 'makaira_tracking_page_id',
            'type'  => 'str',
            'value' => '',
        ],
        [
            'group' => 'TRACKING_PRIVACY',
            'name'  => 'makaira_cookie_banner_enabled',
            'type'  => 'bool',
            'value' => true,
        ],
    ],
    'templates' => [
        'layout/cookie-banner.tpl' => 'makaira/oxid-connect-essential/views/tpl/layout/cookie-banner.tpl',
    ],
    'blocks'      => [
        [
            'template' => 'layout/header.tpl',
            'block'    => 'layout_header_bottom',
            'file'     => 'views/blocks/layout/header_tpl/layout_header_bottom.tpl',
        ],
    ],
];
