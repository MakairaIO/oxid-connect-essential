<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use JsonException;
use Makaira\OxidConnectEssential\Utils\TableTranslatorFactory;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Facts\Edition\EditionSelector;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

use function json_decode;

use const JSON_THROW_ON_ERROR;

class TableTranslatorFactoryTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     * @throws JsonException
     */
    public function testNonEnterpriseTableTranslation(): void
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

        $editionSelector = $this->createMock(EditionSelector::class);
        $editionSelector->method('isEnterprise')->willReturn(false);

        $factory = new TableTranslatorFactory($language, $viewNameGenerator, $editionSelector);
        $tableTranslator = $factory->create(['oxarticles'], ['oxobject2category']);

        $tableTranslator->setShopId(42);
        $tableTranslator->setLanguage('en');
        $translated = $tableTranslator->translate('SELECT *, \'CE\' FROM oxarticles, oxobject2category');

        $this->assertSame('SELECT *, \'CE\' FROM oxarticles_2_42, oxobject2category', $translated);

        $tableTranslator->setShopId(21);
        $tableTranslator->setLanguage(1);
        $translated = $tableTranslator->translate('SELECT *, \'CE\' FROM oxarticles, oxobject2category');

        $this->assertSame('SELECT *, \'CE\' FROM oxarticles_1_21, oxobject2category', $translated);
    }

    /**
     * @return void
     * @throws JsonException
     * @throws Exception
     */
    public function testEnterpriseTableTranslation(): void
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

        $editionSelector = $this->createMock(EditionSelector::class);
        $editionSelector->method('isEnterprise')->willReturn(true);

        $factory = new TableTranslatorFactory($language, $viewNameGenerator, $editionSelector);
        $tableTranslator = $factory->create(['oxarticles'], ['oxobject2category']);

        $tableTranslator->setShopId(42);
        $tableTranslator->setLanguage('en');
        $translated = $tableTranslator->translate('SELECT *, \'EE\' FROM oxarticles, oxobject2category');

        $this->assertSame('SELECT *, \'EE\' FROM oxarticles_2_42, oxobject2category_2_42', $translated);

        $tableTranslator->setShopId(21);
        $tableTranslator->setLanguage(1);
        $translated = $tableTranslator->translate('SELECT *, \'EE\' FROM oxarticles, oxobject2category');

        $this->assertSame('SELECT *, \'EE\' FROM oxarticles_1_21, oxobject2category_1_21', $translated);
    }
}
