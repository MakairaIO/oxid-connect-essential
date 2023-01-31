<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Type\Common;

use Makaira\OxidConnectEssential\Type\Common\AssignedTypedAttribute;
use PHPUnit\Framework\TestCase;

use function md5;

class AssignedTypedAttributeTest extends TestCase
{
    public function testItReturnsItsIdOnStringCast(): void
    {
        $typedAttribute = new AssignedTypedAttribute(
            ['id' => 'phpunit_id', 'title' => 'PHPUnit Title', 'value' => 42]
        );

        $expected = md5('phpunit_id42');

        $this->assertSame($expected, (string) $typedAttribute);
    }
}
