<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Service;

use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\EshopCommunity\Core\Registry;

class UserServiceTest extends IntegrationTestCase
{
    public function test()
    {
        $userService = new UserService(Registry::getSession());
        // User not logged in yet
        self::assertFalse($userService->getCurrentLoggedInUser());

        // Login
        $this->loginToTestingUser();
        $user = $userService->getCurrentLoggedInUser();
        self::assertNotFalse($user);
        self::assertEquals(
            [
                "id" => "oxdefaultadmin",
                "firstname" => "John",
                "lastname" => "Doe",
                "email" => "dev@marmalade.de"
            ],
            [
                'id' => $user->getFieldData('oxid'),
                'firstname' => $user->getFieldData('oxfname'),
                'lastname' => $user->getFieldData('oxlname'),
                'email' => $user->getFieldData('oxusername'),
            ]
        );

        // Logout
        $userService->logout();
        self::assertFalse($userService->getCurrentLoggedInUser());
    }
}
