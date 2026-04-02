<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325103236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image_galerie (image_id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, categorie VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (image_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avis CHANGE utilisateur_id utilisateur_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE image_galerie');
        $this->addSql('ALTER TABLE avis CHANGE utilisateur_id utilisateur_id INT DEFAULT NULL');
    }
}
