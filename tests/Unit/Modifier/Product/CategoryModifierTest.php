<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Product\CategoryModifier;
use Makaira\OxidConnectEssential\Test\TableTranslatorTrait;
use Makaira\OxidConnectEssential\Type\Common\AssignedCategory;
use Makaira\OxidConnectEssential\Type\Product\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function in_array;
use function json_encode;

use const JSON_PRETTY_PRINT;

class CategoryModifierTest extends TestCase
{
    use TableTranslatorTrait;

    private static ?Result $resultMock = null;

    public function testUnnested(): void
    {
        $databaseMock = $this->createMock(Connection::class);
        $databaseMock
            ->method('executeQuery')
            ->willReturnCallback(
                function (...$args) {
                    static $expectedParams = [
                        ['productId' => 'abc', 'productActive' => true],
                        ['left' => 5, 'right' => 7, 'rootId' => 42]
                    ];

                    $this->assertContains($args[1], $expectedParams);

                    static $resultMock = null;

                    if (null === $resultMock) {
                        $resultMock = $this->createResultMock();
                    }

                    return $resultMock;
                }
            );

        $product = new Product();
        $product->id = 'abc';
        $product->OXACTIVE = 1;

        $modifier = new CategoryModifier($databaseMock, $this->getTableTranslatorMock());

        $product = $modifier->apply($product);

        $this->assertEquals(
            [
                new AssignedCategory(
                    [
                        'catid'  => 'def',
                        'pos'  => 1,
                        'shopid' => 1,
                        'path' => 'mytitle/',
                        'title' => 'mysubcat'
                    ]
                ),
            ],
            $product->category
        );
    }

    /**
     * @return Result|(Result&MockObject)|MockObject
     */
    private function createResultMock()
    {
        if (self::$resultMock === null) {
            self::$resultMock = $this->createMock(Result::class);
            self::$resultMock->method('fetchAllAssociative')
                ->willReturnOnConsecutiveCalls(
                    [
                        [
                            'catid'    => 'def',
                            'title'    => 'mysubcat',
                            'oxpos'    => 1,
                            'shopid'   => 1,
                            'active'   => 1,
                            'oxleft'   => 5,
                            'oxright'  => 7,
                            'oxrootid' => 42,
                        ],
                    ],
                    [
                        [
                            'title'  => 'mytitle',
                            'active' => 1
                        ],
                    ],
                    [
                        [
                            'title'  => 'mytitle_inactive',
                            'active' => 0
                        ],
                    ],
                );
        }

        return self::$resultMock;
    }
}
