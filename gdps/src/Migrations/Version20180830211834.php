<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180830211834 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE account_comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, content LONGTEXT NOT NULL, posted_at DATETIME NOT NULL, INDEX IDX_5D5F4624F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_comment_likes (account_comment_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_7F76EA3F4F28FB7 (account_comment_id), INDEX IDX_7F76EA3F99E6F5DF (player_id), PRIMARY KEY(account_comment_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account_comment_dislikes (account_comment_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_565161C34F28FB7 (account_comment_id), INDEX IDX_565161C399E6F5DF (player_id), PRIMARY KEY(account_comment_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account_comment ADD CONSTRAINT FK_5D5F4624F675F31B FOREIGN KEY (author_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE account_comment_likes ADD CONSTRAINT FK_7F76EA3F4F28FB7 FOREIGN KEY (account_comment_id) REFERENCES account_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_comment_likes ADD CONSTRAINT FK_7F76EA3F99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_comment_dislikes ADD CONSTRAINT FK_565161C34F28FB7 FOREIGN KEY (account_comment_id) REFERENCES account_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE account_comment_dislikes ADD CONSTRAINT FK_565161C399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_comment_likes DROP FOREIGN KEY FK_7F76EA3F4F28FB7');
        $this->addSql('ALTER TABLE account_comment_dislikes DROP FOREIGN KEY FK_565161C34F28FB7');
        $this->addSql('DROP TABLE account_comment');
        $this->addSql('DROP TABLE account_comment_likes');
        $this->addSql('DROP TABLE account_comment_dislikes');
    }
}
