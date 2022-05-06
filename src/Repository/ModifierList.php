<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Event\ModifierCollectEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class ModifierList
{
    private array $modifiers;

    /**
     * @param string                   $tag
     * @param EventDispatcherInterface $dispatcher
     * @param array<Modifier>          $modifiers
     */
    public function __construct(string $tag, EventDispatcherInterface $dispatcher, array $modifiers)
    {
        $this->modifiers = $modifiers;
        $dispatcher->dispatch($tag, new ModifierCollectEvent($this));
    }

    /**
     * Add a modifier.
     *
     * @param Modifier $modifier
     */
    public function addModifier(Modifier $modifier): void
    {
        $this->modifiers[] = $modifier;
    }

    /**
     * Apply modifiers to datum.
     *
     * @param Type   $type
     * @param string $docType
     *
     * @return Type
     */
    public function applyModifiers(Type $type, string $docType): Type
    {
        foreach ($this->modifiers as $modifier) {
            $modifier->setDocType($docType);
            $type = $modifier->apply($type);
        }

        return $type;
    }
}
