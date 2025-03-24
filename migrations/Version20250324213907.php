<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324213907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');

        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');

        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON users (email)');

        $this->addSql('CREATE TABLE account_settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, smtp_host VARCHAR(255) DEFAULT NULL, smtp_port INTEGER DEFAULT NULL, smtp_user VARCHAR(255) DEFAULT NULL, smtp_password VARCHAR(255) DEFAULT NULL, email_from_name VARCHAR(255) DEFAULT NULL, email_from_address VARCHAR(255) DEFAULT NULL, mailer_encryption VARCHAR(16) DEFAULT NULL, CONSTRAINT FK_9D8B42737E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D8B42737E3C61F9 ON account_settings (owner_id)');

        $this->addSql('CREATE TABLE forms (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, uid BLOB NOT NULL --(DC2Type:uuid)
        , enabled BOOLEAN NOT NULL, redirect_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_FD3F1BF77E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FD3F1BF77E3C61F9 ON forms (owner_id)');
        $this->addSql('CREATE INDEX IDX_FD3F1BF7539B0606 ON forms (uid)');

        $this->addSql('CREATE TABLE form_fields (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, form_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, required BOOLEAN NOT NULL, position INTEGER NOT NULL, CONSTRAINT FK_7C0B37265FF69B7D FOREIGN KEY (form_id) REFERENCES forms (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7C0B37265FF69B7D ON form_fields (form_id)');

        $this->addSql('CREATE TABLE form_notification_settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, form_id INTEGER NOT NULL, enabled BOOLEAN NOT NULL, type VARCHAR(32) NOT NULL, target VARCHAR(255) DEFAULT NULL, options CLOB NOT NULL --(DC2Type:json)
        , CONSTRAINT FK_9EF488325FF69B7D FOREIGN KEY (form_id) REFERENCES forms (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9EF488325FF69B7D ON form_notification_settings (form_id)');

        $this->addSql('CREATE TABLE form_submissions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, form_id INTEGER NOT NULL, payload CLOB NOT NULL --(DC2Type:json)
        , submitted_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, CONSTRAINT FK_C80AF9E65FF69B7D FOREIGN KEY (form_id) REFERENCES forms (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C80AF9E65FF69B7D ON form_submissions (form_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE form_submissions');
        $this->addSql('DROP TABLE form_notification_settings');
        $this->addSql('DROP TABLE form_fields');
        $this->addSql('DROP TABLE forms');

        $this->addSql('DROP TABLE account_settings');
        $this->addSql('DROP TABLE users');

        $this->addSql('DROP TABLE messenger_messages');
    }
}
