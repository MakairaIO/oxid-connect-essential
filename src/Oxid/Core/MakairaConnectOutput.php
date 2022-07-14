<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use Makaira\OxidConnectEssential\Exception\EmptyTrackingDataException;
use Makaira\OxidConnectEssential\Service\TrackingRenderService;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Output;

use function str_replace;
use function stripos;

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Sunny <dt@marmalade.de>
 * Author URI: http://www.marmalade.de
 * @see Output
 */
class MakairaConnectOutput extends MakairaConnectOutput_parent
{
    use SymfonyContainerTrait;

    /**
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    public function process($sValue, $sClassName): string
    {
        $output = parent::process($sValue, $sClassName);

        if ($this->isAdmin()) {
            return $output;
        }

        if (!str_contains($output, '</head>')) {
            return $output;
        }

        $container = $this->getSymfonyContainer();

        /** @var TrackingRenderService $trackingRenderer */
        $trackingRenderer = $container->get(TrackingRenderService::class);

        try {
            $trackingHtml = $trackingRenderer->render();
            return str_replace('</head>', "{$trackingHtml}</head>", $output);
        } catch (EmptyTrackingDataException $e) {
            return $output;
        }
    }

    /**
     * @param string $sName
     * @param string $output
     *
     * @return void
     */
    public function output($sName, $output): void
    {
        if (self::OUTPUT_FORMAT_HTML === $this->_sOutputFormat) {
            $closingHead = "</body>";
            $closingHeadNew = "<script type=\"text/javascript\">
oiOS=new Date().getTimezoneOffset();oiOS=(oiOS<0?\"+\":\"-\")+(\"00\"+parseInt((Math.abs(oiOS/60)))).slice(-2);
document.cookie= \"oiLocalTimeZone=\"+oiOS+\";path=/;\";
</script></body>";

            $output = ltrim($output);
            $pos = stripos($output, $closingHead);
            if (false !== $pos) {
                $output = substr_replace($output, $closingHeadNew, $pos, strlen($closingHead));
            }
        }

        parent::output($sName, $output);
    }
}
