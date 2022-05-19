<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Module;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception as DBALDriverException;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

use function in_array;
use function strtolower;

class Events
{
    /**
     * @var array|null
     */
    private static ?array $columnCache = null;

    /**
     * @var Connection|null
     */
    private static ?Connection $db = null;

    /**
     * @return void
     * @throws DBALDriverException
     * @throws DBALException
     * @throws Exception
     */
    public static function onActivate(): void
    {
        $db = self::getDb();
        $db->executeQuery(
            'CREATE TABLE IF NOT EXISTS `makaira_connect_changes` (
                `SEQUENCE` BIGINT NOT NULL AUTO_INCREMENT,
                `TYPE` VARCHAR(32) COLLATE latin1_general_ci NOT NULL,
                `OXID` CHAR(32) COLLATE latin1_general_ci NOT NULL,
                `CHANGED` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`OXID`),
                PRIMARY KEY (`SEQUENCE`),
                UNIQUE KEY `uniqueChanges` (`TYPE`, `OXID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci'
        );

        if (!self::hasColumn('oxobject2category', 'OXSHOPID')) {
            $db->executeQuery('ALTER TABLE oxobject2category ADD COLUMN OXSHOPID INT(11) NOT NULL DEFAULT 1');
        }

        if (!self::hasColumn('oxartextends', 'OXTAGS')) {
            $sSql = "ALTER TABLE oxartextends
                ADD OXTAGS VARCHAR(255) NOT NULL COMMENT 'Tags (multilanguage)',
                ADD OXTAGS_1 varchar(255) NOT NULL,
                ADD OXTAGS_2 varchar(255) NOT NULL,
                ADD OXTAGS_3 varchar(255) NOT NULL";
            $db->executeQuery($sSql);
        }
    }

    /**
     * Checks if $column exists in $table
     *
     * @param string $table
     * @param string $column
     *
     * @return bool true if $column exists in $table, else false
     * @throws DBALDriverException
     * @throws DBALException
     */
    private static function hasColumn(string $table, string $column): bool
    {
        if (null === self::$columnCache) {
            $dbName = Registry::getConfig()->getConfigParam('dbName');

            $db = self::getDb();

            /** @var Result $resultStatement */
            $resultStatement = $db->executeQuery(
                "SELECT LOWER(c.`TABLE_NAME`) `table`, LOWER(c.`COLUMN_NAME`) `column`
                 FROM `information_schema`.`COLUMNS` c
                LEFT JOIN `information_schema`.`TABLES` t
                    ON t.`TABLE_SCHEMA` = c.`TABLE_SCHEMA` AND
                       t.`TABLE_NAME` = c.`TABLE_NAME`
                 WHERE 
                    c.`TABLE_SCHEMA` = ? AND
                    t.`TABLE_TYPE` = 'BASE TABLE'",
                [$dbName]
            );

            $tableColumns = $resultStatement->fetchAllAssociative();

            foreach ($tableColumns as $tableColumn) {
                self::$columnCache[$tableColumn['table']][] = $tableColumn['column'];
            }
        }

        $table  = strtolower($table);
        $column = strtolower($column);

        return isset(self::$columnCache[$table]) && in_array($column, self::$columnCache[$table], true);
    }

    /**
     * @return Connection
     * @throws Exception
     */
    private static function getDb(): Connection
    {
        if (null === self::$db) {
            $container = (new ContainerBuilderFactory())->create()->getContainer();
            $container->compile();

            /** @var Connection $db */
            $db = $container->get(Connection::class);
            self::$db = $db;
        }

        return self::$db;
    }
}
