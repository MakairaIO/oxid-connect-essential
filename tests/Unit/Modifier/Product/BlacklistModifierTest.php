<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Product;

use Makaira\OxidConnectEssential\Modifier\Product\BlacklistModifier;
use Makaira\OxidConnectEssential\Type\Product\Product;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use PHPUnit\Framework\TestCase;

class BlacklistModifierTest extends TestCase
{
    private function productFactory($skipFields = []): Product
    {
        $product = new Product();
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
            }
        }

        return $product;
    }

    public function getTestBlacklistedFieldsData(): array
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
    public function testBlacklistedFields($product, $modifiedProduct, $blacklist): void
    {
        $moduleSettingsMock = $this->createMock(ModuleSettingsProvider::class);
        $moduleSettingsMock->method('get')->willReturn($blacklist);

        $modifier = new BlacklistModifier($moduleSettingsMock);
        $product = $modifier->apply($product);

        $this->assertEquals($modifiedProduct, $product);
    }
}
