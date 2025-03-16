<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250316005021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE form_submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, form_id INTEGER NOT NULL, payload CLOB NOT NULL --(DC2Type:json)
        , submitted_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_C80AF9E65FF69B7D FOREIGN KEY (form_id) REFERENCES forms (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C80AF9E65FF69B7D ON form_submissions (form_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__forms AS SELECT id, name, description, uid, created_at, updated_at FROM forms');
        $this->addSql('DROP TABLE forms');
        $this->addSql('CREATE TABLE forms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, uid BLOB NOT NULL --(DC2Type:uuid)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO forms (id, name, description, uid, created_at, updated_at) SELECT id, name, description, uid, created_at, updated_at FROM __temp__forms');
        $this->addSql('DROP TABLE __temp__forms');
        $this->addSql('CREATE INDEX IDX_FD3F1BF7539B0606 ON forms (uid)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE form_submissions');
        $this->addSql('CREATE TEMPORARY TABLE __temp__forms AS SELECT id, name, description, uid, created_at, updated_at FROM forms');
        $this->addSql('DROP TABLE forms');
        $this->addSql('CREATE TABLE forms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, uid BLOB NOT NULL --(DC2Type:uuid)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO forms (id, name, description, uid, created_at, updated_at) SELECT id, name, description, uid, created_at, updated_at FROM __temp__forms');
        $this->addSql('DROP TABLE __temp__forms');
    }
}
