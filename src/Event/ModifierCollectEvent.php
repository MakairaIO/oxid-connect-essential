<?php

/**
 * This file is part of a Makaira GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Martin Schnabel <ms@marmalade.group>
 * Author URI: https://www.makaira.io/
 */

namespace Makaira\OxidConnectEssential\Event;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Repository\ModifierList;
use Symfony\Contracts\EventDispatcher\Event;

class ModifierCollectEvent extends Event
{
    public function __construct(private ModifierList $modifierList)
    {
    }

    public function addModifier(Modifier $modifier): void
    {
        $this->modifierList->addModifier($modifier);
    }
}
