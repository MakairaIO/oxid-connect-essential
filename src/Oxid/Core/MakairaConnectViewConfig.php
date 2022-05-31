<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Core\ViewConfig;
use OxidEsales\EshopCommunity\Core\Config;

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 * @see ViewConfig
 */

class MakairaConnectViewConfig extends MakairaConnectViewConfig_parent
{
    private ?bool $cookieBannerActive = null;

    /**
     * @return bool
     */
    public function isCookieBannerActive(): bool
    {
        if (null === $this->cookieBannerActive) {
            $this->cookieBannerActive = (bool) Registry::getConfig()->getShopConfVar(
                'makaira_cookie_banner_enabled',
                null,
                Config::OXMODULE_MODULE_PREFIX . 'makaira_oxid-connect-essential'
            );
        }

        return $this->cookieBannerActive;
    }


    public function getCookieBannerStylePath(): string
    {
        $modulePath = $this->getModulePath('makaira/oxid-connect-essential');
        $file       = (array) glob($modulePath . 'out/dist/*.css');

        /** @var string $firstFile */
        $firstFile = reset($file);

        return substr($firstFile, strlen($modulePath));
    }

    public function getCookieBannerScriptPath(): string
    {
        $modulePath = $this->getModulePath('makaira/oxid-connect-essential');
        $file       = (array) glob($modulePath . 'out/dist/*.js');

        /** @var string $firstFile */
        $firstFile = reset($file);

        return substr($firstFile, strlen($modulePath));
    }
}