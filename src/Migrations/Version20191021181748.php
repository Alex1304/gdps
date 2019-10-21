<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191021181748 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE chest (id INT AUTO_INCREMENT NOT NULL, cooldown INT NOT NULL, min_orbs INT NOT NULL, max_orbs INT NOT NULL, min_diamonds INT NOT NULL, max_diamonds INT NOT NULL, min_shards INT NOT NULL, max_shards INT NOT NULL, min_keys INT NOT NULL, max_keys INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql("INSERT INTO chest VALUES (NULL, '14400', '20', '50', '1', '4', '0', '1', '5'), (NULL, '86400', '100', '300', '4', '10', '1', '2', '25');");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE chest');
    }
}
