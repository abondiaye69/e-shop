<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250204124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables order et order_item';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, user_id INT DEFAULT NULL, reference VARCHAR(40) NOT NULL, status VARCHAR(20) NOT NULL, email VARCHAR(180) NOT NULL, customer_name VARCHAR(150) DEFAULT NULL, address VARCHAR(180) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, city VARCHAR(80) DEFAULT NULL, note TEXT DEFAULT NULL, subtotal NUMERIC(10, 2) NOT NULL, shipping NUMERIC(10, 2) NOT NULL, total NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993984ECAA77 ON "order" (reference)');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON "order" (user_id)');
        $this->addSql('CREATE TABLE order_item (id SERIAL NOT NULL, order_ref_id INT NOT NULL, name VARCHAR(180) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, quantity INT NOT NULL, line_total NUMERIC(10, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52EA1F0946F3D5EA ON order_item (order_ref_id)');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F0946F3D5EA FOREIGN KEY (order_ref_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F0946F3D5EA');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398A76ED395');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_item');
    }
}
