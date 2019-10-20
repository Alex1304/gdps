<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180901224747 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE friend (id INT AUTO_INCREMENT NOT NULL, a_id INT NOT NULL, b_id INT NOT NULL, is_new_for_a TINYINT(1) NOT NULL, is_new_for_b TINYINT(1) NOT NULL, INDEX IDX_55EEAC613BDE5358 (a_id), INDEX IDX_55EEAC61296BFCB6 (b_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC613BDE5358 FOREIGN KEY (a_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61296BFCB6 FOREIGN KEY (b_id) REFERENCES account (id)');
        $this->addSql('DROP TABLE friends');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE friends (account_source INT NOT NULL, account_target INT NOT NULL, INDEX IDX_21EE706978BEB100 (account_source), INDEX IDX_21EE7069615BE18F (account_target), PRIMARY KEY(account_source, account_target)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friends ADD CONSTRAINT FK_21EE7069615BE18F FOREIGN KEY (account_target) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friends ADD CONSTRAINT FK_21EE706978BEB100 FOREIGN KEY (account_source) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE friend');
    }
}
