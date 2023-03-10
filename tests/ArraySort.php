<?php

namespace Makaira\OxidConnectEssential\Test;

use function array_reverse;
use function count;

class ArraySort
{
    public const ASCENDING = 0;

    public const DESCENDING = 1;

    public static function mergeSort(array $array, array $sorting = [0 => self::ASCENDING]): array
    {
        $sorted = $array;
        $self = new static();
        $sorting = array_reverse($sorting, true);

        foreach ($sorting as $key => $direction) {
            $sorted = $self->doMergeSort($sorted, $key, $direction === self::ASCENDING);
        }

        return $sorted;
    }

    private function doMergeSort(array $array, string|int $key, bool $asc): array
    {
        if (count($array) <= 1) {
            return $array;
        }

        $mid   = count($array) / 2;
        $left  = array_slice($array, 0, $mid);
        $right = array_slice($array, $mid);
        $left  = $this->doMergeSort($left, $key, $asc);
        $right = $this->doMergeSort($right, $key, $asc);

        return $this->merge($left, $right, $key, $asc);
    }

    private function merge(array $left, array $right, string|int $key, bool $asc): array
    {
        $res = [];

        $cmp = static fn ($arrayA, $arrayB) => strcmp(
            $asc ? $arrayA[$key] : $arrayB[$key],
            $asc ? $arrayB[$key] : $arrayA[$key],
        );

        while (count($left) > 0 && count($right) > 0) {
            if ($cmp($left[0], $right[0]) > 0) {
                $res[] = $right[0];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[0];
                $left  = array_slice($left, 1);
            }
        }

        while (count($left) > 0) {
            $res[] = $left[0];
            $left  = array_slice($left, 1);
        }

        while (count($right) > 0) {
            $res[] = $right[0];
            $right = array_slice($right, 1);
        }

        return $res;
    }
}
