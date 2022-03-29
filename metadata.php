<?php

use Makaira\OxidConnectEssential\Controller\ReviewController;

$sMetadataVersion = '2.1';

$aModule          = [
    'id'          => 'makaira_oxid-connect-essential',
    'description' => '',
    'thumbnail'   => 'makaira.jpg',
    'version'     => '1.0.4',
    'author'      => 'Makaira GmbH',
    'controllers' => [
        "MakairaReviewController" => ReviewController::class
    ],
];
