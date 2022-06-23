<?php

namespace Makaira\OxidConnectEssential\Controller;

use Makaira\OxidConnectEssential\Exception\UserBlockedException;
use Makaira\OxidConnectEssential\Service\UserService;
use OxidEsales\Eshop\Core\Exception\CookieException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class UserController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        /** @var UserService $userService */
        $userService       = ContainerFactory::getInstance()->getContainer()->get(UserService::class);
        $this->userService = $userService;
    }
    public function login(): void
    {
        ['username' => $username, 'password' => $password, 'rememberLogin' => $rememberLogin] = $this->getRequestBody();

        try {
            $this->userService->login($username, $password, $rememberLogin);
            $this->sendResponse([
                'success' => true
            ]);
        } catch (UserException | UserBlockedException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (CookieException $e) {
            $this->sendResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function logout(): void
    {
        $this->userService->logout();

        $this->sendResponse(["success" => true]);
    }

    public function getCurrentLoggedInUser(): void
    {
        $user = $this->userService->getCurrentLoggedInUser();
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
