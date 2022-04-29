<?php

namespace Makaira\OxidConnectEssential\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends FrontendController
{
    public function render(): string
    {
        return '';
    }

    protected function sendResponse(array $content, int $status = 200): void
    {
        $response = new JsonResponse($content, $status);
        $response->send();
    }

    protected function getRequestBody(): array
    {
        $request = Request::createFromGlobals();
        $body = (string) $request->getContent(false);

        return (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return User
     */
    protected function checkAndGetActiveUser(): Userŝ
    {
        /** @var User|false $user */
        $user = Registry::getSession()->getUser();
        if ($user === false) {
            $this->sendResponse(["message" => "Unauthorized"], 401);
            exit;
        }

        return $user;
    }
}
