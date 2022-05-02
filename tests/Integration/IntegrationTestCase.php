<?php

namespace Makaira\OxidConnectEssential\Test\Integration;

use Makaira\OxidConnectEssential\Service\UserService;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

abstract class IntegrationTestCase extends UnitTestCase
{
    protected function loginToTestingUser(Session $session = null): User
    {
        if (null === $session) {
            $session = Registry::getSession();
        }

        return (new UserService($session))->login('dev@marmalade.de', 'mGXW6qpWhQTEx-wX_!D7', false);
    }
}
