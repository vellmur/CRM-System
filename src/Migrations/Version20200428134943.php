<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200428134943 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE customer__invoice DROP FOREIGN KEY FK_C8E8B51664D218E');
        $this->addSql('ALTER TABLE customer__location_workdays DROP FOREIGN KEY FK_A2035B2664D218E');
        $this->addSql('ALTER TABLE share__customer DROP FOREIGN KEY FK_440E06E55E9E89CB');
        $this->addSql('ALTER TABLE share__products DROP FOREIGN KEY FK_768DD2B63B1CE6A3');
        $this->addSql('ALTER TABLE customer__invoice_product DROP FOREIGN KEY FK_4CE9878E2AE63FDB');
        $this->addSql('ALTER TABLE customer__orders DROP FOREIGN KEY FK_C1BED32AE63FDB');
        $this->addSql('ALTER TABLE email__feedback DROP FOREIGN KEY FK_F3E29E132AE63FDB');
        $this->addSql('ALTER TABLE share__customer DROP FOREIGN KEY FK_440E06E52AE63FDB');
        $this->addSql('ALTER TABLE share__custom DROP FOREIGN KEY FK_4EDB5B842AE63FDB');
        $this->addSql('ALTER TABLE share__pickups DROP FOREIGN KEY FK_55F08D2B2AE63FDB');
        $this->addSql('ALTER TABLE share__custom DROP FOREIGN KEY FK_4EDB5B84A1615E9C');
        $this->addSql('ALTER TABLE translation__key DROP FOREIGN KEY FK_EAD911C8115F0EE5');
        $this->addSql('ALTER TABLE translation__shared DROP FOREIGN KEY FK_4BB3EACA115F0EE5');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456FD145533');
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456FE559DFD1');
        $this->addSql('ALTER TABLE translation__shared DROP FOREIGN KEY FK_4BB3EACAE559DFD1');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E559DFD1');
        $this->addSql('DROP TABLE customer__location');
        $this->addSql('DROP TABLE customer__location_workdays');
        $this->addSql('DROP TABLE customer__orders');
        $this->addSql('DROP TABLE email__feedback');
        $this->addSql('DROP TABLE email__testimonial_recipient');
        $this->addSql('DROP TABLE share');
        $this->addSql('DROP TABLE share__custom');
        $this->addSql('DROP TABLE share__customer');
        $this->addSql('DROP TABLE share__pickups');
        $this->addSql('DROP TABLE share__products');
        $this->addSql('DROP TABLE share__suspended_weeks');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE translation__domain');
        $this->addSql('DROP TABLE translation__key');
        $this->addSql('DROP TABLE translation__locale');
        $this->addSql('DROP TABLE translation__shared');
        $this->addSql('DROP INDEX IDX_8D93D649E559DFD1 ON user');
        $this->addSql('ALTER TABLE user CHANGE locale_id locale INT NOT NULL');
        $this->addSql('ALTER TABLE pos__products CHANGE pay_by_item pay_by_item TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE customer DROP delivery_day, DROP testimonial');
        $this->addSql('DROP INDEX IDX_C8E8B51664D218E ON customer__invoice');
        $this->addSql('ALTER TABLE customer__invoice DROP location_id');
        $this->addSql('DROP INDEX IDX_4CE9878E2AE63FDB ON customer__invoice_product');
        $this->addSql('ALTER TABLE customer__invoice_product DROP share_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE customer__location (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, apartment INT DEFAULT NULL, city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, postalCode INT DEFAULT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, type INT NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATE NOT NULL, INDEX IDX_DBA334B119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE customer__location_workdays (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, weekday INT NOT NULL, start_time VARCHAR(8) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, duration INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_A2035B2664D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE customer__orders (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, share_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, INDEX IDX_C1BED32AE63FDB (share_id), INDEX IDX_C1BED319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE email__feedback (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, share_id INT DEFAULT NULL, recipient_id INT DEFAULT NULL, is_satisfied TINYINT(1) NOT NULL, share_date DATE NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_F3E29E139395C3F3 (customer_id), UNIQUE INDEX UNIQ_F3E29E13E92F8F78 (recipient_id), INDEX IDX_F3E29E132AE63FDB (share_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE email__testimonial_recipient (id INT AUTO_INCREMENT NOT NULL, affiliate_id INT DEFAULT NULL, email VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, firstname VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_7DDB49AF9F12C49A (affiliate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, price NUMERIC(8, 2) NOT NULL, is_active TINYINT(1) NOT NULL, updated_at DATE DEFAULT NULL, INDEX IDX_EF069D5A19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share__custom (id INT AUTO_INCREMENT NOT NULL, share_product_id INT DEFAULT NULL, product_id INT DEFAULT NULL, share_id INT DEFAULT NULL, INDEX IDX_4EDB5B844584665A (product_id), INDEX IDX_4EDB5B84A1615E9C (share_product_id), INDEX IDX_4EDB5B842AE63FDB (share_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share__customer (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, share_id INT NOT NULL, location INT DEFAULT NULL, type INT NOT NULL, status INT NOT NULL, start_date DATE NOT NULL, pickups_num INT NOT NULL, renewal_date DATE NOT NULL, pickup_day INT DEFAULT NULL, INDEX IDX_440E06E52AE63FDB (share_id), INDEX IDX_440E06E59395C3F3 (customer_id), INDEX IDX_440E06E55E9E89CB (location), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share__pickups (id INT AUTO_INCREMENT NOT NULL, share_id INT DEFAULT NULL, date DATE NOT NULL, skipped TINYINT(1) NOT NULL, is_suspended TINYINT(1) NOT NULL, INDEX IDX_55F08D2B2AE63FDB (share_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share__products (id INT AUTO_INCREMENT NOT NULL, customer_order INT DEFAULT NULL, vendor_order INT DEFAULT NULL, product_id INT DEFAULT NULL, price NUMERIC(7, 2) NOT NULL, weight NUMERIC(7, 2) NOT NULL, qty INT NOT NULL, INDEX IDX_768DD2B6E36F91D8 (vendor_order), INDEX IDX_768DD2B63B1CE6A3 (customer_order), INDEX IDX_768DD2B64584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE share__suspended_weeks (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, week INT NOT NULL, year INT NOT NULL, INDEX IDX_5A20DD0519EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE translation (id INT AUTO_INCREMENT NOT NULL, key_id INT NOT NULL, locale_id INT NOT NULL, translation LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_B469456FE559DFD1 (locale_id), UNIQUE INDEX translation_unique (key_id, locale_id), INDEX IDX_B469456FD145533 (key_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE translation__domain (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX translation_domain_unique (domain), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE translation__key (id INT AUTO_INCREMENT NOT NULL, domain_id INT NOT NULL, trans_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX translation_key_unique (domain_id, trans_key), INDEX IDX_EAD911C8115F0EE5 (domain_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE translation__locale (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX translation_locale_unique (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE translation__shared (id INT AUTO_INCREMENT NOT NULL, locale_id INT NOT NULL, domain_id INT NOT NULL, is_shared TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4BB3EACA115F0EE5 (domain_id), UNIQUE INDEX translation_shared_unique (locale_id, domain_id), INDEX IDX_4BB3EACAE559DFD1 (locale_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE customer__location ADD CONSTRAINT FK_DBA334B119EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE customer__location_workdays ADD CONSTRAINT FK_A2035B2664D218E FOREIGN KEY (location_id) REFERENCES customer__location (id)');
        $this->addSql('ALTER TABLE customer__orders ADD CONSTRAINT FK_C1BED319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__orders ADD CONSTRAINT FK_C1BED32AE63FDB FOREIGN KEY (share_id) REFERENCES share (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email__feedback ADD CONSTRAINT FK_F3E29E132AE63FDB FOREIGN KEY (share_id) REFERENCES share (id)');
        $this->addSql('ALTER TABLE email__feedback ADD CONSTRAINT FK_F3E29E139395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE email__feedback ADD CONSTRAINT FK_F3E29E13E92F8F78 FOREIGN KEY (recipient_id) REFERENCES email__recipient (id)');
        $this->addSql('ALTER TABLE email__testimonial_recipient ADD CONSTRAINT FK_7DDB49AF9F12C49A FOREIGN KEY (affiliate_id) REFERENCES customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE share__custom ADD CONSTRAINT FK_4EDB5B842AE63FDB FOREIGN KEY (share_id) REFERENCES share__customer (id)');
        $this->addSql('ALTER TABLE share__custom ADD CONSTRAINT FK_4EDB5B844584665A FOREIGN KEY (product_id) REFERENCES pos__products (id)');
        $this->addSql('ALTER TABLE share__custom ADD CONSTRAINT FK_4EDB5B84A1615E9C FOREIGN KEY (share_product_id) REFERENCES share__products (id)');
        $this->addSql('ALTER TABLE share__customer ADD CONSTRAINT FK_440E06E52AE63FDB FOREIGN KEY (share_id) REFERENCES share (id)');
        $this->addSql('ALTER TABLE share__customer ADD CONSTRAINT FK_440E06E55E9E89CB FOREIGN KEY (location) REFERENCES customer__location (id)');
        $this->addSql('ALTER TABLE share__customer ADD CONSTRAINT FK_440E06E59395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE share__pickups ADD CONSTRAINT FK_55F08D2B2AE63FDB FOREIGN KEY (share_id) REFERENCES share__customer (id)');
        $this->addSql('ALTER TABLE share__products ADD CONSTRAINT FK_768DD2B63B1CE6A3 FOREIGN KEY (customer_order) REFERENCES customer__orders (id)');
        $this->addSql('ALTER TABLE share__products ADD CONSTRAINT FK_768DD2B64584665A FOREIGN KEY (product_id) REFERENCES pos__products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share__products ADD CONSTRAINT FK_768DD2B6E36F91D8 FOREIGN KEY (vendor_order) REFERENCES customer__vendor_orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share__suspended_weeks ADD CONSTRAINT FK_5A20DD0519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456FD145533 FOREIGN KEY (key_id) REFERENCES translation__key (id)');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456FE559DFD1 FOREIGN KEY (locale_id) REFERENCES translation__locale (id)');
        $this->addSql('ALTER TABLE translation__key ADD CONSTRAINT FK_EAD911C8115F0EE5 FOREIGN KEY (domain_id) REFERENCES translation__domain (id)');
        $this->addSql('ALTER TABLE translation__shared ADD CONSTRAINT FK_4BB3EACA115F0EE5 FOREIGN KEY (domain_id) REFERENCES translation__domain (id)');
        $this->addSql('ALTER TABLE translation__shared ADD CONSTRAINT FK_4BB3EACAE559DFD1 FOREIGN KEY (locale_id) REFERENCES translation__locale (id)');
        $this->addSql('ALTER TABLE customer ADD delivery_day INT DEFAULT NULL, ADD testimonial TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE customer__invoice ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer__invoice ADD CONSTRAINT FK_C8E8B51664D218E FOREIGN KEY (location_id) REFERENCES customer__location (id)');
        $this->addSql('CREATE INDEX IDX_C8E8B51664D218E ON customer__invoice (location_id)');
        $this->addSql('ALTER TABLE customer__invoice_product ADD share_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer__invoice_product ADD CONSTRAINT FK_4CE9878E2AE63FDB FOREIGN KEY (share_id) REFERENCES share (id)');
        $this->addSql('CREATE INDEX IDX_4CE9878E2AE63FDB ON customer__invoice_product (share_id)');
        $this->addSql('ALTER TABLE pos__products CHANGE pay_by_item pay_by_item TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE locale locale_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E559DFD1 FOREIGN KEY (locale_id) REFERENCES translation__locale (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649E559DFD1 ON user (locale_id)');
    }
}
