<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180829132502 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE private_message (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, recipient_id INT NOT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, is_unread TINYINT(1) NOT NULL, INDEX IDX_4744FC9BF675F31B (author_id), INDEX IDX_4744FC9BE92F8F78 (recipient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friends (account_source INT NOT NULL, account_target INT NOT NULL, INDEX IDX_21EE706978BEB100 (account_source), INDEX IDX_21EE7069615BE18F (account_target), PRIMARY KEY(account_source, account_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend_requests (account_source INT NOT NULL, account_target INT NOT NULL, INDEX IDX_EC63B01B78BEB100 (account_source), INDEX IDX_EC63B01B615BE18F (account_target), PRIMARY KEY(account_source, account_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE blocked_accounts (account_source INT NOT NULL, account_target INT NOT NULL, INDEX IDX_9EAF1BED78BEB100 (account_source), INDEX IDX_9EAF1BED615BE18F (account_target), PRIMARY KEY(account_source, account_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE private_message ADD CONSTRAINT FK_4744FC9BF675F31B FOREIGN KEY (author_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE private_message ADD CONSTRAINT FK_4744FC9BE92F8F78 FOREIGN KEY (recipient_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE friends ADD CONSTRAINT FK_21EE706978BEB100 FOREIGN KEY (account_source) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friends ADD CONSTRAINT FK_21EE7069615BE18F FOREIGN KEY (account_target) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_requests ADD CONSTRAINT FK_EC63B01B78BEB100 FOREIGN KEY (account_source) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_requests ADD CONSTRAINT FK_EC63B01B615BE18F FOREIGN KEY (account_target) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blocked_accounts ADD CONSTRAINT FK_9EAF1BED78BEB100 FOREIGN KEY (account_source) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE blocked_accounts ADD CONSTRAINT FK_9EAF1BED615BE18F FOREIGN KEY (account_target) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account ADD friend_request_policy INT NOT NULL, ADD private_message_policy INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE private_message');
        $this->addSql('DROP TABLE friends');
        $this->addSql('DROP TABLE friend_requests');
        $this->addSql('DROP TABLE blocked_accounts');
        $this->addSql('ALTER TABLE account DROP friend_request_policy, DROP private_message_policy');
    }
}
