<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsServer;
use OxidEsales\EshopCommunity\Core\Config;

class MakairaCookieUtils
{
    private static ?bool $bannerEnabled = null;

    public function hasCookiesAccepted(): bool
    {
        if (null === self::$bannerEnabled) {
            /** @var bool $bannerEnabled */
            $bannerEnabled = Registry::getConfig()
                ->getShopConfVar(
                    'makaira_cookie_banner_enabled',
                    null,
                    Config::OXMODULE_MODULE_PREFIX . 'makaira_oxid-connect-essential'
                );
            self::$bannerEnabled = $bannerEnabled;
        }

        if (!self::$bannerEnabled) {
            return true;
        }

        if (isset($_COOKIE['cookie-consent'])) {
            return 'accept' === $_COOKIE['cookie-consent'];
        }

        return false;
    }

    /**
     * @param string      $name
     * @param string      $value
     * @param int         $expire
     * @param string      $path
     * @param string|null $domain
     * @param bool        $saveToSession
     * @param bool        $secure
     * @param bool        $httpOnly
     *
     * @return bool
     */
    public function setCookie(
        string $name,
        string $value,
        int $expire = 0,
        string $path = '/',
        ?string $domain = null,
        bool $saveToSession = true,
        bool $secure = false,
        bool $httpOnly = true
    ): bool {
        if ($this->hasCookiesAccepted()) {
            /** @var UtilsServer $oxidServerUtils */
            $oxidServerUtils = Registry::get(UtilsServer::class);

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
