<?php

use Makaira\OxidConnectEssential\Controller\ReviewController;
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
];
