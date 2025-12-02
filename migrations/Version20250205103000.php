<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250205103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table product (catalogue avec prix, promo, image)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product (id SERIAL NOT NULL, name VARCHAR(180) NOT NULL, price NUMERIC(10, 2) NOT NULL, promo_percent DOUBLE PRECISION DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, badge VARCHAR(60) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product');
    }
}
