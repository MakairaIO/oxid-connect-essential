<?php

namespace Makaira\OxidConnectEssential\Controller\Admin;

use function ltrim;
use function method_exists;

trait PSR12WrapperTrait
{
    /**
     * @param string $method
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function callPSR12Incompatible(string $method, ...$args)
    {
        $newMethod  = ltrim($method, '_');
        $callMethod = method_exists($this, $newMethod) ? $newMethod : $method;

        return $this->{$callMethod}(...$args);
    }
}
