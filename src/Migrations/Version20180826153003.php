<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180826153003 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, original_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, data LONGTEXT NOT NULL, audio_track INT NOT NULL, custom_song_id INT NOT NULL, stars INT NOT NULL, feature_score INT NOT NULL, is_epic TINYINT(1) NOT NULL, game_version INT NOT NULL, version INT NOT NULL, requested_stars INT NOT NULL, uploaded_at DATETIME NOT NULL, last_updated_at DATETIME NOT NULL, length INT NOT NULL, is_ldm TINYINT(1) NOT NULL, is_unlisted TINYINT(1) NOT NULL, password INT NOT NULL, object_count INT NOT NULL, extra_string VARCHAR(255) NOT NULL, is_two_player TINYINT(1) NOT NULL, coins INT NOT NULL, INDEX IDX_9AEACC1361220EA6 (creator_id), INDEX IDX_9AEACC13108B7592 (original_id), INDEX levelsearch_idx (name), INDEX featured_idx (feature_score), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_downloads (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_CD2DDFC45FB14BA7 (level_id), INDEX IDX_CD2DDFC499E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_likes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_23134B745FB14BA7 (level_id), INDEX IDX_23134B7499E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_dislikes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_A05A1BC15FB14BA7 (level_id), INDEX IDX_A05A1BC199E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_difficulty_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_660400BB5FB14BA7 (level_id), INDEX IDX_660400BB99E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_demon_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_58A829935FB14BA7 (level_id), INDEX IDX_58A8299399E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC1361220EA6 FOREIGN KEY (creator_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE level ADD CONSTRAINT FK_9AEACC13108B7592 FOREIGN KEY (original_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level_downloads ADD CONSTRAINT FK_CD2DDFC45FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_downloads ADD CONSTRAINT FK_CD2DDFC499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_likes ADD CONSTRAINT FK_23134B745FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_likes ADD CONSTRAINT FK_23134B7499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_dislikes ADD CONSTRAINT FK_A05A1BC15FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_dislikes ADD CONSTRAINT FK_A05A1BC199E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_difficulty_votes ADD CONSTRAINT FK_660400BB5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_difficulty_votes ADD CONSTRAINT FK_660400BB99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_demon_votes ADD CONSTRAINT FK_58A829935FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_demon_votes ADD CONSTRAINT FK_58A8299399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE level DROP FOREIGN KEY FK_9AEACC13108B7592');
        $this->addSql('ALTER TABLE level_downloads DROP FOREIGN KEY FK_CD2DDFC45FB14BA7');
        $this->addSql('ALTER TABLE level_likes DROP FOREIGN KEY FK_23134B745FB14BA7');
        $this->addSql('ALTER TABLE level_dislikes DROP FOREIGN KEY FK_A05A1BC15FB14BA7');
        $this->addSql('ALTER TABLE level_difficulty_votes DROP FOREIGN KEY FK_660400BB5FB14BA7');
        $this->addSql('ALTER TABLE level_demon_votes DROP FOREIGN KEY FK_58A829935FB14BA7');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE level_downloads');
        $this->addSql('DROP TABLE level_likes');
        $this->addSql('DROP TABLE level_dislikes');
        $this->addSql('DROP TABLE level_difficulty_votes');
        $this->addSql('DROP TABLE level_demon_votes');
    }
}
