<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Repository\CategoryModifierList;
use Makaira\OxidConnectEssential\Type;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CategoryModifierListTest extends TestCase
{
    public function testApplyModifier(): void
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
