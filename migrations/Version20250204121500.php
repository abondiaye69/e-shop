<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250204121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout table password_reset_token et champ must_change_password sur user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD must_change_password BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('CREATE TABLE password_reset_token (id SERIAL NOT NULL, user_id INT NOT NULL, token_hash VARCHAR(120) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A0CEB5F0A76ED395 ON password_reset_token (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A0CEB5F0B9E1C759 ON password_reset_token (token_hash)');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_A0CEB5F0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset_token DROP CONSTRAINT FK_A0CEB5F0A76ED395');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('ALTER TABLE "user" DROP must_change_password');
    }
}
