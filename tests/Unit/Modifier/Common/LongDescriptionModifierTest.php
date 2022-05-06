<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier\Common\LongDescriptionModifier;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Utils\ContentParserInterface;
use OxidEsales\TestingLibrary\UnitTestCase;

class LongDescriptionModifierTest extends UnitTestCase
{
    public function testShortText()
    {
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->method('parseContent')
            ->willReturnArgument(0);

        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new Product();
        $product->longdesc = 'This is a short text';
        $product = $modifier->apply($product);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

    public function testShortTextWithHTML()
    {
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->method('parseContent')
            ->willReturnArgument(0);

        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new Product();
        $product->longdesc = 'This is a <del>short</del> text';
        $product = $modifier->apply($product);
        $this->assertEquals('This is a short text', $product->longdesc);
    }

    public function testTrimming()
    {
        $parserMock = $this->createMock(ContentParserInterface::class);
        $parserMock
            ->method('parseContent')
            ->willReturnArgument(0);

        $modifier = new LongDescriptionModifier($parserMock, true);
        $product = new Product();
        $product->longdesc = '   This is a short text   ' . PHP_EOL;
        $product = $modifier->apply($product);
        $this->assertEquals('This is a short text', $product->longdesc);
    }
}
