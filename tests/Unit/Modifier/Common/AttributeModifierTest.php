<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Common;

use PHPUnit\Framework\TestCase;

class AttributeModifierTest extends TestCase
{
    public function testApply(): void
    {
        //
    }

    /*public function testApply()
    {
        $dbMock = $this->getMock(DatabaseInterface::class);
        $oxid = 'abcdef';
        $dbResult = [
            'id' => $oxid,
            'title' => 'abcdef',
            'value' => 'abcdef'
        ];
        $dbMock
            ->expects($this->at(0))
            ->method('query')
            ->will($this->returnValue([$dbResult]));

        $dbMock
            ->expects($this->at(1))
            ->method('query')
            ->will($this->returnValue([]));

        $dbMock
            ->expects($this->at(2))
            ->method('query')
            ->will($this->returnValue([["oxvarname" => "qwert"]]));

        $modifier = new AttributeModifier($dbMock, '1', [], []);

        $product = $modifier->apply(new Product(['id' => $oxid, 'active' => 1]));

        $this->assertArraySubset(
            [new AssignedTypedAttribute([
                 'id'    => $dbResult['id'],
                 'title' => $dbResult['title'],
                 'value' => $dbResult['value'],
            ])],
            $product->attributeStr
        );
    }*/
}
