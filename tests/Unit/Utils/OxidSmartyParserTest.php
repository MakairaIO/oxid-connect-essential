<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Makaira\OxidConnectEssential\Utils\ContentParser;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class OxidSmartyParserTest extends TestCase
{
    public function testParsing(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('renderFragment')
            ->with('foo', 'ox:phpunit_420', [])
            ->willReturn('bar');

        $language = $this->createMock(Language::class);
        $language
            ->expects($this->once())
            ->method('getTplLanguage')
            ->willReturn(0);

        $controller = $this->createMock(FrontendController::class);
        $controller
            ->expects($this->once())
            ->method('getViewData')
            ->willReturn([]);

        $parser = new ContentParser($renderer, $language, $controller);

        $this->assertEquals('bar', $parser->parseContent('phpunit_42', 'foo'));
    }
}
