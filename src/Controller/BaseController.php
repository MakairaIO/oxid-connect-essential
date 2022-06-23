<?php

namespace Makaira\OxidConnectEssential\Controller;

use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class BaseController extends FrontendController
{
    use SymfonyContainerTrait;

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
        $container = $this->getSymfonyContainer();
        $request = $container->get('request');
        $body = (string) $request->getContent(false);

        return (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return User
     */
    protected function checkAndGetActiveUser(): User
    {
        /** @var User|false $user */
        $container = $this->getSymfonyContainer();
        $session = $container->get(Session::class);
        $user = $session->getUser();
        if ($user === false) {
            $this->sendResponse(["message" => "Unauthorized"], 401);
            exit;
        }

        return $user;
    }
}
