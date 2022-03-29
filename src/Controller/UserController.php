<?php

namespace Makaira\OxidConnectEssential\Controller;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;

class UserController extends BaseController
{
    #[NoReturn]
    public function login()
    {
        ['username' => $username, 'password' => $password, 'rememberLogin' => $rememberLogin] = $this->getRequestBody();

        $httpCode = 200;
        try {
            /** @var User $user */
            $user = oxNew(User::class);
            $response = [
                'success' => $user->login($username, $password, $rememberLogin)
            ];
        } catch (Exception $userException) {
            $response = [
                'success' => false,
                'message' => $userException->getMessage()
            ];
            $httpCode = 500;
        }

        // after login
        $session = Registry::getSession();
        if ($session->isSessionStarted()) {
            $session->regenerateSessionId();
        }

        // this user is blocked, deny him
        if ($user->inGroup('oxidblocked')) {
            $response = [
                'success' => false,
                'message' => 'USER_BLOCKED'
            ];
            $httpCode = 403;
        }

        // recalc basket
        if ($basket = $session->getBasket()) {
            $basket->onUpdate();
        }

        $this->sendResponse($response, $httpCode);
    }

    #[NoReturn]
    public function logout()
    {
        $user = oxNew(User::class);
        $user->logout();

        // after logout
        Registry::getSession()->deleteVariable('paymentid');
        Registry::getSession()->deleteVariable('sShipSet');
        Registry::getSession()->deleteVariable('deladrid');
        Registry::getSession()->deleteVariable('dynvalue');

        // resetting & recalc basket
        if (($basket = Registry::getSession()->getBasket())) {
            $basket->resetUserInfo();
            $basket->onUpdate();
        }

        Registry::getSession()->delBasket();

        $this->sendResponse(["success" => true]);
    }

    #[NoReturn]
    public function getUser()
    {
        $user = Registry::getSession()->getUser();
        if ($user) {
            $this->sendResponse([
                'id' => $user->getFieldData('oxid'),
                'firstname' => $user->getFieldData('oxfname'),
                'lastname' => $user->getFieldData('oxlname'),
                'email' => $user->getFieldData('oxusername'),
            ]);
        } else {
            $this->sendResponse([
                'message' => 'Forbidden'
            ], 403);
        }
    }
}
