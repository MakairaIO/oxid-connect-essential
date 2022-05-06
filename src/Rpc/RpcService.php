<?php

namespace Makaira\OxidConnectEssential\Rpc;

use JsonException;
use Makaira\OxidConnectEssential\Exception;
use Makaira\OxidConnectEssential\HttpException;
use Symfony\Component\HttpFoundation\Request;

use function json_decode;
use function lcfirst;

class RpcService
{
    /**
     * @var array<HandlerInterface>
     */
    private array $rpcHandlers;

    private SignatureCheck $signatureCheck;

    /**
     * @param SignatureCheck             $signatureCheck
     * @param iterable<HandlerInterface> $rpcHandlers
     */
    public function __construct(SignatureCheck $signatureCheck, iterable $rpcHandlers)
    {
        $this->signatureCheck = $signatureCheck;
        foreach ($rpcHandlers as $rpcHandler) {
            $class = get_class($rpcHandler);
            $name  = basename(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class));

            $this->rpcHandlers[lcfirst($name)] = $rpcHandler;
        }
    }

    /**
     * @param Request $request
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function handleRequest(Request $request): array
    {
        if (!$this->signatureCheck->hasHeaders($request)) {
            throw new HttpException(401);
        }

        if (!$this->signatureCheck->verifyRequest($request)) {
            throw new HttpException(403);
        }

        try {
            /** @var string $body */
            $body        = $request->getContent(false);
            /** @var array $requestBody */
            $requestBody = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new HttpException(400);
        }

        $action = $requestBody['action'];
        if (!isset($action)) {
            throw new HttpException(400);
        }

        if (!isset($this->rpcHandlers[$action])) {
            throw new HttpException(404);
        }

        return $this->rpcHandlers[$action]->handle($requestBody);
    }
}
