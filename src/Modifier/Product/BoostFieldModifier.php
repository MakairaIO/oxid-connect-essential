<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Modifier\Product;

use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Exception as DBALException;
use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\BoostFields;

class BoostFieldModifier extends Modifier
{
    /**
     * BoostFieldModifier constructor.
     *
     * @param BoostFields $boostFields
     */
    public function __construct(private BoostFields $boostFields)
    {
    }

    /**
     * Modify product and return modified product
     *
     * @param Type\Product\Product $type
     *
     * @return Type
     * @throws DBALDriverException
     * @throws DBALException
     */
    public function apply(Type $type)
    {
        /** @var string $insertDate */
        $insertDate = $type->additionalData['OXINSERT'];

        $type->mak_boost_norm_insert = $this->boostFields->normalizeTimestamp((string) $insertDate, 'insert');

        $type->mak_boost_norm_sold   = $this->boostFields->normalize(
            (float) $type->additionalData['OXSOLDAMOUNT'],
            'sold'
        );
        $type->mak_boost_norm_rating = $this->boostFields->normalize(
            (float) $type->additionalData['OXRATING'],
            'rating'
        );

        $priceAverage =
            ((float) $type->additionalData['OXVARMINPRICE'] + (float) $type->additionalData['OXVARMAXPRICE']) / 2;

        $type->mak_boost_norm_revenue = $this->boostFields->normalize(
            (float) ($priceAverage * (float) $type->additionalData['OXSOLDAMOUNT']),
            'revenue'
        );

        $type->mak_boost_norm_profit_margin = $this->boostFields->normalize(
            (0.0 === round((float) $type->additionalData['OXBPRICE'])) ? 0 :
                ($priceAverage - (float) $type->additionalData['OXBPRICE']),
            'profit_margin'
        );

        return $type;
    }
}
