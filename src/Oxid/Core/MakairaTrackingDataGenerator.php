<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Application\Controller\BasketController;
use OxidEsales\Eshop\Application\Controller\ThankYouController;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidEsales\EshopCommunity\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;
use oxRegistry;
use Thankyou;

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * @version    0.1
 * @author     Sunny <dt@marmalade.de>
 * @link       http://www.marmalade.de
 */

/**
 * This class generates tracking data for certain pages in OXID.
 * To add more pages create a new "generateFor<ControllerClass>" method.
 * For example <ControllerClass> can be "Content" for OXIDs CMS content controller.
 */
class MakairaTrackingDataGenerator
{
    public const TRACKER_URL = 'https://piwik.makaira.io/';

    /**
     * @var mixed
     */
    private static $odoScopeTracking = false;

    private const METHOD_MAP = [
        BasketController::class   => 'generateForBasket',
        ThankYouController::class => 'generateForThankYou',

    ];

    /**
     * Generates the tracking data array to send to piwik.
     * @param string $oxidControllerClass OXIDs controller class to generate the tracking data for.
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    public function generate(string $oxidControllerClass): array
    {
        $siteId = Registry::getConfig()->getShopConfVar(
            'makaira_tracking_page_id',
            null,
            'module:makaira_oxid-connect-essential'
        );

        if (empty($siteId)) {
            return [];
        }

        $childTrackingData = null;
        $methodName = self::METHOD_MAP[$oxidControllerClass] ?? 'UnknownController';

        if (is_callable([$this, $methodName]) && method_exists($this, $methodName)) {
            $childTrackingData = $this->{$methodName}();
        }

        if (Registry::getSession()->getBasket()->isNewItemAdded()) {
            $childTrackingData = $this->generateForBasket();
        }

        if (null === $childTrackingData) {
            $childTrackingData = [['trackPageView']];
        }

        $trackingData = [
            $childTrackingData,
            [
                ['enableLinkTracking'],
                ['setTrackerUrl', "{$this->getTrackerUrl()}/piwik.php"],
                ['setSiteId', $siteId],
            ],
            $this->getCustomTrackingData()
        ];

        if (self::$odoScopeTracking) {
            $trackingData[] = [
                ['trackEvent', 'odoscope', self::$odoScopeTracking['group'], self::$odoScopeTracking['data']]
            ];
        }

        // Send tracking to piwik if mak_experiments cookie is set
        if (!empty($_COOKIE['mak_experiments'])) {
            $experiments = json_decode($_COOKIE['mak_experiments'], true);
            if (is_array($experiments)) {
                $trackingData[] = array_map(
                    static fn ($experiment) => [
                        'trackEvent',
                        'abtesting',
                        $experiment['experiment'],
                        $experiment['variation']
                    ],
                    $experiments
                );
            }
        }

        return array_merge(...$trackingData);
    }

    public static function setOdoscopeData($odoscopeData)
    {
        self::$odoScopeTracking = $odoscopeData;
    }

    /**
     * Hook to add custom data to Piwik/Matomo tracking.
     * @return array
     */
    protected function getCustomTrackingData(): array
    {
        return [];
    }

    /**
     * Normalizes OXIDs controller class name for the method call.
     * @param string $className OXIDs controller class name.
     * @return string
     */
    protected function normalize(string $className): string
    {
        if (str_starts_with($className, 'ox')) {
            $className = preg_replace('/^ox/', '', $className);
        }

        return ucfirst($className);
    }

    /**
     * Generates tracking data for OXIDs "basket" controller or if a new item was added to the cart.
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function generateForBasket(): array
    {
        $cart = Registry::getSession()->getBasket();
        $cartData = $this->createCartTrackingData($cart);

        $cartData[] = [
            'trackEcommerceCartUpdate',
            $cart->getPrice()->getBruttoPrice(),
        ];

        return $cartData;
    }

    /**
     * Creates tracking data from the shopping cart. Used for cart updates and the order.
     * @param Basket $cart
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function createCartTrackingData(Basket $cart): array
    {
        $cartData = [];

        /** @var BasketItem $cartItem */
        foreach ($cart->getContents() as $cartItem) {
            $product = $cartItem->getArticle();
            $category = $product->getCategory();

            $cartData[] = [
                'addEcommerceItem',
                $product->oxarticles__oxartnum->value,
                $cartItem->getTitle(),
                $category->oxcategories__oxtitle->value,
                $cartItem->getUnitPrice()->getBruttoPrice(),
                $cartItem->getAmount(),
            ];
        }

        return $cartData;
    }

    /**
     * Generates tracking data for a completed order.
     *
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function generateForThankYou(): array
    {
        /** @var Thankyou $oxidController */
        $oxidController = Registry::getConfig()->getTopActiveView();

        $cart     = $oxidController->getBasket();
        $cartData = $this->createCartTrackingData($cart);
        $order    = $oxidController->getOrder();

        $cartData[] = [
            'trackEcommerceOrder',
            $order->oxorder__oxordernr->value,
            $order->getTotalOrderSum(),
            $cart->getDiscountedProductsBruttoPrice(),
            ($order->oxorder__oxartvatprice1->value + $order->oxorder__oxartvatprice2->value),
            ($order->getOrderDeliveryPrice()->getBruttoPrice() +
                $order->getOrderPaymentPrice()->getBruttoPrice() +
                $order->getOrderWrappingPrice()->getBruttoPrice()),
            $order->oxorder__oxdiscount->value,
        ];

        return $cartData;
    }

    /**
     * Returns the URL to Makaira's tracking tool.
     * @return string
     */
    public function getTrackerUrl(): string
    {
        return rtrim(self::TRACKER_URL, '/');
    }
}
