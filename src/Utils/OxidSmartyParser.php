<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\UtilsView;

class OxidSmartyParser implements ContentParserInterface
{
    /**
     * OxidSmartyParser constructor.
     *
     * @param \oxLang $oxLang
     * @param \oxUtilsView $oxUtilsView
     */
    public function __construct(
        private Language $oxLang,
        private UtilsView $oxUtilsView,
        private FrontendController $frontendController
    ) {
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
