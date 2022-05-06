<?php

namespace Makaira\OxidConnectEssential\Rpc;

use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use Makaira\Signing\HashGenerator;
use Symfony\Component\HttpFoundation\Request;

class SignatureCheck
{
    public const HEADER_NONCE     = 'X-Makaira-Nonce';
    public const HEADER_SIGNATURE = 'X-Makaira-Hash';

    private HashGenerator $hashGenerator;

    private ModuleSettingsProvider $moduleSettings;

    /**
     * @param HashGenerator          $hashGenerator
     * @param ModuleSettingsProvider $moduleSettings
     */
    public function __construct(HashGenerator $hashGenerator, ModuleSettingsProvider $moduleSettings)
    {
        $this->moduleSettings = $moduleSettings;
        $this->hashGenerator  = $hashGenerator;
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function hasHeaders(Request $request): bool
    {
        return $request->headers->has(static::HEADER_NONCE) && $request->headers->has(static::HEADER_SIGNATURE);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function verifyRequest(Request $request): bool
    {
        return $this->verify(
            (string) $request->headers->get(static::HEADER_NONCE),
            (string) $request->getContent(false),
            (string) $request->headers->get(static::HEADER_SIGNATURE)
        );
    }

    /**
     * @param string $nonce
     * @param string $body
     * @param string $signature
     *
     * @return bool
     */
    public function verify(string $nonce, string $body, string $signature): bool
    {
        $expectedHash = $this->hashGenerator->hash($nonce, $body, $this->moduleSettings->get('makaira_connect_secret'));

        return $expectedHash === $signature;
    }
}
