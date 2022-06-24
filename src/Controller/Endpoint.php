<?php

namespace Makaira\OxidConnectEssential\Controller;

use Makaira\OxidConnectEssential\HttpException;
use Makaira\OxidConnectEssential\Rpc\RpcService;
use Makaira\OxidConnectEssential\SymfonyContainerTrait;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function explode;
use function ini_set;

use const PHP_EOL;

/**
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class Endpoint extends FrontendController
{
    use SymfonyContainerTrait;

    public function render()
    {
        ini_set('html_errors', 'off');

        $container = $this->getSymfonyContainer();
        /** @var Request $request */
        $request   = $container->get('request');
        $response  = $this->handleRequest($request);

        $response->send();
        Registry::getSession()->freeze();

        exit(0);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handleRequest(Request $request): Response
    {
        $container  = $this->getSymfonyContainer();
        /** @var RpcService $rpcService */
        $rpcService = $container->get(RpcService::class);

        $exception = null;
        try {
            $responseContent = $rpcService->handleRequest($request);
            $statusCode      = 200;
        } catch (HttpException $exception) {
            $responseContent = ['message' => $exception->getMessage()];
            $statusCode      = $exception->getCode();
        } catch (Throwable $exception) {
            $responseContent = ['message' => $exception->getMessage()];
            $statusCode      = 500;
        }

        if ($exception instanceof Throwable && !Registry::getConfig()->isProductiveMode()) {
            $responseContent['file']  = $exception->getFile();
            $responseContent['line']  = $exception->getLine();
            $responseContent['stack'] = explode(PHP_EOL, $exception->getTraceAsString());
        }

        return new JsonResponse($responseContent, $statusCode);
    }
}
