<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\UtilsView;

class OxidSmartyParser implements ContentParserInterface
{
    private UtilsView $oxUtilsView;

    private FrontendController $frontendController;

    /**
     * OxidSmartyParser constructor.
     *
     * @param UtilsView          $oxUtilsView
     * @param FrontendController $frontendController
     */
    public function __construct(
        UtilsView $oxUtilsView,
        FrontendController $frontendController
    ) {
        $this->frontendController = $frontendController;
        $this->oxUtilsView        = $oxUtilsView;
    }

    /**
     * Parse content through a templating engine
     *
     * @param string $content
     *
     * @return string
     */
    public function parseContent($content): string
    {
        $this->frontendController->addGlobalParams();

        return $this->oxUtilsView->getRenderedContent($content, $this->frontendController->getViewData());
    }
}
