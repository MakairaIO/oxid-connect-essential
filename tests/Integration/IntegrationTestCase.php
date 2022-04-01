<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use Makaira\OxidConnectEssential\Service\UserService;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\TestingLibrary\UnitTestCase;

class IntegrationTestCase extends UnitTestCase
{
    protected function loginToTestingUser(): User
    {
        $userService = new UserService();
        return $userService->login('dev@marmalade.de', 'mGXW6qpWhQTEx-wX_!D7', false);
    }
}
