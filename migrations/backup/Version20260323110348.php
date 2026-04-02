<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323110348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_statut_historique (id INT AUTO_INCREMENT NOT NULL, ancien_statut VARCHAR(50) NOT NULL, nouveau_statut VARCHAR(50) NOT NULL, date_changement DATETIME NOT NULL, commande_numero VARCHAR(50) NOT NULL, utilisateur_id INT DEFAULT NULL, INDEX IDX_634BFC75F70E1A4B (commande_numero), INDEX IDX_634BFC75FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande_statut_historique ADD CONSTRAINT FK_634BFC75F70E1A4B FOREIGN KEY (commande_numero) REFERENCES commande (numero_commande)');
        $this->addSql('ALTER TABLE commande_statut_historique ADD CONSTRAINT FK_634BFC75FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_statut_historique DROP FOREIGN KEY FK_634BFC75F70E1A4B');
        $this->addSql('ALTER TABLE commande_statut_historique DROP FOREIGN KEY FK_634BFC75FB88E14F');
        $this->addSql('DROP TABLE commande_statut_historique');
    }
}
