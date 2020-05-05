<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200505171034 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC419EB6921');
        $this->addSql('DROP INDEX IDX_8A5C4BC419EB6921 ON master__email_recipient');
        $this->addSql('DROP INDEX master_email_recipient ON master__email_recipient');
        $this->addSql('ALTER TABLE master__email_recipient CHANGE client_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8A5C4BC4A76ED395 ON master__email_recipient (user_id)');
        $this->addSql('CREATE UNIQUE INDEX master_email_recipient ON master__email_recipient (email_id, user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC4A76ED395');
        $this->addSql('DROP INDEX IDX_8A5C4BC4A76ED395 ON master__email_recipient');
        $this->addSql('DROP INDEX master_email_recipient ON master__email_recipient');
        $this->addSql('ALTER TABLE master__email_recipient CHANGE user_id client_id INT NOT NULL');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_8A5C4BC419EB6921 ON master__email_recipient (client_id)');
        $this->addSql('CREATE UNIQUE INDEX master_email_recipient ON master__email_recipient (email_id, client_id)');
    }
}
