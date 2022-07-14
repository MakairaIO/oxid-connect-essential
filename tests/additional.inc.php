<?php

$testConfig = new \OxidEsales\TestingLibrary\TestConfig();
$serviceCaller = new \OxidEsales\TestingLibrary\ServiceCaller();
$fixturesFile = sprintf('%s/fixtures/shop-%s.sql', __DIR__, strtolower($testConfig->getShopEdition()));

if (file_exists($fixturesFile)) {
    $serviceCaller->setParameter('importSql', "@{$fixturesFile}");
    $serviceCaller->setParameter('addDemoData', 0);
    $serviceCaller->callService('ShopPreparation', 1);
}
