<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Utils;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Utils\BoostFields;
use PHPUnit\Framework\TestCase;

class BoostFieldsTest extends TestCase
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

    public function testCanNormalizeFieldWithNegativeRange(): void
    {
        $boostFields = new BoostFields($this->createDbMock('sold', -1, 10));
        $actual      = $boostFields->normalize(1, 'sold');
        $this->assertSame(0.25, $actual);
    }

    public function testCanNormalizeTimestamp(): void
    {
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAssociative')->willReturn([
            "insert_max" => '2022-05-02',
        ]);

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')->willReturn($resultMock);

        $boostFields = new BoostFields($databaseMock);
        $actual = $boostFields->normalizeTimestamp('2022-05-02', 'insert');

        $this->assertSame(1.0, $actual);
    }

    private function createDbMock(string $key, int $min, int $max): Connection
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
