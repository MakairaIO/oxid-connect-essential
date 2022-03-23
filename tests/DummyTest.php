<?php
/**
 * This file is part of Makaira.
 *
 * @see https://makaira.io
 */

namespace Makaira\OxidConnectEssential\Test;

use Makaira\OxidConnectEssential\Dummy;
use PHPUnit\Framework\TestCase;

/**
 * Dummy test case to set up sonarcloud.
 */
class DummyTest extends TestCase
{
    /**
     * Dummy test to set up sonarcloud.
     *
     * @return void
     */
    public function testDummyTest(): void
    {
        $o      = new Dummy();
        $actual = $o->add(4, 2);
        self::assertSame(6, $actual);
    }
}
