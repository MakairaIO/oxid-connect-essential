<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\Output;

use function get_class;

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
    private static ?array $trackingData = null;

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

        if (
            !str_contains($output, '</head>') ||
            !Registry::get(MakairaCookieUtils::class)->hasCookiesAccepted()
        ) {
            return $output;
        }

        $trackingData = $this->getTrackingData();

        if (empty($trackingData)) {
            return $output;
        }

        /** @var MakairaTrackingDataGenerator $trackingDataGenerator */
        $trackingDataGenerator = Registry::get(MakairaTrackingDataGenerator::class);

        $trackerUrl = json_encode($trackingDataGenerator->getTrackerUrl());
        $trackingHtml = '<script type="text/javascript">var _paq = _paq || [];';

        foreach ($trackingData as $trackingPart) {
            $trackingHtml .= '_paq.push(' . json_encode($trackingPart) . ');';
        }
        // @codingStandardsIgnoreStart
        $trackingHtml .= "var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';";
        $trackingHtml .= "g.defer=true; g.async=true; g.src={$trackerUrl}+'/piwik.js'; s.parentNode.insertBefore(g,s);";
        $trackingHtml .= '</script>';
        // @codingStandardsIgnoreEnd

        return str_replace('</head>', "{$trackingHtml}</head>", $output);
    }

    /**
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function getTrackingData(): array
    {
        if (null === self::$trackingData) {
            /** @var MakairaTrackingDataGenerator $trackingDataGenerator */
            $trackingDataGenerator = Registry::get(MakairaTrackingDataGenerator::class);
            $oxidController = Registry::getConfig()
                ->getTopActiveView();
            self::$trackingData = $trackingDataGenerator->generate(get_class($oxidController));
        }

        return self::$trackingData;
    }

    public function output($sName, $output)
    {
        if (
            self::OUTPUT_FORMAT_HTML === $this->_sOutputFormat &&
            Registry::get(MakairaCookieUtils::class)->hasCookiesAccepted()
        ) {
            $closingHead = "</body>";
            $closingHeadNew = "<script type=\"text/javascript\">
oiOS=new Date().getTimezoneOffset();oiOS=(oiOS<0?\"+\":\"-\")+(\"00\"+parseInt((Math.abs(oiOS/60)))).slice(-2);
document.cookie= \"oiLocalTimeZone=\"+oiOS+\";path=/;\";
</script></body>";

            $output = ltrim($output);
            if (false !== ($pos = stripos($output, $closingHead))) {
                $output = substr_replace($output, $closingHeadNew, $pos, strlen($closingHead));
            }
        }

        parent::output($sName, $output);
    }
}
