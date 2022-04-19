<?php

namespace Makaira\OxidConnectEssential\Test;

use Makaira\OxidConnectEssential\Utils\TableTranslator;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait TableTranslatorTrait
{
    protected function getTableTranslatorMock(): TableTranslator
    {
        return $this->createMock(
            TableTranslator::class,
            ['translate'],
            [['oxarticles', 'oxartextends', 'oxattribute', 'oxcategories', 'oxmanufacturers', 'oxobject2attribute']]
        );
    }
}
