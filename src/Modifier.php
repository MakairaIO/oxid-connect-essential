<?php

namespace Makaira\OxidConnectEssential;

use Makaira\OxidConnectEssential\Event\ModifierCollectEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class Modifier
 *
 * @package Makaira\Connect
 * @SuppressWarnings(PHPMD)
 */
abstract class Modifier
{
    private ?string $docType = null;

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     */
    abstract public function apply(Type $type);

    /**
     * @return string
     */
    protected function getDocType(): string
    {
        return (string) $this->docType;
    }

    /**
     * @param $docType
     *
     * @return $this
     */
    public function setDocType($docType): static
    {
        $this->docType = $docType;

        return $this;
    }

    /**
     * @param Event $e
     *
     * @return $this
     */
    public function addModifier(Event $e): static
    {
        if ($e instanceof ModifierCollectEvent) {
            $e->addModifier($this);
        }

        return $this;
    }
}
