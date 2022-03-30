<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Registry;

class CartController extends BaseController
{
    /**
     * @throws Exception
     */
    public function getCartItems()
    {
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $basketItems = $basket->getContents();

        $cartItems = [];

        foreach ($basketItems as $itemId => $basketItem) {
            /**@var BasketItem $basketItem*/
            $article = $basketItem->getArticle();
            $cartItems[] = [
                'cart_item_id' => $itemId,
                'name' => $basketItem->getTitle(),
                'price' => $article->getPrice()->getPrice(),
                'base_price' => $article->getBasePrice(),
                'quantity' => $basketItem->getAmount(),
                'image_path' => $basketItem->getIconUrl()
            ];
        }

        $this->sendResponse($cartItems);
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
        ['cart_item_id' => $cartItemId, 'amount' => $amount] = $this->getRequestBody();

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
            $basket->addToBasket(sProductID: $article->getProductId(), dAmount: $amount, blOverride: true);

            $basket->calculateBasket();

            $this->sendResponse([
                'success' => true,
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
            ]);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
