<?php

namespace Makaira\OxidConnectEssential\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20220530141944 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        if (!$schema->hasTable('makaira_connect_changes')) {
            $sql = 'CREATE TABLE IF NOT EXISTS `makaira_connect_changes` (
                `SEQUENCE` BIGINT NOT NULL AUTO_INCREMENT,
                `TYPE` VARCHAR(32) COLLATE latin1_general_ci NOT NULL,
                `OXID` CHAR(32) COLLATE latin1_general_ci NOT NULL,
                `CHANGED` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`OXID`),
                PRIMARY KEY (`SEQUENCE`),
                UNIQUE KEY `uniqueChanges` (`TYPE`, `OXID`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci';
            $this->addSql($sql);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}
