<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191026122420 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_comment ADD likes BIGINT NOT NULL');
        $this->addSql('ALTER TABLE level ADD downloads BIGINT NOT NULL, ADD likes BIGINT NOT NULL');
        $this->addSql('ALTER TABLE level_comment ADD likes BIGINT NOT NULL');
		$this->addSql('UPDATE level l SET l.downloads = (SELECT COUNT(*) FROM level_downloads ld WHERE ld.level_id = l.id), l.likes = (SELECT COUNT(*) FROM level_likes ll WHERE ll.level_id = l.id) - (SELECT COUNT(*) FROM level_dislikes ld WHERE ld.level_id = l.id)');
		$this->addSql('UPDATE level_comment c SET c.likes = (SELECT COUNT(*) FROM level_comment_likes ll WHERE ll.level_comment_id = c.id) - (SELECT COUNT(*) FROM level_comment_dislikes ld WHERE ld.level_comment_id = c.id)');
		$this->addSql('UPDATE account_comment c SET c.likes = (SELECT COUNT(*) FROM account_comment_likes al WHERE al.account_comment_id = c.id) - (SELECT COUNT(*) FROM account_comment_dislikes ad WHERE ad.account_comment_id = c.id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE account_comment DROP likes');
        $this->addSql('ALTER TABLE level DROP downloads, DROP likes');
        $this->addSql('ALTER TABLE level_comment DROP likes');
    }
}
