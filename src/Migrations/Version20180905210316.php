<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180905210316 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_score (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, account_id INT NOT NULL, percent INT NOT NULL, coins INT NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_584032585FB14BA7 (level_id), INDEX IDX_584032589B6B5FBA (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_score ADD CONSTRAINT FK_584032585FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level_score ADD CONSTRAINT FK_584032589B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE level_score');
    }
}
