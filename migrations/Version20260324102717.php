<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324102717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du lien avis -> commande, date de création, et ajustement des champs note et description';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis ADD date_creation DATETIME NOT NULL, ADD commande_numero VARCHAR(50) NOT NULL, CHANGE note note INT NOT NULL, CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0F70E1A4B FOREIGN KEY (commande_numero) REFERENCES commande (numero_commande)');
        $this->addSql('CREATE INDEX fk_avis_commande ON avis (commande_numero)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0F70E1A4B');
        $this->addSql('DROP INDEX fk_avis_commande ON avis');
        $this->addSql('ALTER TABLE avis DROP date_creation, DROP commande_numero, CHANGE note note VARCHAR(50) NOT NULL, CHANGE description description VARCHAR(50) NOT NULL');
    }
}