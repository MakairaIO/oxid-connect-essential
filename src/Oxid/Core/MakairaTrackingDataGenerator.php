<?php

namespace Makaira\OxidConnectEssential\Oxid\Core;

use OxidEsales\Eshop\Application\Controller\BasketController;
use OxidEsales\Eshop\Application\Controller\ThankYouController;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\OrderArticle;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\EshopCommunity\Application\Model\Basket;
use OxidEsales\EshopCommunity\Application\Model\BasketItem;
use OxidEsales\EshopCommunity\Core\Registry;

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
        /** @var string $siteId */
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

    /**
     * Hook to add custom data to Piwik/Matomo tracking.
     * @return array
     */
    protected function getCustomTrackingData(): array
    {
        return [];
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
            /** @var Article|OrderArticle $product */
            $product = $cartItem->getArticle();
            if ($product instanceof OrderArticle) {
                $product = $product->getArticle();
            }
            /** @var Category $category */
            $category = $product->getCategory();

            $cartData[] = [
                'addEcommerceItem',
                $product->getFieldData('oxarticles__oxartnum'),
                $cartItem->getTitle(),
                $category->getFieldData('oxcategories__oxtitle'),
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
        /** @var ThankYouController $oxidController */
        $oxidController = Registry::getConfig()->getTopActiveView();

        $cart     = $oxidController->getBasket();
        $cartData = $this->createCartTrackingData($cart);
        /** @var Order $order */
        $order    = $oxidController->getOrder();

        $cartData[] = [
            'trackEcommerceOrder',
            $order->getFieldData('oxorder__oxordernr'),
            $order->getTotalOrderSum(),
            $cart->getDiscountedProductsBruttoPrice(),
            ($order->getFieldData('oxorder__oxartvatprice1') + $order->getFieldData('oxorder__oxartvatprice2')),
            ($order->getOrderDeliveryPrice()->getBruttoPrice() +
                $order->getOrderPaymentPrice()->getBruttoPrice() +
                $order->getOrderWrappingPrice()->getBruttoPrice()),
            $order->getFieldData('oxorder__oxdiscount'),
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
