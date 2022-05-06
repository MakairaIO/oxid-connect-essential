<?php

namespace Makaira\OxidConnectEssential\Test\Integration\Service;

use Exception;
use Makaira\OxidConnectEssential\Service\UserService;
use Makaira\OxidConnectEssential\Test\Integration\IntegrationTestCase;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
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

    public function testThrowsExceptionIfUserIsBlocked()
    {
        $userService = new UserService(Registry::getSession());

        $blockedUser = new User();
        $blockedUser->setPassword('PHPUnit');
        $blockedUser->assign(
            [
                'oxid' => null,
                'oxusername' => 'BlockedUser',
            ]
        );
        $blockedUser->save();
        $blockedUser->addToGroup('oxidblocked');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User blocked');
        $userService->login('BlockedUser', 'PHPUnit', false);
    }

    public function testSessionIsRefreshedIfAlreadyStarted()
    {
        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('isSessionStarted')
            ->willReturn(true);
        $session
            ->expects($this->once())
            ->method('regenerateSessionId');

        $this->loginToTestingUser($session);
    }
}
