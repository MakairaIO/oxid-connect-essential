<?php

namespace Makaira\OxidConnectEssential\Service;

use Makaira\OxidConnectEssential\Exception\EmptyTrackingDataException;
use Makaira\OxidConnectEssential\Oxid\Core\MakairaTrackingDataGenerator;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;

use function get_class;
use function json_encode;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackingRenderService
{
    private static ?array $trackingData = null;

    public function __construct(private MakairaTrackingDataGenerator $trackingDataGenerator, private Config $config)
    {
    }

    /**
     * @throws EmptyTrackingDataException
     * @throws NoArticleException
     * @throws ArticleInputException
     */
    public function render(): string
    {
        $trackingData = $this->getTrackingData();

        if (empty($trackingData)) {
            throw new EmptyTrackingDataException();
        }

        $trackerUrl = json_encode($this->trackingDataGenerator->getTrackerUrl());
        $trackingHtml = '<script type="text/javascript">var _paq = _paq || [];';

        foreach ($trackingData as $trackingPart) {
            $trackingHtml .= '_paq.push(' . json_encode($trackingPart) . ');';
        }

        $trackingHtml .= "var d=document, g=d.createElement('script'), ";
        $trackingHtml .= "s=d.getElementsByTagName('script')[0]; g.type='text/javascript';";
        $trackingHtml .= "g.defer=true; g.async=true; g.src={$trackerUrl}+'/piwik.js'; ";
        $trackingHtml .= "s.parentNode.insertBefore(g,s);";
        $trackingHtml .= '</script>';

        return $trackingHtml;
    }

    /**
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function getTrackingData(): array
    {
        if (null === self::$trackingData) {
            $oxidController     = $this->config->getTopActiveView();
            self::$trackingData = $this->trackingDataGenerator->generate(get_class($oxidController));
        }

        return self::$trackingData;
    }
}
