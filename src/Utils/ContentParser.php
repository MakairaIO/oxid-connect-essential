<?php

namespace Makaira\OxidConnectEssential\Utils;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Framework\Templating\TemplateRendererInterface;

class ContentParser implements ContentParserInterface
{
    public function __construct(
        private TemplateRendererInterface $renderer,
        private Language $oxidLanguage,
        private FrontendController $frontendController
    ) {
    }

    /**
     * Parse content through a templating engine
     *
     * @param string $id
     * @param string $content
     *
     * @return string
     */
    public function parseContent(string $id, string $content): string
    {
        $this->frontendController->addGlobalParams();

        $activeLanguageId = $this->oxidLanguage->getTplLanguage();

        return $this->renderer->renderFragment(
            $content,
            "ox:{$id}{$activeLanguageId}",
            $this->frontendController->getViewData()
        );
    }
}
