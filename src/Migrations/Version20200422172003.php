<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200422172003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD team INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C4E0A61F FOREIGN KEY (team) REFERENCES client__team (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C4E0A61F ON user (team)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84B239936447454A ON client__affiliate (referral_code)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_84B239936447454A ON client__affiliate');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C4E0A61F');
        $this->addSql('DROP INDEX UNIQ_8D93D649C4E0A61F ON user');
        $this->addSql('ALTER TABLE user DROP team');
    }
}
