<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Utils;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Exception;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class BoostFields
{
    /**
     * @var null|array
     */
    private ?array $minMaxValues = null;

    /**
     * BoostFieldStatistics constructor.
     *
     * @param Connection $connection
     */
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return array<string>
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function getMinMaxValues(): array
    {
        if (null === $this->minMaxValues) {
            /** @var Result $resultStatement */
            $resultStatement = $this->connection->executeQuery($this->getMinMaxQuery());

            /** @var array<string> $rawValues */
            $rawValues = (array) $resultStatement->fetchAssociative();

            $this->minMaxValues = $rawValues;
        }

        return $this->minMaxValues;
    }

    /**
     * @param float  $value
     * @param string $key
     * @param float  $maxInfluence
     *
     * @return float
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function normalize(float $value, string $key, float $maxInfluence = 1.0): float
    {
        $minMaxValues = $this->getMinMaxValues();
        $min = $minMaxValues["{$key}_min"];
        if ($min < 0) {
            $max = $this->scaleValue((float) $minMaxValues["{$key}_max"] - (float) $min);
            $scaled = $this->scaleValue($value - (float) $min);
            $min = 0;
        } else {
            $min = $this->scaleValue((float) $minMaxValues["{$key}_min"]);
            $max = $this->scaleValue((float) $minMaxValues["{$key}_max"]);
            $scaled = $this->scaleValue($value);
        }


        $diff = $max - $min;
        $normed = ($diff > 0) ? (($scaled - $min) / $diff) : 0;

        return $maxInfluence * $normed;
    }

    /**
     * @param string $value
     * @param string $key
     * @param float  $maxInfluence
     *
     * @return float
     * @throws DBALDriverException
     * @throws DBALException
     * @throws Exception
     */
    public function normalizeTimestamp(string $value, string $key, float $maxInfluence = 1.0): float
    {
        $minMaxValues = $this->getMinMaxValues();
        $max          = $minMaxValues["{$key}_max"];

        $timestamp            = new DateTime($value);
        $maxTimestamp         = new DateTime($max);
        $daysFromMaxTimestamp = (int) $maxTimestamp->diff($timestamp)->format('%r%a');

        $alpha  = 0.1;
        $factor = 60;

        // (0.5*(1+alpha*(x+x_zero)/(1+alpha*abs((x+x_zero))))+1/(2*(1+alpha*x_zero)))*max_influence
        return (
                0.5 * (
                    1 + $alpha * ($daysFromMaxTimestamp + $factor) /
                    (1 + $alpha * abs($factor + $daysFromMaxTimestamp))
                ) + 1 / (2 * (1 + $alpha * $factor))
            ) * $maxInfluence;
    }

    /**
     * @param float $value
     *
     * @return float
     */
    private function scaleValue(float $value): float
    {
        return $value >= 0 ? $value + 1 : 0;
    }

    /**
     * @return string
     */
    private function getMinMaxQuery(): string
    {
        return '
            SELECT
                MIN(OXSOLDAMOUNT) AS sold_min,
                MAX(OXSOLDAMOUNT) AS sold_max,
                MIN(OXRATING) AS rating_min,
                MAX(OXRATING) AS rating_max,
                MIN(OXVARMINPRICE) AS price_min,
                MAX(OXVARMAXPRICE) AS price_max,
                MAX(OXINSERT) AS insert_max,
                MIN(OXSOLDAMOUNT * OXVARMINPRICE) AS revenue_min,
                MAX(OXSOLDAMOUNT * OXVARMAXPRICE) AS revenue_max,
                MIN(IF(0=OXBPRICE,0,OXVARMINPRICE - OXBPRICE)) AS profit_margin_min,
                MAX(IF(0=OXBPRICE,0,OXVARMAXPRICE - OXBPRICE)) AS profit_margin_max
            FROM
                `oxarticles`
        ';
    }
}
