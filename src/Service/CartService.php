<?php

namespace Makaira\OxidConnectEssential\Service;

use Makaira\OxidConnectEssential\Exception\InvalidCartItem;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\BasketItem;
use OxidEsales\Eshop\Core\Exception\ArticleException;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;
use OxidEsales\Eshop\Core\Exception\OutOfStockException;

class CartService
{
    public function __construct(private Basket $basket)
    {
    }

    /**
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     * @throws ArticleException
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
    public function addProductToCart(string $productId, float $amount): void
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
    public function updateCartItem(string $cartItemId, float $amount): void
    {
        $basketItems = $this->basket->getContents();
        if (!isset($basketItems[$cartItemId])) {
            throw new InvalidCartItem("Invalid cart_item_id: {$cartItemId}");
        }
        // Update cart
        /**@var Article $article*/
        $article = $basketItems[$cartItemId];
        $this->basket->addToBasket($article->getProductId(), $amount, null, null, true);

        $this->basket->calculateBasket();
    }

    public function removeCartItem(string $cartItemId): void
    {
        $this->basket->removeItem($cartItemId);
        $this->basket->calculateBasket();
    }
}
