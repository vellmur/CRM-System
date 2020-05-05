<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200505150735 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE device__promotion_visit (id INT AUTO_INCREMENT NOT NULL, page_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_80A5FFF2C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, locale INT NOT NULL, date_format INT DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATE DEFAULT NULL, enabled TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D64919EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device__page_views (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, module_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, page VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_66BAC7EF94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__device (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ip VARCHAR(15) DEFAULT NULL, is_computer TINYINT(1) NOT NULL, os VARCHAR(30) NOT NULL, browser VARCHAR(30) NOT NULL, browser_version VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A8114F07A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__posts (id INT AUTO_INCREMENT NOT NULL, thumb_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F385F1482B36786B (title), INDEX IDX_F385F148C7034EA5 (thumb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email (id INT AUTO_INCREMENT NOT NULL, automated_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, is_draft TINYINT(1) NOT NULL, in_process TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9C4A37C6B1254A89 (automated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email_recipient (id INT AUTO_INCREMENT NOT NULL, email_id INT NOT NULL, client_id INT NOT NULL, email_address VARCHAR(50) NOT NULL, is_sent TINYINT(1) NOT NULL, is_delivered TINYINT(1) NOT NULL, is_opened TINYINT(1) NOT NULL, is_clicked TINYINT(1) NOT NULL, is_bounced TINYINT(1) NOT NULL, INDEX IDX_8A5C4BC4A832C1C9 (email_id), INDEX IDX_8A5C4BC419EB6921 (client_id), UNIQUE INDEX master_email_recipient (email_id, client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email_automated (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, type INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__subscription (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, amount NUMERIC(8, 2) NOT NULL, INDEX IDX_8E2B3C4E19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__posts (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, thumb_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_57C31DD52B36786B (title), INDEX IDX_57C31DD519EB6921 (client_id), INDEX IDX_57C31DD5C7034EA5 (thumb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__referral (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, affiliate_id INT NOT NULL, is_paid TINYINT(1) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_B6E7086F19EB6921 (client_id), INDEX IDX_B6E7086F9F12C49A (affiliate_id), UNIQUE INDEX referral_unique (client_id, affiliate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification__notify (id INT AUTO_INCREMENT NOT NULL, notification_id INT DEFAULT NULL, user_id INT DEFAULT NULL, seen TINYINT(1) NOT NULL, INDEX IDX_CD5680FCEF1A9D84 (notification_id), INDEX IDX_CD5680FCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(4000) NOT NULL, message VARCHAR(4000) DEFAULT NULL, link VARCHAR(4000) DEFAULT NULL, module_id INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__address (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(2) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__module_access (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, module_id INT NOT NULL, expired_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, status INT NOT NULL, INDEX IDX_22F969DA19EB6921 (client_id), UNIQUE INDEX access_unique (client_id, module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__affiliate (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(25) DEFAULT NULL, referral_code VARCHAR(20) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_84B239936447454A (referral_code), UNIQUE INDEX UNIQ_84B2399319EB6921 (client_id), UNIQUE INDEX affiliate_unique (name, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(50) NOT NULL, currency INT NOT NULL, timezone VARCHAR(30) DEFAULT NULL, token VARCHAR(30) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_C74404555E237E06 (name), UNIQUE INDEX UNIQ_C7440455F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__vendor_orders (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, vendor_id INT DEFAULT NULL, order_date DATE NOT NULL, INDEX IDX_A5D88E9819EB6921 (client_id), INDEX IDX_A5D88E98F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, gardener_price INT NOT NULL, farmer_price INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__transactions (id INT AUTO_INCREMENT NOT NULL, payment_method INT NOT NULL, payment_id INT DEFAULT NULL, transaction_status INT NOT NULL, payment_code VARCHAR(255) NOT NULL, wallet VARCHAR(255) NOT NULL, invoice VARCHAR(255) NOT NULL, amount BIGINT NOT NULL, created_at DATETIME NOT NULL, confirmed_at DATETIME DEFAULT NULL, INDEX IDX_D49608E67B61A1F6 (payment_method), UNIQUE INDEX UNIQ_D49608E64C3A3BB (payment_id), INDEX IDX_D49608E6D7D175C (transaction_status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pos__product (id INT AUTO_INCREMENT NOT NULL, pos_id INT NOT NULL, product_id INT DEFAULT NULL, price NUMERIC(7, 2) NOT NULL, weight NUMERIC(7, 2) DEFAULT NULL, qty INT DEFAULT NULL, INDEX IDX_EE06AFE041085FAE (pos_id), INDEX IDX_EE06AFE04584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__notifies (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, notify_type INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_D4DC51699395C3F3 (customer_id), UNIQUE INDEX customer_emails_unique (customer_id, notify_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pos__products (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, image INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, price NUMERIC(7, 2) NOT NULL, delivery_price NUMERIC(7, 2) DEFAULT NULL, category INT DEFAULT NULL, weight NUMERIC(7, 2) DEFAULT NULL, sku VARCHAR(16) DEFAULT NULL, pay_by_item TINYINT(1) DEFAULT \'1\' NOT NULL, is_pos TINYINT(1) NOT NULL, INDEX IDX_BBEA2BDC19EB6921 (client_id), INDEX IDX_BBEA2BDCC53D045F (image), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__apartment (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, number INT NOT NULL, UNIQUE INDEX UNIQ_FD2BCDCB96901F54 (number), INDEX IDX_FD2BCDCB4D2A7E12 (building_id), UNIQUE INDEX aparment_unique (building_id, number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__vendor (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) NOT NULL, category LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', order_day LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', is_active TINYINT(1) NOT NULL, INDEX IDX_10CC70CB19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client__tags (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, name VARCHAR(191) NOT NULL, INDEX IDX_91F4788A19EB6921 (client_id), UNIQUE INDEX tags_unique (client_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__vendor_contact (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, token VARCHAR(50) NOT NULL, notify_enabled TINYINT(1) NOT NULL, INDEX IDX_F544C542F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__payments (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, transaction_id INT DEFAULT NULL, amount NUMERIC(8, 2) NOT NULL, shares LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_E0EF26489395C3F3 (customer_id), UNIQUE INDEX UNIQ_E0EF26482FC0CB0F (transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, apartment_id INT NOT NULL, firstname VARCHAR(25) NOT NULL, lastname VARCHAR(25) NOT NULL, email VARCHAR(50) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at DATE NOT NULL, token VARCHAR(50) NOT NULL, is_activated TINYINT(1) NOT NULL, INDEX IDX_81398E0919EB6921 (client_id), INDEX IDX_81398E09176DFE85 (apartment_id), UNIQUE INDEX customer_unique (client_id, email), UNIQUE INDEX customer_phone_unique (client_id, phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__transaction_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__product__tag (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, client_id INT DEFAULT NULL, INDEX IDX_FD349E724584665A (product_id), INDEX IDX_FD349E7219EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__invoice (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, amount NUMERIC(8, 2) NOT NULL, ref_num VARCHAR(255) DEFAULT NULL, order_date DATE NOT NULL, is_paid TINYINT(1) NOT NULL, is_sent TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_C8E8B5169395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__invoice_product (id INT AUTO_INCREMENT NOT NULL, invoice_id INT DEFAULT NULL, product_id INT DEFAULT NULL, qty INT DEFAULT NULL, weight NUMERIC(7, 2) DEFAULT NULL, total_amount NUMERIC(8, 2) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4CE9878E2989F1FD (invoice_id), INDEX IDX_4CE9878E4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pos (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, total NUMERIC(7, 2) NOT NULL, received_amount NUMERIC(7, 2) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_80D9E6AC19EB6921 (client_id), INDEX IDX_80D9E6AC9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__recipient (id INT AUTO_INCREMENT NOT NULL, log_id INT NOT NULL, customer_id INT DEFAULT NULL, email_address VARCHAR(50) NOT NULL, is_sent TINYINT(1) NOT NULL, is_delivered TINYINT(1) NOT NULL, is_opened TINYINT(1) NOT NULL, is_clicked TINYINT(1) NOT NULL, is_bounced TINYINT(1) NOT NULL, INDEX IDX_892BA88BEA675D86 (log_id), INDEX IDX_892BA88B9395C3F3 (customer_id), UNIQUE INDEX customer_email_recipient (log_id, customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__log (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, automated_id INT DEFAULT NULL, reply_email VARCHAR(50) NOT NULL, reply_name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, is_draft TINYINT(1) NOT NULL, in_process TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5D6251BA19EB6921 (client_id), INDEX IDX_5D6251BAB1254A89 (automated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__auto (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, type INT NOT NULL, INDEX IDX_A6D2146E19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__image (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F37C721D19EB6921 (client_id), UNIQUE INDEX client_image_unique (client_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE device__promotion_visit ADD CONSTRAINT FK_80A5FFF2C4663E4 FOREIGN KEY (page_id) REFERENCES device__page_views (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE device__page_views ADD CONSTRAINT FK_66BAC7EF94A4C7D4 FOREIGN KEY (device_id) REFERENCES user__device (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user__device ADD CONSTRAINT FK_A8114F07A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE master__posts ADD CONSTRAINT FK_F385F148C7034EA5 FOREIGN KEY (thumb_id) REFERENCES media__image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE master__email ADD CONSTRAINT FK_9C4A37C6B1254A89 FOREIGN KEY (automated_id) REFERENCES master__email_automated (id)');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC4A832C1C9 FOREIGN KEY (email_id) REFERENCES master__email (id)');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__subscription ADD CONSTRAINT FK_8E2B3C4E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__posts ADD CONSTRAINT FK_57C31DD519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client__posts ADD CONSTRAINT FK_57C31DD5C7034EA5 FOREIGN KEY (thumb_id) REFERENCES media__image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE client__referral ADD CONSTRAINT FK_B6E7086F19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__referral ADD CONSTRAINT FK_B6E7086F9F12C49A FOREIGN KEY (affiliate_id) REFERENCES client__affiliate (id)');
        $this->addSql('ALTER TABLE notification__notify ADD CONSTRAINT FK_CD5680FCEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE notification__notify ADD CONSTRAINT FK_CD5680FCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE client__module_access ADD CONSTRAINT FK_22F969DA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__affiliate ADD CONSTRAINT FK_84B2399319EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455F5B7AF75 FOREIGN KEY (address_id) REFERENCES client__address (id)');
        $this->addSql('ALTER TABLE customer__vendor_orders ADD CONSTRAINT FK_A5D88E9819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__vendor_orders ADD CONSTRAINT FK_A5D88E98F603EE73 FOREIGN KEY (vendor_id) REFERENCES customer__vendor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__transactions ADD CONSTRAINT FK_D49608E67B61A1F6 FOREIGN KEY (payment_method) REFERENCES customer__payment_method (id)');
        $this->addSql('ALTER TABLE customer__transactions ADD CONSTRAINT FK_D49608E64C3A3BB FOREIGN KEY (payment_id) REFERENCES customer__payments (id)');
        $this->addSql('ALTER TABLE customer__transactions ADD CONSTRAINT FK_D49608E6D7D175C FOREIGN KEY (transaction_status) REFERENCES customer__transaction_status (id)');
        $this->addSql('ALTER TABLE pos__product ADD CONSTRAINT FK_EE06AFE041085FAE FOREIGN KEY (pos_id) REFERENCES pos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pos__product ADD CONSTRAINT FK_EE06AFE04584665A FOREIGN KEY (product_id) REFERENCES pos__products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__notifies ADD CONSTRAINT FK_D4DC51699395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE pos__products ADD CONSTRAINT FK_BBEA2BDC19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pos__products ADD CONSTRAINT FK_BBEA2BDCC53D045F FOREIGN KEY (image) REFERENCES media__image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE customer__apartment ADD CONSTRAINT FK_FD2BCDCB4D2A7E12 FOREIGN KEY (building_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE customer__vendor ADD CONSTRAINT FK_10CC70CB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__tags ADD CONSTRAINT FK_91F4788A19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__vendor_contact ADD CONSTRAINT FK_F544C542F603EE73 FOREIGN KEY (vendor_id) REFERENCES customer__vendor (id)');
        $this->addSql('ALTER TABLE customer__payments ADD CONSTRAINT FK_E0EF26489395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer__payments ADD CONSTRAINT FK_E0EF26482FC0CB0F FOREIGN KEY (transaction_id) REFERENCES customer__transactions (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E0919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09176DFE85 FOREIGN KEY (apartment_id) REFERENCES customer__apartment (id)');
        $this->addSql('ALTER TABLE customer__product__tag ADD CONSTRAINT FK_FD349E724584665A FOREIGN KEY (product_id) REFERENCES pos__products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__product__tag ADD CONSTRAINT FK_FD349E7219EB6921 FOREIGN KEY (client_id) REFERENCES client__tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE customer__invoice ADD CONSTRAINT FK_C8E8B5169395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer__invoice_product ADD CONSTRAINT FK_4CE9878E2989F1FD FOREIGN KEY (invoice_id) REFERENCES customer__invoice (id)');
        $this->addSql('ALTER TABLE customer__invoice_product ADD CONSTRAINT FK_4CE9878E4584665A FOREIGN KEY (product_id) REFERENCES pos__products (id)');
        $this->addSql('ALTER TABLE pos ADD CONSTRAINT FK_80D9E6AC19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pos ADD CONSTRAINT FK_80D9E6AC9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email__recipient ADD CONSTRAINT FK_892BA88BEA675D86 FOREIGN KEY (log_id) REFERENCES email__log (id)');
        $this->addSql('ALTER TABLE email__recipient ADD CONSTRAINT FK_892BA88B9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE email__log ADD CONSTRAINT FK_5D6251BA19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE email__log ADD CONSTRAINT FK_5D6251BAB1254A89 FOREIGN KEY (automated_id) REFERENCES email__auto (id)');
        $this->addSql('ALTER TABLE email__auto ADD CONSTRAINT FK_A6D2146E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE media__image ADD CONSTRAINT FK_F37C721D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user__device DROP FOREIGN KEY FK_A8114F07A76ED395');
        $this->addSql('ALTER TABLE notification__notify DROP FOREIGN KEY FK_CD5680FCA76ED395');
        $this->addSql('ALTER TABLE device__promotion_visit DROP FOREIGN KEY FK_80A5FFF2C4663E4');
        $this->addSql('ALTER TABLE device__page_views DROP FOREIGN KEY FK_66BAC7EF94A4C7D4');
        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC4A832C1C9');
        $this->addSql('ALTER TABLE master__email DROP FOREIGN KEY FK_9C4A37C6B1254A89');
        $this->addSql('ALTER TABLE notification__notify DROP FOREIGN KEY FK_CD5680FCEF1A9D84');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455F5B7AF75');
        $this->addSql('ALTER TABLE client__referral DROP FOREIGN KEY FK_B6E7086F9F12C49A');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921');
        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC419EB6921');
        $this->addSql('ALTER TABLE client__subscription DROP FOREIGN KEY FK_8E2B3C4E19EB6921');
        $this->addSql('ALTER TABLE client__posts DROP FOREIGN KEY FK_57C31DD519EB6921');
        $this->addSql('ALTER TABLE client__referral DROP FOREIGN KEY FK_B6E7086F19EB6921');
        $this->addSql('ALTER TABLE client__module_access DROP FOREIGN KEY FK_22F969DA19EB6921');
        $this->addSql('ALTER TABLE client__affiliate DROP FOREIGN KEY FK_84B2399319EB6921');
        $this->addSql('ALTER TABLE customer__vendor_orders DROP FOREIGN KEY FK_A5D88E9819EB6921');
        $this->addSql('ALTER TABLE pos__products DROP FOREIGN KEY FK_BBEA2BDC19EB6921');
        $this->addSql('ALTER TABLE customer__apartment DROP FOREIGN KEY FK_FD2BCDCB4D2A7E12');
        $this->addSql('ALTER TABLE customer__vendor DROP FOREIGN KEY FK_10CC70CB19EB6921');
        $this->addSql('ALTER TABLE client__tags DROP FOREIGN KEY FK_91F4788A19EB6921');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E0919EB6921');
        $this->addSql('ALTER TABLE pos DROP FOREIGN KEY FK_80D9E6AC19EB6921');
        $this->addSql('ALTER TABLE email__log DROP FOREIGN KEY FK_5D6251BA19EB6921');
        $this->addSql('ALTER TABLE email__auto DROP FOREIGN KEY FK_A6D2146E19EB6921');
        $this->addSql('ALTER TABLE media__image DROP FOREIGN KEY FK_F37C721D19EB6921');
        $this->addSql('ALTER TABLE customer__transactions DROP FOREIGN KEY FK_D49608E67B61A1F6');
        $this->addSql('ALTER TABLE customer__payments DROP FOREIGN KEY FK_E0EF26482FC0CB0F');
        $this->addSql('ALTER TABLE pos__product DROP FOREIGN KEY FK_EE06AFE04584665A');
        $this->addSql('ALTER TABLE customer__product__tag DROP FOREIGN KEY FK_FD349E724584665A');
        $this->addSql('ALTER TABLE customer__invoice_product DROP FOREIGN KEY FK_4CE9878E4584665A');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09176DFE85');
        $this->addSql('ALTER TABLE customer__vendor_orders DROP FOREIGN KEY FK_A5D88E98F603EE73');
        $this->addSql('ALTER TABLE customer__vendor_contact DROP FOREIGN KEY FK_F544C542F603EE73');
        $this->addSql('ALTER TABLE customer__product__tag DROP FOREIGN KEY FK_FD349E7219EB6921');
        $this->addSql('ALTER TABLE customer__transactions DROP FOREIGN KEY FK_D49608E64C3A3BB');
        $this->addSql('ALTER TABLE customer__notifies DROP FOREIGN KEY FK_D4DC51699395C3F3');
        $this->addSql('ALTER TABLE customer__payments DROP FOREIGN KEY FK_E0EF26489395C3F3');
        $this->addSql('ALTER TABLE customer__invoice DROP FOREIGN KEY FK_C8E8B5169395C3F3');
        $this->addSql('ALTER TABLE pos DROP FOREIGN KEY FK_80D9E6AC9395C3F3');
        $this->addSql('ALTER TABLE email__recipient DROP FOREIGN KEY FK_892BA88B9395C3F3');
        $this->addSql('ALTER TABLE customer__transactions DROP FOREIGN KEY FK_D49608E6D7D175C');
        $this->addSql('ALTER TABLE customer__invoice_product DROP FOREIGN KEY FK_4CE9878E2989F1FD');
        $this->addSql('ALTER TABLE pos__product DROP FOREIGN KEY FK_EE06AFE041085FAE');
        $this->addSql('ALTER TABLE email__recipient DROP FOREIGN KEY FK_892BA88BEA675D86');
        $this->addSql('ALTER TABLE email__log DROP FOREIGN KEY FK_5D6251BAB1254A89');
        $this->addSql('ALTER TABLE master__posts DROP FOREIGN KEY FK_F385F148C7034EA5');
        $this->addSql('ALTER TABLE client__posts DROP FOREIGN KEY FK_57C31DD5C7034EA5');
        $this->addSql('ALTER TABLE pos__products DROP FOREIGN KEY FK_BBEA2BDCC53D045F');
        $this->addSql('DROP TABLE device__promotion_visit');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE device__page_views');
        $this->addSql('DROP TABLE user__device');
        $this->addSql('DROP TABLE master__posts');
        $this->addSql('DROP TABLE master__email');
        $this->addSql('DROP TABLE master__email_recipient');
        $this->addSql('DROP TABLE master__email_automated');
        $this->addSql('DROP TABLE client__subscription');
        $this->addSql('DROP TABLE client__posts');
        $this->addSql('DROP TABLE client__referral');
        $this->addSql('DROP TABLE notification__notify');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE client__address');
        $this->addSql('DROP TABLE client__module_access');
        $this->addSql('DROP TABLE client__affiliate');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE customer__vendor_orders');
        $this->addSql('DROP TABLE customer__payment_method');
        $this->addSql('DROP TABLE customer__transactions');
        $this->addSql('DROP TABLE pos__product');
        $this->addSql('DROP TABLE customer__notifies');
        $this->addSql('DROP TABLE pos__products');
        $this->addSql('DROP TABLE customer__apartment');
        $this->addSql('DROP TABLE customer__vendor');
        $this->addSql('DROP TABLE client__tags');
        $this->addSql('DROP TABLE customer__vendor_contact');
        $this->addSql('DROP TABLE customer__payments');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE customer__transaction_status');
        $this->addSql('DROP TABLE customer__product__tag');
        $this->addSql('DROP TABLE customer__invoice');
        $this->addSql('DROP TABLE customer__invoice_product');
        $this->addSql('DROP TABLE pos');
        $this->addSql('DROP TABLE email__recipient');
        $this->addSql('DROP TABLE email__log');
        $this->addSql('DROP TABLE email__auto');
        $this->addSql('DROP TABLE media__image');
    }
}
