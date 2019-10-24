<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024212102 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE opened_chest (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, orbs INT NOT NULL, diamonds INT NOT NULL, shards INT NOT NULL, demon_keys INT NOT NULL, opened_at DATETIME NOT NULL, type INT NOT NULL, INDEX IDX_5B12CA4699E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE opened_chest ADD CONSTRAINT FK_5B12CA4699E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE player DROP next_small_chest_at, DROP next_big_chest_at, DROP last_small_chest_count, DROP last_big_chest_count, DROP mana_orbs_collected_from_chests');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE opened_chest');
        $this->addSql('ALTER TABLE player ADD next_small_chest_at DATETIME NOT NULL, ADD next_big_chest_at DATETIME NOT NULL, ADD last_small_chest_count INT NOT NULL, ADD last_big_chest_count INT NOT NULL, ADD mana_orbs_collected_from_chests BIGINT NOT NULL');
    }
}
