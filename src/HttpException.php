<?php

namespace Makaira\OxidConnectEssential;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.UndefinedVariable)
 */
class HttpException extends Exception
{
    public function __construct(int $code = 200, ?Throwable $previous = null)
    {
        parent::__construct(Response::$statusTexts[$code] ?? '', $code, $previous);
    }
}
