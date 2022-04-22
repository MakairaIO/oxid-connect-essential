<?php

use OxidEsales\Eshop\Core\ConfigFile;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\TestingLibrary\Services\Library\DatabaseHandler;

$configFile = Registry::get(ConfigFile::class);
$DbHandler = new DatabaseHandler($configFile);
$DbHandler->import(__DIR__ . '/test.sql');
