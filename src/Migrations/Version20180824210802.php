<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180824210802 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, udid VARCHAR(255) NOT NULL, uuid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, stars INT NOT NULL, demons INT NOT NULL, diamonds INT NOT NULL, icon INT NOT NULL, color1 INT NOT NULL, color2 INT NOT NULL, icon_type INT NOT NULL, coins INT NOT NULL, user_coins INT NOT NULL, special TINYINT(1) NOT NULL, acc_icon INT NOT NULL, acc_ship INT NOT NULL, acc_ball INT NOT NULL, acc_ufo INT NOT NULL, acc_wave INT NOT NULL, acc_robot INT NOT NULL, acc_glow INT NOT NULL, acc_spider INT NOT NULL, acc_explosion INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, youtube VARCHAR(255) NOT NULL, twitter VARCHAR(255) NOT NULL, twitch VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_7D3656A499E6F5DF (player_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A499E6F5DF');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE account');
    }
}
