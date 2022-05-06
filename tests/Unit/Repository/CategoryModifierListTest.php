<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Repository\CategoryModifierList;
use Makaira\OxidConnectEssential\Type;
use OxidEsales\TestingLibrary\UnitTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CategoryModifierListTest extends UnitTestCase
{
    public function testApplyModifier()
    {
        $modifierMock = $this->createMock(Modifier::class);
        $type = new Type();

        $modifierMock
            ->expects($this->once())
            ->method('apply')
            ->with($type)
            ->willReturn($type);

        $modifierList = new CategoryModifierList(new EventDispatcher(), [$modifierMock]);
        $result = $modifierList->applyModifiers($type, 'category');

        $this->assertSame($type, $result);
    }
}
