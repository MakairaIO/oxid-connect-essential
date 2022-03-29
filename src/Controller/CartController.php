<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Registry;

class CartController extends BaseController
{
    /**
     * @throws Exception
     */
    #[NoReturn]
    public function getCart()
    {
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $basketItems = $basket->getContents();

        $cartItems = [];

        foreach ($basketItems as $itemId => $basketItem) {
            /**@var BasketItem $basketItem*/
            $cartItems[] = [
                'cart_item_id' => $itemId,
                'name' => $basketItem->getTitle(),
                'price' => $basketItem->getPrice(),
                'base_price' => $basketItem->getArticle()->getBasePrice(),
                'quantity' => $basketItem->getAmount(),
                'image_path' => $basketItem->getIconUrl()
            ];
        }

        $response = [
            'cart_items' => $cartItems
        ];

        $this->sendResponse($response);
    }

    public function addProductToCart()
    {
        ['product_id' => $productId, 'amount' => $amount] = $this->getRequestBody();

        $session = Registry::getSession();
        $basket = $session->getBasket();

        try {
            $basket->addToBasket($productId, $amount);
            $basket->calculateBasket();

            $this->sendResponse([
                'success' => true,
                'itemCount' => $basket->getItemsCount(),
                'totalSum' => $basket->getBruttoSum()
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCartItem()
    {
        ['cart_item_id' => $cartItemId, 'count' => $count] = $this->getRequestBody();

        $session = Registry::getSession();
        $basket = $session->getBasket();

        try {
            $basketItems = $basket->getContents();
            if (!isset($basketItems[$cartItemId])) {
                $this->sendResponse([
                    'message' => 'Invalid cart_item_id.'
                ], 400);
            }
            // Update cart
            /**@var Article $article*/
            $article = $basketItems[$cartItemId];
            $basket->addToBasket(sProductID: $article->getProductId(), dAmount: $count, sOldBasketItemId: $cartItemId);

            $basket->calculateBasket();

            $this->sendResponse([
                'success' => true,
                'itemCount' => $basket->getItemsCount(),
                'totalSum' => $basket->getBruttoSum()
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function removeCartItem()
    {
        ['cart_item_id' => $cartItemId] = $this->getRequestBody();

        $session = Registry::getSession();
        $basket = $session->getBasket();

        try {
            $basket->removeItem($cartItemId);
            $basket->calculateBasket();

            $this->sendResponse([
                'success' => true,
                'itemCount' => $basket->getItemsCount(),
                'totalSum' => $basket->getBruttoSum()
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
