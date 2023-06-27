<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627203730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE gallery_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE news_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE photo_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE gallery (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE news (id INT NOT NULL, name VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, img VARCHAR(255) NOT NULL, tekst TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE photo (id INT NOT NULL, gallery_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, img VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_14B784184E7AF8F ON photo (gallery_id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE gallery_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE news_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE photo_id_seq CASCADE');
        $this->addSql('ALTER TABLE photo DROP CONSTRAINT FK_14B784184E7AF8F');
        $this->addSql('DROP TABLE gallery');
        $this->addSql('DROP TABLE news');
        $this->addSql('DROP TABLE photo');
    }
}
