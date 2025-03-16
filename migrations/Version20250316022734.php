<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250316022734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forms ADD COLUMN enabled BOOLEAN NOT NULL DEFAULT TRUE');
        $this->addSql('ALTER TABLE forms ADD COLUMN redirect_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__forms AS SELECT id, name, description, uid, created_at, updated_at FROM forms');
        $this->addSql('DROP TABLE forms');
        $this->addSql('CREATE TABLE forms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, uid BLOB NOT NULL --(DC2Type:uuid)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO forms (id, name, description, uid, created_at, updated_at) SELECT id, name, description, uid, created_at, updated_at FROM __temp__forms');
        $this->addSql('DROP TABLE __temp__forms');
        $this->addSql('CREATE INDEX IDX_FD3F1BF7539B0606 ON forms (uid)');
    }
}
