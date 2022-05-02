<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Makaira\OxidConnectEssential\Utils\TableTranslator;
use Makaira\OxidConnectEssential\Utils\TableTranslatorConfigurator;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\TestingLibrary\UnitTestCase;

use function json_decode;

class TableTranslatorConfiguratorTest extends UnitTestCase
{
    public function testTranslate()
    {
        $viewNameGenerator = $this->createMock(TableViewNameGenerator::class);
        $viewNameGenerator->method('getViewName')
            ->willReturnCallback(
                static fn($table, $language, $shopId) => "{$table}_{$language}_{$shopId}"
            );
        $language = $this->createMock(Language::class);
        $language->method('getLanguageArray')->willReturn(
            json_decode('[{"abbr":"de","id":1}, {"abbr":"en","id":2}]', false, 512, JSON_THROW_ON_ERROR)
        );

        $tableTranslator = new TableTranslator(['oxarticles']);
        $configurator = new TableTranslatorConfigurator($language, $viewNameGenerator);
        $configurator->configure($tableTranslator);

        $tableTranslator->setShopId(42);
        $tableTranslator->setLanguage('en');
        $translated = $tableTranslator->translate('SELECT * FROM oxarticles');

        $this->assertSame('SELECT * FROM oxarticles_2_42', $translated);

        $tableTranslator->setShopId(21);
        $tableTranslator->setLanguage(1);
        $translated = $tableTranslator->translate('SELECT * FROM oxarticles');

        $this->assertSame('SELECT * FROM oxarticles_1_21', $translated);
    }
}
