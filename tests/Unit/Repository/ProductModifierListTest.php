<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Repository\ProductModifierList;
use Makaira\OxidConnectEssential\Type;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProductModifierListTest extends TestCase
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

        $modifierList = new ProductModifierList(new EventDispatcher(), []);
        $modifierList->addModifier($modifierMock);
        $result = $modifierList->applyModifiers($type, 'product');

        $this->assertSame($type, $result);
    }
}
