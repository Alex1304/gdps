<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180828191955 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, level_id INT NOT NULL, posted_at DATETIME NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_35DC7773F675F31B (author_id), INDEX IDX_35DC77735FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_comment_likes (level_comment_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_4B94C00BDFED62C2 (level_comment_id), INDEX IDX_4B94C00B99E6F5DF (player_id), PRIMARY KEY(level_comment_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_comment_dislikes (level_comment_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_545C63C7DFED62C2 (level_comment_id), INDEX IDX_545C63C799E6F5DF (player_id), PRIMARY KEY(level_comment_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_comment ADD CONSTRAINT FK_35DC7773F675F31B FOREIGN KEY (author_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE level_comment ADD CONSTRAINT FK_35DC77735FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level_comment_likes ADD CONSTRAINT FK_4B94C00BDFED62C2 FOREIGN KEY (level_comment_id) REFERENCES level_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_comment_likes ADD CONSTRAINT FK_4B94C00B99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_comment_dislikes ADD CONSTRAINT FK_545C63C7DFED62C2 FOREIGN KEY (level_comment_id) REFERENCES level_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_comment_dislikes ADD CONSTRAINT FK_545C63C799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE level_comment_likes DROP FOREIGN KEY FK_4B94C00BDFED62C2');
        $this->addSql('ALTER TABLE level_comment_dislikes DROP FOREIGN KEY FK_545C63C7DFED62C2');
        $this->addSql('DROP TABLE level_comment');
        $this->addSql('DROP TABLE level_comment_likes');
        $this->addSql('DROP TABLE level_comment_dislikes');
    }
}
