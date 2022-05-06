<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier\Common\ZeroDateTimeModifier;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\TestingLibrary\UnitTestCase;

class ZeroDateTimeModifierTest extends UnitTestCase
{
    public function testValidDateTime()
    {
        $modifier = new ZeroDateTimeModifier();

        $type = new Type();
        $type->timestamp = "2016-01-01 00:00:00";
        $this->assertEquals("2016-01-01 00:00:00", $modifier->apply($type)->timestamp);

        $type = new Type();
        $type->timestamp = "2016-01-01";
        $this->assertEquals("2016-01-01", $modifier->apply($type)->timestamp);
    }

    public function testInvalidDateTime()
    {
        $modifier = new ZeroDateTimeModifier();

        $type = new Type();
        $type->timestamp = "0000-00-00 00:00:00";
        $this->assertEquals(null, $modifier->apply($type)->timestamp);

        $type = new Type();
        $type->timestamp = "0000-00-00";
        $this->assertEquals(null, $modifier->apply($type)->timestamp);
    }

    public function testNonDateValues()
    {
        $modifier = new ZeroDateTimeModifier();
        $stringTestValue = 'some string';
        $arrayTestValue = [1,2,3];
        $boolTestValue = false;

        $type = new Type();
        $type->id = $stringTestValue;
        $type->active = $boolTestValue;
        $type->shop = $arrayTestValue;

        $this->assertEquals($stringTestValue, $modifier->apply($type)->id);

        $this->assertEquals($boolTestValue, $modifier->apply($type)->active);

        $this->assertEquals($arrayTestValue, $modifier->apply($type)->shop);
    }

    public function testInvalidDateTimeNested()
    {
        $modifier = new ZeroDateTimeModifier();

        $type = new Type();
        $type->additionalData['invalidTimestamp'] = "0000-00-00 00:00:00";
        $type->additionalData['someOtherProp'] = "test";
        $this->assertEquals(null, $modifier->apply($type)->additionalData['invalidTimestamp']);
        $this->assertEquals('test', $modifier->apply($type)->additionalData['someOtherProp']);

        $type = new Type();
        $type->additionalData['invalidTimestamp'] = "0000-00-00";
        $type->additionalData['someOtherProp'] = "test";
        $this->assertEquals(null, $modifier->apply($type)->additionalData['invalidTimestamp']);
        $this->assertEquals('test', $modifier->apply($type)->additionalData['someOtherProp']);
    }
}
