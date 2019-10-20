<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180826211631 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_star_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_79F88D325FB14BA7 (level_id), INDEX IDX_79F88D3299E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_star_votes ADD CONSTRAINT FK_79F88D325FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_star_votes ADD CONSTRAINT FK_79F88D3299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE level_difficulty_votes');
        $this->addSql('ALTER TABLE level ADD difficulty INT NOT NULL, ADD voted_demon INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_difficulty_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_660400BB5FB14BA7 (level_id), INDEX IDX_660400BB99E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_difficulty_votes ADD CONSTRAINT FK_660400BB5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_difficulty_votes ADD CONSTRAINT FK_660400BB99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE level_star_votes');
        $this->addSql('ALTER TABLE level DROP difficulty, DROP voted_demon');
    }
}
