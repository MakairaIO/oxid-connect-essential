<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Makaira\OxidConnectEssential\Utils\OxidSmartyParser;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\TestingLibrary\UnitTestCase;

/**
 *
 */
class OxidSmartyParserTest extends UnitTestCase
{
    public function testParsing(): void
    {
        $utilsViewMock = $this->createMock(UtilsView::class);
        $utilsViewMock
            ->expects($this->once())
            ->method('getRenderedContent')
            ->with('foo')
            ->willReturn('bar');

        $frontendControllerMock = $this->createMock(FrontendController::class);
        $frontendControllerMock
            ->expects($this->once())
            ->method('getViewData')
            ->willReturn([]);

        $parser = new OxidSmartyParser($utilsViewMock, $frontendControllerMock);

        $this->assertEquals('bar', $parser->parseContent('foo'));
    }
}
