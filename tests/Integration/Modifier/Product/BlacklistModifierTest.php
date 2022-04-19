<?php
/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Test\Integration\Modifier\Product;

use Makaira\OxidConnectEssential\Modifier\Product\BlacklistModifier;
use Makaira\OxidConnectEssential\Type\Common\BaseProduct;
use OxidEsales\TestingLibrary\UnitTestCase;

class BlacklistModifierTest extends UnitTestCase
{
    private function productFactory($skipFields = [])
    {
        $product = new BaseProduct();
        $fieldValues = [
            'OXTITLE' => 'TITEL',
            'OXSHORTDESC' => 'SHORTDESC',
            'OXBPRICE' => 10,
            'OXPRICE' => 10,
            'OXTPRICE' => 10,
            'OXPRICEA' => 10,
            'OXPRICEB' => 10,
            'OXPRICEC' => 10,
            'OXUPDATEPRICE' => 10,
            'OXVARMAXPRICE' => 10,
            'OXVARMINPRICE' => 10,
            'OXUPDATEPRICEA' => 10,
            'OXUPDATEPRICEB' => 10,
            'OXUPDATEPRICEC' => 10,
            'OXVAT' => null,
            'SOMECUSTOMPROPERTY' => 'foo',
        ];
        foreach ($fieldValues as $property => $value) {
            $product->$property = $value;

            if (in_array($property, $skipFields)) {
                unset($product->$property);
                continue;
            }
        }

        return $product;
    }

    public function getTestBlacklistedFieldsData()
    {
        return [
            [
                $this->productFactory(),
                $this->productFactory(),
                []
            ],
            [
                $this->productFactory(),
                $this->productFactory(),
                ['NONEXISTINGFIELD']
            ],
            [
                $this->productFactory(),
                $this->productFactory(['OXTITLE']),
                ['OXTITLE']
            ],
            [
                $this->productFactory(),
                $this->productFactory(['SOMECUSTOMPROPERTY']),
                ['SOMECUSTOMPROPERTY']
            ],
            [
                $this->productFactory(),
                $this->productFactory(['OXTITLE', 'OXPRICE', 'OXUPDATEPRICE']),
                ['OXTITLE', 'OXPRICE', 'OXUPDATEPRICE']
            ],

        ];
    }

    /**
     * @dataProvider getTestBlacklistedFieldsData
     */
    public function testBlacklistedFields($product, $modifiedProduct, $blacklist)
    {
        $modifier = new BlacklistModifier($blacklist);
        $product = $modifier->apply($product);

        $this->assertEquals($modifiedProduct, $product);
    }
}
