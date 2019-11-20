<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191120222143 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comment_ban (id INT AUTO_INCREMENT NOT NULL, moderator_id INT NOT NULL, target_id INT NOT NULL, expires_at DATETIME NOT NULL, reason LONGTEXT NOT NULL, INDEX IDX_8D6C90D5D0AFA354 (moderator_id), INDEX IDX_8D6C90D5158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment_ban ADD CONSTRAINT FK_8D6C90D5D0AFA354 FOREIGN KEY (moderator_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE comment_ban ADD CONSTRAINT FK_8D6C90D5158E0B66 FOREIGN KEY (target_id) REFERENCES player (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE comment_ban');
    }
}
