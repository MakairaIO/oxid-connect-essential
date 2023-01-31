<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier\Common\PriceModifier;
use Makaira\OxidConnectEssential\Type\Product\Product;
use PHPUnit\Framework\TestCase;

class PriceModifierTest extends TestCase
{
    private function productFactory(): Product
    {
        $product = new Product();
        $product->OXBPRICE = 10;
        $product->OXPRICE = 10;
        $product->OXTPRICE = 10;
        $product->OXPRICEA = 10;
        $product->OXPRICEB = 10;
        $product->OXPRICEC = 10;
        $product->OXUPDATEPRICE = 10;
        $product->OXVARMAXPRICE = 10;
        $product->OXVARMINPRICE = 10;
        $product->OXUPDATEPRICEA = 10;
        $product->OXUPDATEPRICEB = 10;
        $product->OXUPDATEPRICEC = 10;
        $product->OXVAT = null;

        return $product;
    }

    public function testBruttoBrutto(): void
    {
        $modifier = new PriceModifier(false, false, 16);
        $product = $modifier->apply($this->productFactory());
        $this->assertEquals(10, $product->OXPRICE);
    }

    public function testBruttoNetto(): void
    {
        $modifier = new PriceModifier(false, true, 16);
        $product = $modifier->apply($this->productFactory());
        $this->assertEquals(10, $product->OXPRICE);
    }

    public function testNettoBrutto(): void
    {
        $modifier = new PriceModifier(true, false, 16);
        $product = $modifier->apply($this->productFactory());
        $this->assertEquals(11.6, $product->OXPRICE);
    }

    public function testNettoNetto(): void
    {
        $modifier = new PriceModifier(true, true, 16);
        $product = $modifier->apply($this->productFactory());
        $this->assertEquals(10, $product->OXPRICE);
    }

    public function testAllPrices(): void
    {
        $modifier = new PriceModifier(true, false, 16);
        $product = $modifier->apply($this->productFactory());
        $this->assertEquals(11.6, $product->OXBPRICE);
        $this->assertEquals(11.6, $product->OXPRICE);
        $this->assertEquals(11.6, $product->OXTPRICE);
        $this->assertEquals(11.6, $product->OXPRICEA);
        $this->assertEquals(11.6, $product->OXPRICEB);
        $this->assertEquals(11.6, $product->OXPRICEC);
        $this->assertEquals(11.6, $product->OXUPDATEPRICE);
        $this->assertEquals(11.6, $product->OXVARMAXPRICE);
        $this->assertEquals(11.6, $product->OXVARMINPRICE);
        $this->assertEquals(11.6, $product->OXUPDATEPRICEA);
        $this->assertEquals(11.6, $product->OXUPDATEPRICEB);
        $this->assertEquals(11.6, $product->OXUPDATEPRICEC);
    }
}
