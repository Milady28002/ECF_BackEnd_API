<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225092219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY `FK_8F91ABF0FB88E14F`');
        $this->addSql('DROP INDEX fk_8f91abf0fb88e14f ON avis');
        $this->addSql('CREATE INDEX fk_avis_utilisateur ON avis (utilisateur_id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT `FK_8F91ABF0FB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY `FK_6EEAA67DFB88E14F`');
        $this->addSql('DROP INDEX fk_6eeaa67dfb88e14f ON commande');
        $this->addSql('CREATE INDEX utilisateur_id ON commande (utilisateur_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT `FK_6EEAA67DFB88E14F` FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE plat CHANGE photo photo BLOB DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE utilisateur CHANGE firstname firstname VARCHAR(255) DEFAULT \'NULL\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('DROP INDEX fk_avis_utilisateur ON avis');
        $this->addSql('CREATE INDEX FK_8F91ABF0FB88E14F ON avis (utilisateur_id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
        $this->addSql('DROP INDEX utilisateur_id ON commande');
        $this->addSql('CREATE INDEX FK_6EEAA67DFB88E14F ON commande (utilisateur_id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
        $this->addSql('ALTER TABLE plat CHANGE photo photo BLOB DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE firstname firstname VARCHAR(255) DEFAULT NULL');
    }
}
