<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191025172918 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_data (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, data LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_C4D608725FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_data ADD CONSTRAINT FK_C4D608725FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
		$this->addSql('INSERT INTO level_data (data, level_id) SELECT data, id FROM level');
        $this->addSql('ALTER TABLE level DROP data');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE level ADD data LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
		$this->addSql('UPDATE level l, level_data d SET l.data = d.data WHERE d.level_id = l.id');
        $this->addSql('DROP TABLE level_data');
    }
}
