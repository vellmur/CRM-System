<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200430093128 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer__apartment (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, UNIQUE INDEX aparment_unique (number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE customer__referrals');
        $this->addSql('DROP TABLE customer__renewal_views');
        $this->addSql('ALTER TABLE client ADD street VARCHAR(255) DEFAULT NULL, DROP weight_format');
        $this->addSql('ALTER TABLE customer__vendor DROP FOREIGN KEY FK_10CC70CBF5B7AF75');
        $this->addSql('DROP INDEX UNIQ_10CC70CBF5B7AF75 ON customer__vendor');
        $this->addSql('ALTER TABLE customer__vendor DROP address_id');
        $this->addSql('ALTER TABLE customer ADD apartment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09176DFE85 FOREIGN KEY (apartment_id) REFERENCES customer__apartment (id)');
        $this->addSql('CREATE INDEX IDX_81398E09176DFE85 ON customer (apartment_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09176DFE85');
        $this->addSql('CREATE TABLE customer__referrals (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, referral_id INT DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX customer_referral_unique (customer_id, referral_id), INDEX IDX_AB286D099395C3F3 (customer_id), UNIQUE INDEX UNIQ_AB286D093CCAA4B7 (referral_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE customer__renewal_views (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, customer_id INT DEFAULT NULL, ip INT UNSIGNED DEFAULT NULL, step INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_88E36BAB9395C3F3 (customer_id), INDEX IDX_88E36BAB19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE customer__referrals ADD CONSTRAINT FK_AB286D093CCAA4B7 FOREIGN KEY (referral_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer__referrals ADD CONSTRAINT FK_AB286D099395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer__renewal_views ADD CONSTRAINT FK_88E36BAB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE customer__renewal_views ADD CONSTRAINT FK_88E36BAB9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('DROP TABLE customer__apartment');
        $this->addSql('ALTER TABLE client ADD weight_format INT NOT NULL, DROP street');
        $this->addSql('DROP INDEX IDX_81398E09176DFE85 ON customer');
        $this->addSql('ALTER TABLE customer DROP apartment_id');
        $this->addSql('ALTER TABLE customer__vendor ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer__vendor ADD CONSTRAINT FK_10CC70CBF5B7AF75 FOREIGN KEY (address_id) REFERENCES customer__address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_10CC70CBF5B7AF75 ON customer__vendor (address_id)');
    }
}
