<?php

namespace Makaira\OxidConnectEssential\Repository;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Traversable;

use function iterator_to_array;

class VariantModifierList extends ModifierList
{
    public function __construct(EventDispatcherInterface $dispatcher, iterable $modifiers)
    {
        if ($modifiers instanceof Traversable) {
            $modifiers = iterator_to_array($modifiers);
        }

        parent::__construct('makaira.importer.modifier.variant', $dispatcher, $modifiers);
    }
}
