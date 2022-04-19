<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use Makaira\OxidConnectEssential\Service\UserService;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

abstract class IntegrationTestCase extends UnitTestCase
{
    protected function loginToTestingUser(): User
    {
        $userService = new UserService(Registry::getSession());
        return $userService->login('dev@marmalade.de', 'mGXW6qpWhQTEx-wX_!D7', false);
    }
}
