<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Utils\BoostFields;
use OxidEsales\TestingLibrary\UnitTestCase;

class BoostFieldsTest extends UnitTestCase
{
    public function testCanNormalizeFieldWithRange100(): void
    {
        $boostFields = new BoostFields($this->createDbMock('sold', 0, 100));
        $actual      = $boostFields->normalize(255, 'sold');
        $this->assertSame(2.55, $actual);
    }

    public function testCanNormalizeFieldWithRange0(): void
    {
        $boostFields = new BoostFields($this->createDbMock('sold', 0, 0));
        $actual      = $boostFields->normalize(255, 'sold');
        $this->assertSame(0.0, $actual);
    }

    private function createDbMock(string $key, int $min, int $max)
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAssociative')->willReturn([
            "{$key}_min" => $min,
            "{$key}_max" => $max,
        ]);

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')->willReturn($resultMock);

        return $databaseMock;
    }
}
