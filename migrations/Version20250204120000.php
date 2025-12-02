<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250204120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table user pour l’authentification/admin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(80) DEFAULT NULL, last_name VARCHAR(80) DEFAULT NULL, address VARCHAR(180) DEFAULT NULL, city VARCHAR(80) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, country VARCHAR(80) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE "user"');
    }
}
