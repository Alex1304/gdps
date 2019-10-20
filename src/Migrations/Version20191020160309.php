<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191020160309 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE quest (id INT AUTO_INCREMENT NOT NULL, currency INT NOT NULL, amount INT NOT NULL, diamond_reward INT NOT NULL, name VARCHAR(255) NOT NULL, tier INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
		$this->addSql("INSERT INTO quest VALUES (NULL, '1', '200', '5', 'Orb Finder', '1'), (NULL, '2', '2', '5', 'Coin Finder', '1'), (NULL, '3', '5', '5', 'Star Finder', '1'), (NULL, '2', '4', '10', 'Coin Collector', '2'), (NULL, '3', '10', '10', 'Star Collector', '2'), (NULL, '1', '500', '10', 'Orb Collector', '2'), (NULL, '3', '15', '15', 'Star Master', '3'), (NULL, '1', '1000', '15', 'Orb Master', '3'), (NULL, '2', '6', '15', 'Coin Master', '3')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE quest');
    }
}
