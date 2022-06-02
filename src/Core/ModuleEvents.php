<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Makaira\OxidConnectEssential\Utils\ModuleSettingsProvider;
use OxidEsales\DoctrineMigrationWrapper\MigrationsBuilder;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

use function in_array;
use function strtolower;

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
        $migrations->execute('migrations:migrate', ModuleSettingsProvider::MODULE_ID);
    }
}
