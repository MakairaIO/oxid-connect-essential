<?php

namespace Makaira\OxidConnectEssential\Rpc\Handler;

use Makaira\OxidConnectEssential\Rpc\HandlerInterface;
use OxidEsales\Eshop\Core\Language;

class ListLanguages implements HandlerInterface
{
    /**
     * @param Language $language
     */
    public function __construct(private Language $language)
    {
    }

    /**
     * @param array<string, mixed> $request
     *
     * @return array<string>
     */
    public function handle(array $request): array
    {
        return $this->language->getLanguageIds();
    }
}
