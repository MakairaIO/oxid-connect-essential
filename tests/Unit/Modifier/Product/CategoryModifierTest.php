<?php

namespace Makaira\OxidConnectEssential\Test\Unit\Modifier\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Makaira\OxidConnectEssential\Modifier\Product\CategoryModifier;
use Makaira\OxidConnectEssential\Type\Common\AssignedCategory;
use Makaira\OxidConnectEssential\Type\Product\Product;
use OxidEsales\TestingLibrary\UnitTestCase;

class CategoryModifierTest extends UnitTestCase
{
    public function testUnnested()
    {

        $resultMock = $this->createMock(Result::class);
        $resultMock->method('fetchAllAssociative')
            ->willReturnOnConsecutiveCalls(
                [
                    [
                        'catid'  => 'def',
                        'title'  => 'mysubcat',
                        'oxpos'  => 1,
                        'shopid' => 1,
                        'active' => 1,
                        'oxleft' => 5,
                        'oxright' => 7,
                        'oxrootid' => 42,
                    ],
                ],
                [
                    [
                        'title'  => 'mytitle',
                        'active'  => 1
                    ],
                ]
            );

        $databaseMock = $this->createMock(Connection::class);
        $databaseMock->method('executeQuery')
            ->withConsecutive(
                [$this->anything(), ['productId' => 'abc', 'productActive' => 1]],
                [$this->anything(), ['left' => 5, 'right' => 7, 'rootId' => 42]]
            )
            ->willReturn($resultMock);

        $product = new Product();
        $product->id = 'abc';
        $product->OXACTIVE = 1;

        $modifier = new CategoryModifier($databaseMock);

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
}
