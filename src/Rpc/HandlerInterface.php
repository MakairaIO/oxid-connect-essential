<?php

namespace Makaira\OxidConnectEssential\Rpc;

interface HandlerInterface
{
    public function handle(array $request): array;
}
