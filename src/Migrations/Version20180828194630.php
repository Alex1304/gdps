<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180828194630 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_demon_vote (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, player_id INT NOT NULL, demon_value INT NOT NULL, INDEX IDX_CF0FF2685FB14BA7 (level_id), INDEX IDX_CF0FF26899E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_star_vote (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, player_id INT NOT NULL, star_value INT NOT NULL, INDEX IDX_2B5FE65C5FB14BA7 (level_id), INDEX IDX_2B5FE65C99E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_demon_vote ADD CONSTRAINT FK_CF0FF2685FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level_demon_vote ADD CONSTRAINT FK_CF0FF26899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE level_star_vote ADD CONSTRAINT FK_2B5FE65C5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE level_star_vote ADD CONSTRAINT FK_2B5FE65C99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('DROP TABLE level_demon_votes');
        $this->addSql('DROP TABLE level_star_votes');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE level_demon_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_58A829935FB14BA7 (level_id), INDEX IDX_58A8299399E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level_star_votes (level_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_79F88D325FB14BA7 (level_id), INDEX IDX_79F88D3299E6F5DF (player_id), PRIMARY KEY(level_id, player_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE level_demon_votes ADD CONSTRAINT FK_58A829935FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_demon_votes ADD CONSTRAINT FK_58A8299399E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_star_votes ADD CONSTRAINT FK_79F88D325FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE level_star_votes ADD CONSTRAINT FK_79F88D3299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE level_demon_vote');
        $this->addSql('DROP TABLE level_star_vote');
    }
}
