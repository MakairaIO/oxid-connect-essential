<?php

namespace Makaira\OxidConnectEssential\Test;

use Makaira\OxidConnectEssential\Dummy;
use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testDummyTest(): void
    {
        $o      = new Dummy();
        $actual = $o->add(4, 2);
        self::assertSame(6, $actual);
    }
}
