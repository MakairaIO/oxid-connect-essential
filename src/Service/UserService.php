<?php

namespace Makaira\OxidConnectEssential\Service;

use Makaira\OxidConnectEssential\Exception\UserBlockedException;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\CookieException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Session;

class UserService
{
    public function __construct(private Session $session)
    {
    }

    /**
     * @throws UserBlockedException
     * @throws UserException
     * @throws CookieException Do not remove this because of "never thrown" warning
     */
    public function login($username, $password, $rememberLogin): User
    {
        /** @var User $user */
        $user = oxNew(User::class);
        // this user is blocked, deny him
        if ($user->inGroup('oxidblocked')) {
            throw new UserBlockedException('User blocked');
        }

        $user->login($username, $password, $rememberLogin);

        // after login
        if ($this->session->isSessionStarted()) {
            $this->session->regenerateSessionId();
        }

        // recalculate basket
        if ($basket = $this->session->getBasket()) {
            $basket->onUpdate();
        }

        return $user;
    }

    public function logout()
    {
        $user = oxNew(User::class);
        $user->logout();

        // after logout
        $this->session->deleteVariable('paymentid');
        $this->session->deleteVariable('sShipSet');
        $this->session->deleteVariable('deladrid');
        $this->session->deleteVariable('dynvalue');

        // resetting & recalculate basket
        if (($basket = $this->session->getBasket())) {
            $basket->resetUserInfo();
            $basket->onUpdate();
        }

        $this->session->delBasket();
    }

    public function getCurrentLoggedInUser(): false|User|null
    {
        return $this->session->getUser();
    }
}
