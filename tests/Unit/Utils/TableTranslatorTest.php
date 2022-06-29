<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Makaira\OxidConnectEssential\Utils\TableTranslator;
use OxidEsales\TestingLibrary\UnitTestCase;

class TableTranslatorTest extends UnitTestCase
{
    public function testSimpleTranslate()
    {
        $translator = new TableTranslator(['oxarticles']);

        $sql = $translator->translate('SELECT * FROM oxarticles');
        self::assertEquals('SELECT * FROM oxv_oxarticles_de', $sql);
    }

    public function testTranslateWithSetLanguage()
    {
        $translator = new TableTranslator(['oxarticles']);
        $translator->setLanguage('kh');

        $sql = $translator->translate('SELECT * FROM oxarticles');
        self::assertEquals('SELECT * FROM oxv_oxarticles_kh', $sql);
    }

    public function testTranslateWithShopId()
    {
        $translator = new TableTranslator(['oxarticles']);
        $translator->setShopId(42);

        $sql = $translator->translate('SELECT * FROM oxarticles');
        self::assertEquals('SELECT * FROM oxv_oxarticles_42_de', $sql);
    }

    public function testTranslateWithLanguageAndShopId()
    {
        $translator = new TableTranslator(['oxarticles']);
        $translator->setLanguage('kh');
        $translator->setShopId(42);

        $sql = $translator->translate('SELECT * FROM oxarticles');
        self::assertEquals('SELECT * FROM oxv_oxarticles_42_kh', $sql);
    }

    public function testTranslateWithView()
    {
        $translator = new TableTranslator(['oxarticles']);

        $sql = $translator->translate('SELECT * FROM oxv_oxarticles_en');
        self::assertEquals('SELECT * FROM oxv_oxarticles_en', $sql);
    }

    public function testMultiTranslate()
    {
        $translator = new TableTranslator(['oxarticles']);

        $sql = $translator->translate('SELECT * FROM oxarticles WHERE oxarticles.OXACTIVE = 1');
        self::assertEquals('SELECT * FROM oxv_oxarticles_de WHERE oxv_oxarticles_de.OXACTIVE = 1', $sql);
    }

    public function testTranslateWithMultipleTables()
    {
        $translator = new TableTranslator(['oxarticles', 'oxartextends']);

        $sql = $translator->translate(
            'SELECT * FROM oxarticles LEFT JOIN oxartextends ON oxartextends.OXID = oxarticles.OXID'
        );
        self::assertEquals(
            'SELECT * FROM oxv_oxarticles_de ' .
            'LEFT JOIN oxv_oxartextends_de ON oxv_oxartextends_de.OXID = oxv_oxarticles_de.OXID',
            $sql
        );
    }

    public function testTranslateWithPartialMatches()
    {
        $translator = new TableTranslator(['oxarticles']);

        $sql = $translator->translate(
            'SELECT * FROM oxarticles LEFT JOIN oxarticles2shop ON oxarticles2shop.OXMAPOBJECTID = oxarticles.OXMAPID'
        );
        self::assertEquals(
            'SELECT * FROM oxv_oxarticles_de ' .
            'LEFT JOIN oxarticles2shop ON oxarticles2shop.OXMAPOBJECTID = oxv_oxarticles_de.OXMAPID',
            $sql
        );
    }

    public function testTranslateWithcustomTranslation()
    {
        $translator = new TableTranslator(['oxarticles']);
        $translator->setViewNameGenerator(static fn () => 'phpunit_table');
        $translator->setLanguage('xx');

        $sql = $translator->translate('SELECT * FROM oxarticles');
        self::assertEquals('SELECT * FROM phpunit_table', $sql);
    }
}
