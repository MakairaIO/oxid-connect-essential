<?php

namespace Makaira\OxidConnectEssential\Entity;

use Doctrine\DBAL\Connection;

class ChangesRepository
{
    public function __construct()
    {
    }

    public function getChanges(array $revisions): array
    {
        return [];
    }
}
