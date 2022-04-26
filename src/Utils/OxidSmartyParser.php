<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\UtilsView;

class OxidSmartyParser implements ContentParserInterface
{
    private Language $oxLang;

    private UtilsView $oxUtilsView;

    private FrontendController $frontendController;

    /**
     * OxidSmartyParser constructor.
     *
     * @param \oxLang $oxLang
     * @param \oxUtilsView $oxUtilsView
     */
    public function __construct(
        Language $oxLang,
        UtilsView $oxUtilsView,
        FrontendController $frontendController
    ) {
        $this->frontendController = $frontendController;
        $this->oxUtilsView        = $oxUtilsView;
        $this->oxLang             = $oxLang;
    }

    /**
     * @param $langId
     *
     * @return void
     */
    public function setTplLang($langId): void
    {
        $this->oxLang->setTplLanguage($langId);
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
        return $this->oxUtilsView->getRenderedContent($content, $this->frontendController->getViewData());
    }
}
