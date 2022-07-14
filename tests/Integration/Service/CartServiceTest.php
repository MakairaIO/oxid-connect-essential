<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Service;

use Makaira\OxidConnectEssential\Exception\InvalidCartItem;
use Makaira\OxidConnectEssential\Service\CartService;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\Eshop\Application\Model\Basket;

use function array_map;
use function preg_replace;

class CartServiceTest extends IntegrationTestCase
{
    public function test()
    {
        // Empty cart at first time
        $cartService = new CartService(new Basket());
        self::assertEquals([], $cartService->getCartItems());

        // Add a product to cart
        $cartService->addProductToCart('b56597806428de2f58b1c6c7d3e0e093', 20);
        $expected = [
            [
                "cart_item_id" => "5f62f5daee9ecb4b4389918a2b070643",
                "name"         => "Kite NBK EVO 2010",
                "price"        => 699.0,
                "base_price"   => "699",
                "quantity"     => 20.0,
                "image_path"   => '/out/pictures/generated/product/1/87_87_75/nkb_evo_2010_1.jpg',
            ],
        ];

        $actual = $cartService->getCartItems();
        $actual = array_map(
            static function ($entry) {
                $entry['image_path'] = preg_replace('@^.*(/out/pictures/)@', '$1', $entry['image_path']);

                return $entry;
            },
            $actual
        );

        self::assertEquals($expected, $actual);

        // Add a product to cart
        $cartService->updateCartItem('5f62f5daee9ecb4b4389918a2b070643', 15);
        $expected = [
            [
                "cart_item_id" => "5f62f5daee9ecb4b4389918a2b070643",
                "name"         => "Kite NBK EVO 2010",
                "price"        => 699.0,
                "base_price"   => "699",
                "quantity"     => 15.0,
                "image_path"   => '/out/pictures/generated/product/1/87_87_75/nkb_evo_2010_1.jpg',
            ],
        ];

        $actual = $cartService->getCartItems();
        $actual = array_map(
            static function ($entry) {
                $entry['image_path'] = preg_replace('@^.*(/out/pictures/)@', '$1', $entry['image_path']);

                return $entry;
            },
            $actual
        );

        self::assertEquals($expected, $actual);

        // Add a product to cart
        $cartService->removeCartItem('5f62f5daee9ecb4b4389918a2b070643');
        self::assertEquals([], $cartService->getCartItems());
    }

    /**
     * @return void
     * @throws InvalidCartItem
     * @throws \OxidEsales\Eshop\Core\Exception\ArticleInputException
     * @throws \OxidEsales\Eshop\Core\Exception\NoArticleException
     * @throws \OxidEsales\Eshop\Core\Exception\OutOfStockException
     */
    public function testThrowExceptionOnInvalidCartItemId()
    {
        $cartService = new CartService(new Basket());

        $this->expectException(InvalidCartItem::class);
        $this->expectExceptionMessage("Invalid cart_item_id: phpunit42");

        $cartService->updateCartItem('phpunit42', 42);
    }
}
