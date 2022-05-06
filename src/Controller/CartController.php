<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use Makaira\OxidConnectEssential\Exception\InvalidCartItem;
use Makaira\OxidConnectEssential\Service\CartService;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

class CartController extends BaseController
{
    private CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        /** @var CartService $cartService */
        $cartService = ContainerFactory::getInstance()->getContainer()->get(CartService::class);
        $this->cartService = $cartService;
    }

    public function getCartItems(): void
    {
        try {
            $this->sendResponse($this->cartService->getCartItems());
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addProductToCart(): void
    {
        ['product_id' => $productId, 'amount' => $amount] = $this->getRequestBody();

        try {
            $this->cartService->addProductToCart($productId, $amount);
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

    public function updateCartItem(): void
    {
        ['cart_item_id' => $cartItemId, 'amount' => $amount] = $this->getRequestBody();

        try {
            $this->cartService->updateCartItem($cartItemId, $amount);

            $this->sendResponse([
                'success' => true,
            ]);
        } catch (InvalidCartItem $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function removeCartItem(): void
    {
        ['cart_item_id' => $cartItemId] = $this->getRequestBody();

        try {
            $this->cartService->removeCartItem($cartItemId);

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
