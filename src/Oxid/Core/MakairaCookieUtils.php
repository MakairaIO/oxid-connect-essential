<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsServer;
use OxidEsales\EshopCommunity\Core\Config;

class MakairaCookieUtils
{
    private static $bannerEnabled;

    public function hasCookiesAccepted(): bool
    {
        if (null === self::$bannerEnabled) {
            self::$bannerEnabled = Registry::getConfig()
                ->getShopConfVar(
                    'makaira_cookie_banner_enabled',
                    null,
                    Config::OXMODULE_MODULE_PREFIX . 'makaira_oxid-connect-essential'
                );
        }

        if (!self::$bannerEnabled) {
            return true;
        }

        if (isset($_COOKIE['cookie-consent'])) {
            return 'accept' === $_COOKIE['cookie-consent'];
        }

        return false;
    }

    public function setCookie(
        $name,
        $value,
        $expire = 0,
        $path = '/',
        $domain = null,
        $saveToSession = true,
        $secure = false,
        $httpOnly = true
    ): bool {
        if ($this->hasCookiesAccepted()) {
            /** @var UtilsServer $oxidServerUtils */
            $oxidServerUtils = Registry::get('oxutilsserver');

            return $oxidServerUtils->setOxCookie(
                $name,
                $value,
                $expire,
                $path,
                $domain,
                $saveToSession,
                $secure,
                $httpOnly
            );
        }

        return false;
    }
}
