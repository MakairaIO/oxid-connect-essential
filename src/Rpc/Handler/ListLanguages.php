<?php

namespace Makaira\OxidConnectEssential\Rpc\Handler;

use Makaira\OxidConnectEssential\Rpc\HandlerInterface;
use OxidEsales\Eshop\Core\Language;

class ListLanguages implements HandlerInterface
{
    private Language $language;

    /**
     * @param Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
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
