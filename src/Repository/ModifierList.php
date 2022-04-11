<?php

namespace Makaira\OxidConnectEssential\Repository;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Event\ModifierCollectEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class ModifierList
{
    /**
     * @param                          $tag
     * @param EventDispatcherInterface $dispatcher
     * @param array<Modifier>          $modifiers
     */
    public function __construct($tag, EventDispatcherInterface $dispatcher, private array $modifiers)
    {
        $dispatcher->dispatch($tag, new ModifierCollectEvent($this));
    }

    /**
     * Add a modifier.
     *
     * @param Modifier $modifier
     */
    public function addModifier(Modifier $modifier)
    {
        $this->modifiers[] = $modifier;
    }

    /**
     * Apply modifiers to datum.
     *
     * @param Type $type
     *
     * @return Type
     */
    public function applyModifiers(Type $type, $docType)
    {
        foreach ($this->modifiers as $modifier) {
            if (defined('MAKAIRA_CONNECT_DEBUG_MODIFIER')) {
                $before = self::flatData(json_decode(json_encode($type), true));
            }

            $modifier->setDocType($docType);
            $type = $modifier->apply($type);

            if (defined('MAKAIRA_CONNECT_DEBUG_MODIFIER')) {
                $after = self::flatData(json_decode(json_encode($type), true));
                self::printCompare($before, $after, $modifier);
            }
        }

        return $type;
    }

    private static function flatData($data, $prefix = '', &$addTo = null)
    {
        if ($addTo === null) {
            $addTo = [];
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::flatData($value, $prefix . ($prefix ? '.' : '') . $key, $addTo);
            }
        } else {
            $addTo[$prefix] = json_encode($data);
        }

        return $addTo;
    }

    private static function printCompare($before, $after, $modifier)
    {
        echo '# Modifier: ' . get_class($modifier) . "\n";
        $missingKey = $new = array_diff_key($before, $after);
        $newKey = array_diff_key($after, $before);
        $new = array_diff_assoc($after, $before);
        if ($missingKey) {
            echo "## Deleted properties:\n";
            foreach ($missingKey as $key => $value) {
                echo '- ' . $key . ' = ' . $value . "\n";
            }
        }
        if ($newKey) {
            echo "## New properties:\n";
            foreach ($newKey as $key => $value) {
                echo '- ' . $key . ' = ' . $value . "\n";
            }
        }
        if ($new) {
            $p = true;
            foreach ($new as $key => $value) {
                if (!array_key_exists($key, $newKey)) {
                    if ($p) {
                        $p = false;
                        echo "## Updated properties:\n";
                    }
                    echo '- ' . $key . ' = ' . $value . ' (' . $before[$key] . ')' .  "\n";
                }
            }
        }
    }
}
