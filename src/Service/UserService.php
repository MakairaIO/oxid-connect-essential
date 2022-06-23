<?php

namespace Makaira\OxidConnectEssential\Service;

use Makaira\OxidConnectEssential\Exception\UserBlockedException;
use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\CookieException;
use OxidEsales\Eshop\Core\Exception\UserException;
use OxidEsales\Eshop\Core\Session;

class UserService
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @throws UserBlockedException
     * @throws UserException
     * @throws CookieException Do not remove this because of "never thrown" warning
     */
    public function login(string $username, string $password, bool $rememberLogin): User
    {
        /** @var User $user */
        $user = oxNew(User::class);

        $user->login($username, $password, $rememberLogin);

        // this user is blocked, deny him
        if ($user->inGroup('oxidblocked')) {
            throw new UserBlockedException('User blocked');
        }

        // after login
        if ($this->session->isSessionStarted()) {
            $this->session->regenerateSessionId();
        }

        // recalculate basket
        $basket = $this->session->getBasket();
        if ($basket instanceof Basket) {
            $basket->onUpdate();
        }

        return $user;
    }

    public function logout(): void
    {
        $user = oxNew(User::class);
        $user->logout();

        // after logout
        $this->session->deleteVariable('paymentid');
        $this->session->deleteVariable('sShipSet');
        $this->session->deleteVariable('deladrid');
        $this->session->deleteVariable('dynvalue');

        // resetting & recalculate basket
        $basket = $this->session->getBasket();
        if ($basket instanceof Basket) {
            $basket->resetUserInfo();
            $basket->onUpdate();
        }

        $this->session->delBasket();
    }

    /**
     * @return false|User|null
     */
    public function getCurrentLoggedInUser()
    {
        return $this->session->getUser();
    }
}
