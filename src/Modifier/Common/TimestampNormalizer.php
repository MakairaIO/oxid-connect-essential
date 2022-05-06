<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use DateTimeImmutable;
use DateTimeInterface;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class TimestampNormalizer extends Modifier
{
    public function apply(Type $type)
    {
        if ($type->timestamp) {
            $type->timestamp = (new DateTimeImmutable($type->timestamp))->format(DateTimeInterface::ATOM);
        }

        return $type;
    }
}
