<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Service;

use Makaira\OxidConnectEssential\Service\CartService;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;

class CartServiceTest extends IntegrationTestCase
{
    public function test()
    {
        // Empty cart at first time
        $cartService = new CartService();
        self::assertEquals([], $cartService->getCartItems());

        // Add a product to cart
        $cartService->addProductToCart('b56597806428de2f58b1c6c7d3e0e093', 20);
        self::assertEquals([
            [
                "cart_item_id" => "5f62f5daee9ecb4b4389918a2b070643",
                "name" => "Kite NBK EVO",
                "price" => 699.0,
                "base_price" => "699",
                "quantity" => 20.0,
                "image_path" => 'https://oxid64.makaira.vm/out/pictures/generated/product/1/87_87_75/nkb_evo_2010_1.jpg'
            ]
        ], $cartService->getCartItems());

        // Add a product to cart
        $cartService->updateCartItem('5f62f5daee9ecb4b4389918a2b070643', 15);
        self::assertEquals([
            [
                "cart_item_id" => "5f62f5daee9ecb4b4389918a2b070643",
                "name" => "Kite NBK EVO",
                "price" => 699.0,
                "base_price" => "699",
                "quantity" => 15.0,
                "image_path" => 'https://oxid64.makaira.vm/out/pictures/generated/product/1/87_87_75/nkb_evo_2010_1.jpg'
            ]
        ], $cartService->getCartItems());

        // Add a product to cart
        $cartService->removeCartItem('5f62f5daee9ecb4b4389918a2b070643');
        self::assertEquals([], $cartService->getCartItems());
    }
}
