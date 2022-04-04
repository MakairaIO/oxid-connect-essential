<?php

namespace Makaira\OxidConnectEssential\Module;

use Doctrine\DBAL\Connection;
use Exception;
use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

class Events
{
    /**
     * @return void
     * @throws Exception
     */
    public static function onActivate(): void
    {
        $container = (new ContainerBuilderFactory())->create()->getContainer();
        $container->compile();

        /** @var Connection $db */
        $db = $container->get(Connection::class);
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
    }
}
