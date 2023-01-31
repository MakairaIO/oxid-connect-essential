<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Repository\ManufacturerModifierList;
use Makaira\OxidConnectEssential\Type;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ManufacturerModifierListTest extends TestCase
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

        $modifierList = new ManufacturerModifierList(new EventDispatcher(), [$modifierMock]);
        $result = $modifierList->applyModifiers($type, 'manufacturer');

        $this->assertSame($type, $result);
    }
}
