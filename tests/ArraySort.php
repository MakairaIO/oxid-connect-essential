<?php

namespace Makaira\OxidConnectEssential\Test;

use function count;

trait ArraySortTrait
{
    protected function mergeSort(array $array, array $sorting = [0 => 'asc']): array
    {
        foreach ($sorting as $key => $direction) {
            $array = $this->doMergeSort($array, $key, $direction);
        }

        return $array;
    }

    private function doMergeSort(array $array, string|int $key, string $direction = 'asc'): array
    {
        if (count($array) == 1) {
            return $array;
        }

        $mid   = count($array) / 2;
        $left  = array_slice($array, 0, $mid);
        $right = array_slice($array, $mid);
        $left  = $this->doMergeSort($left, $key, $direction);
        $right = $this->doMergeSort($right, $key, $direction);

        return $this->merge($left, $right, $key, $direction);
    }

    private function merge(array $left, array $right, string|int $key, string $direction = 'asc'): array
    {
        $res = [];

        $cmp = static fn ($arrayA, $arrayB) => strcmp(
            $direction === 'asc' ? $arrayA[$key] : $arrayB[$key],
            $direction === 'asc' ? $arrayB[$key] : $arrayA[$key],
        );

        while (count($left) > 0 && count($right) > 0) {
            if ($cmp($left, $right) > 0) {
                $res[] = $right[$key];
                $right = array_slice($right, 1);
            } else {
                $res[] = $left[$key];
                $left  = array_slice($left, 1);
            }
        }

        while (count($left) > 0) {
            $res[] = $left[$key];
            $left  = array_slice($left, 1);
        }

        while (count($right) > 0) {
            $res[] = $right[$key];
            $right = array_slice($right, 1);
        }

        return $res;
    }
}
