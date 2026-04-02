<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306131348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu ADD regime_id INT NOT NULL, ADD theme_id INT NOT NULL, DROP regime');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9335E7D534 FOREIGN KEY (regime_id) REFERENCES regime (regime_id)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A9359027487 FOREIGN KEY (theme_id) REFERENCES theme (theme_id)');
        $this->addSql('CREATE INDEX IDX_7D053A9335E7D534 ON menu (regime_id)');
        $this->addSql('CREATE INDEX IDX_7D053A9359027487 ON menu (theme_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9335E7D534');
        $this->addSql('ALTER TABLE menu DROP FOREIGN KEY FK_7D053A9359027487');
        $this->addSql('DROP INDEX IDX_7D053A9335E7D534 ON menu');
        $this->addSql('DROP INDEX IDX_7D053A9359027487 ON menu');
        $this->addSql('ALTER TABLE menu ADD regime VARCHAR(50) NOT NULL, DROP regime_id, DROP theme_id');
    }
}
