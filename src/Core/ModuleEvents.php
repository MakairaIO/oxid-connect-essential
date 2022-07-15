<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Core;

use Exception;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use Symfony\Component\Console\Output\BufferedOutput;

final class ModuleEvents
{
    /**
     * @return void
     * @throws Exception
     */
    public static function onActivate(): void
    {
        self::executeMigrations();
    }

    private static function executeMigrations(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $needsUpdate = $migrations->execute('migrations:up-to-date', ModuleSettingsProvider::MODULE_ID);

        if ($needsUpdate) {
            $migrations->execute('migrations:migrate', ModuleSettingsProvider::MODULE_ID);
        }
    }
}
