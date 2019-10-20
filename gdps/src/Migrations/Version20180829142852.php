<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180829142852 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE friend_requests');
        $this->addSql('ALTER TABLE account CHANGE player_id player_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE friend_requests (account_source INT NOT NULL, account_target INT NOT NULL, INDEX IDX_EC63B01B78BEB100 (account_source), INDEX IDX_EC63B01B615BE18F (account_target), PRIMARY KEY(account_source, account_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friend_requests ADD CONSTRAINT FK_EC63B01B615BE18F FOREIGN KEY (account_target) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_requests ADD CONSTRAINT FK_EC63B01B78BEB100 FOREIGN KEY (account_source) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account CHANGE player_id player_id INT NOT NULL');
    }
}
