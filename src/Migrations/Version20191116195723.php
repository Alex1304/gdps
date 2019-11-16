<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191116195723 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE level_score ADD periodic_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE level_score ADD CONSTRAINT FK_5840325879B05C5B FOREIGN KEY (periodic_id) REFERENCES periodic_level (id)');
        $this->addSql('CREATE INDEX IDX_5840325879B05C5B ON level_score (periodic_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE level_score DROP FOREIGN KEY FK_5840325879B05C5B');
        $this->addSql('DROP INDEX IDX_5840325879B05C5B ON level_score');
        $this->addSql('ALTER TABLE level_score DROP periodic_id');
    }
}
