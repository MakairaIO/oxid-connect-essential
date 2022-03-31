<?php

namespace Makaira\OxidConnectEssential\Service;

use Makaira\OxidConnectEssential\Exception\InvalidCartItem;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;

class CartService
{
    private Session $session;
    private Basket $basket;

    public function __construct()
    {
        $this->session = Registry::getSession();
        $this->basket = $this->session->getBasket();
    }

    /**
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    public function getCartItems(): array
    {
        $cartItems = [];
        foreach ($this->basket->getContents() as $itemId => $basketItem) {
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

        return $cartItems;
    }

    /**
     * @throws OutOfStockException
     * @throws NoArticleException
     * @throws ArticleInputException
     */
    public function addProductToCart($productId, $amount)
    {
        $this->basket->addToBasket($productId, $amount);
        $this->basket->calculateBasket();
    }

    /**
     * @throws OutOfStockException
     * @throws NoArticleException
     * @throws ArticleInputException
     * @throws InvalidCartItem
     */
    public function updateCartItem($cartItemId, $amount)
    {
        $basketItems = $this->basket->getContents();
        if (!isset($basketItems[$cartItemId])) {
            throw new InvalidCartItem("Invalid cart_item_id: {$cartItemId}");
        }
        // Update cart
        /**@var Article $article*/
        $article = $basketItems[$cartItemId];
        $this->basket->addToBasket(sProductID: $article->getProductId(), dAmount: $amount, blOverride: true);

        $this->basket->calculateBasket();
    }

    public function removeCartItem($cartItemId)
    {
        $this->basket->removeItem($cartItemId);
        $this->basket->calculateBasket();
    }
}
