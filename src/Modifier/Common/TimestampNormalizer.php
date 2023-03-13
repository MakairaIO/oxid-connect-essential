<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use DateTimeImmutable;
use DateTimeInterface;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class TimestampNormalizer extends Modifier
{
    private DateTimeInterface $zeroDate;

    public function __construct()
    {
        $this->zeroDate = new DateTimeImmutable('0001-01-01 00:00:00');
    }

    public function apply(Type $type)
    {
        if ($type->timestamp) {
            $timestamp = new DateTimeImmutable($type->timestamp);
            if ($timestamp < $this->zeroDate) {
                $timestamp = $this->zeroDate;
            }

            $type->timestamp = $timestamp->format('Y-m-d H:i:s');
        }

        return $type;
    }
}
