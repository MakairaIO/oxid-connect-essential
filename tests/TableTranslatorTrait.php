<?php

namespace Makaira\OxidConnectEssential\Test;

use Makaira\OxidConnectEssential\Utils\TableTranslator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait TableTranslatorTrait
{
    /**
     * @return TableTranslator|MockObject
     */
    protected function getTableTranslatorMock()
    {
        $mockBuilder = $this->getMockBuilder(TableTranslator::class)
            ->setMethodsExcept(['translate'])
            ->setConstructorArgs(
                [
                    [
                        'oxarticles',
                        'oxartextends',
                        'oxattribute',
                        'oxcategories',
                        'oxmanufacturers',
                        'oxobject2attribute',
                    ],
                ]
            );

        return $mockBuilder->getMock();
    }
}
