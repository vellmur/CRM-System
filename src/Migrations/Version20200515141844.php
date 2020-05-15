<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200515141844 extends AbstractMigration
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
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, locale INT NOT NULL, date_format INT DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATE DEFAULT NULL, enabled TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D6494D2A7E12 (building_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device__page_views (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, module_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, page VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_66BAC7EF94A4C7D4 (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user__device (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ip VARCHAR(15) DEFAULT NULL, is_computer TINYINT(1) NOT NULL, os VARCHAR(30) NOT NULL, browser VARCHAR(30) NOT NULL, browser_version VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_A8114F07A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__posts (id INT AUTO_INCREMENT NOT NULL, thumb_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F385F1482B36786B (title), INDEX IDX_F385F148C7034EA5 (thumb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email (id INT AUTO_INCREMENT NOT NULL, automated_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, is_draft TINYINT(1) NOT NULL, in_process TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9C4A37C6B1254A89 (automated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email_recipient (id INT AUTO_INCREMENT NOT NULL, email_id INT NOT NULL, user_id INT NOT NULL, email_address VARCHAR(50) NOT NULL, is_sent TINYINT(1) NOT NULL, is_delivered TINYINT(1) NOT NULL, is_opened TINYINT(1) NOT NULL, is_clicked TINYINT(1) NOT NULL, is_bounced TINYINT(1) NOT NULL, INDEX IDX_8A5C4BC4A832C1C9 (email_id), INDEX IDX_8A5C4BC4A76ED395 (user_id), UNIQUE INDEX master_email_recipient (email_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE master__email_automated (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, type INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__notifies (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, notify_type INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_D4DC51699395C3F3 (customer_id), UNIQUE INDEX customer_emails_unique (customer_id, notify_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer__apartment (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, number VARCHAR(10) NOT NULL, UNIQUE INDEX UNIQ_FD2BCDCB96901F54 (number), INDEX IDX_FD2BCDCB4D2A7E12 (building_id), UNIQUE INDEX aparment_unique (building_id, number), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, building_id INT DEFAULT NULL, apartment_id INT NOT NULL, firstname VARCHAR(25) NOT NULL, lastname VARCHAR(25) NOT NULL, email VARCHAR(50) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at DATE NOT NULL, token VARCHAR(50) NOT NULL, is_activated TINYINT(1) NOT NULL, INDEX IDX_81398E094D2A7E12 (building_id), INDEX IDX_81398E09176DFE85 (apartment_id), UNIQUE INDEX customer_unique (building_id, email), UNIQUE INDEX customer_phone_unique (building_id, phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__recipient (id INT AUTO_INCREMENT NOT NULL, log_id INT NOT NULL, customer_id INT DEFAULT NULL, email_address VARCHAR(50) NOT NULL, is_sent TINYINT(1) NOT NULL, is_delivered TINYINT(1) NOT NULL, is_opened TINYINT(1) NOT NULL, is_clicked TINYINT(1) NOT NULL, is_bounced TINYINT(1) NOT NULL, INDEX IDX_892BA88BEA675D86 (log_id), INDEX IDX_892BA88B9395C3F3 (customer_id), UNIQUE INDEX customer_email_recipient (log_id, customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__log (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, automated_id INT DEFAULT NULL, reply_email VARCHAR(50) NOT NULL, reply_name VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, is_draft TINYINT(1) NOT NULL, in_process TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5D6251BA4D2A7E12 (building_id), INDEX IDX_5D6251BAB1254A89 (automated_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email__auto (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, subject VARCHAR(255) NOT NULL, text TEXT NOT NULL, type INT NOT NULL, INDEX IDX_A6D2146E4D2A7E12 (building_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__subscription (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, amount NUMERIC(8, 2) NOT NULL, INDEX IDX_840161F54D2A7E12 (building_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__posts (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, thumb_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_12D38C8D2B36786B (title), INDEX IDX_12D38C8D4D2A7E12 (building_id), INDEX IDX_12D38C8DC7034EA5 (thumb_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__referral (id INT AUTO_INCREMENT NOT NULL, building_id INT DEFAULT NULL, affiliate_id INT NOT NULL, is_paid TINYINT(1) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_A1A5DC1D4D2A7E12 (building_id), INDEX IDX_A1A5DC1D9F12C49A (affiliate_id), UNIQUE INDEX referral_unique (building_id, affiliate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification__notify (id INT AUTO_INCREMENT NOT NULL, notification_id INT DEFAULT NULL, user_id INT DEFAULT NULL, seen TINYINT(1) NOT NULL, INDEX IDX_CD5680FCEF1A9D84 (notification_id), INDEX IDX_CD5680FCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(4000) NOT NULL, message VARCHAR(4000) DEFAULT NULL, link VARCHAR(4000) DEFAULT NULL, module_id INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__address (id INT AUTO_INCREMENT NOT NULL, country VARCHAR(2) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, region VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building (id INT AUTO_INCREMENT NOT NULL, address_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(50) NOT NULL, currency INT DEFAULT NULL, timezone VARCHAR(30) DEFAULT NULL, token VARCHAR(30) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_E16F61D45E237E06 (name), UNIQUE INDEX UNIQ_E16F61D4F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__module_access (id INT AUTO_INCREMENT NOT NULL, building_id INT NOT NULL, module_id INT NOT NULL, expired_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, status INT NOT NULL, INDEX IDX_7E4029834D2A7E12 (building_id), UNIQUE INDEX access_unique (building_id, module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE building__affiliate (id INT AUTO_INCREMENT NOT NULL, building_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, email VARCHAR(25) DEFAULT NULL, referral_code VARCHAR(20) NOT NULL, created_at DATE NOT NULL, UNIQUE INDEX UNIQ_3AAE6B576447454A (referral_code), UNIQUE INDEX UNIQ_3AAE6B574D2A7E12 (building_id), UNIQUE INDEX affiliate_unique (name, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media__image (id INT AUTO_INCREMENT NOT NULL, building_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F37C721D4D2A7E12 (building_id), UNIQUE INDEX building_image_unique (building_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE device__promotion_visit ADD CONSTRAINT FK_80A5FFF2C4663E4 FOREIGN KEY (page_id) REFERENCES device__page_views (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE device__page_views ADD CONSTRAINT FK_66BAC7EF94A4C7D4 FOREIGN KEY (device_id) REFERENCES user__device (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user__device ADD CONSTRAINT FK_A8114F07A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE master__posts ADD CONSTRAINT FK_F385F148C7034EA5 FOREIGN KEY (thumb_id) REFERENCES media__image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE master__email ADD CONSTRAINT FK_9C4A37C6B1254A89 FOREIGN KEY (automated_id) REFERENCES master__email_automated (id)');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC4A832C1C9 FOREIGN KEY (email_id) REFERENCES master__email (id)');
        $this->addSql('ALTER TABLE master__email_recipient ADD CONSTRAINT FK_8A5C4BC4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer__notifies ADD CONSTRAINT FK_D4DC51699395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE customer__apartment ADD CONSTRAINT FK_FD2BCDCB4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E094D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09176DFE85 FOREIGN KEY (apartment_id) REFERENCES customer__apartment (id)');
        $this->addSql('ALTER TABLE email__recipient ADD CONSTRAINT FK_892BA88BEA675D86 FOREIGN KEY (log_id) REFERENCES email__log (id)');
        $this->addSql('ALTER TABLE email__recipient ADD CONSTRAINT FK_892BA88B9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE email__log ADD CONSTRAINT FK_5D6251BA4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE email__log ADD CONSTRAINT FK_5D6251BAB1254A89 FOREIGN KEY (automated_id) REFERENCES email__auto (id)');
        $this->addSql('ALTER TABLE email__auto ADD CONSTRAINT FK_A6D2146E4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE building__subscription ADD CONSTRAINT FK_840161F54D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE building__posts ADD CONSTRAINT FK_12D38C8D4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE building__posts ADD CONSTRAINT FK_12D38C8DC7034EA5 FOREIGN KEY (thumb_id) REFERENCES media__image (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE building__referral ADD CONSTRAINT FK_A1A5DC1D4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE building__referral ADD CONSTRAINT FK_A1A5DC1D9F12C49A FOREIGN KEY (affiliate_id) REFERENCES building__affiliate (id)');
        $this->addSql('ALTER TABLE notification__notify ADD CONSTRAINT FK_CD5680FCEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)');
        $this->addSql('ALTER TABLE notification__notify ADD CONSTRAINT FK_CD5680FCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE building ADD CONSTRAINT FK_E16F61D4F5B7AF75 FOREIGN KEY (address_id) REFERENCES building__address (id)');
        $this->addSql('ALTER TABLE building__module_access ADD CONSTRAINT FK_7E4029834D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE building__affiliate ADD CONSTRAINT FK_3AAE6B574D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
        $this->addSql('ALTER TABLE media__image ADD CONSTRAINT FK_F37C721D4D2A7E12 FOREIGN KEY (building_id) REFERENCES building (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user__device DROP FOREIGN KEY FK_A8114F07A76ED395');
        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC4A76ED395');
        $this->addSql('ALTER TABLE notification__notify DROP FOREIGN KEY FK_CD5680FCA76ED395');
        $this->addSql('ALTER TABLE device__promotion_visit DROP FOREIGN KEY FK_80A5FFF2C4663E4');
        $this->addSql('ALTER TABLE device__page_views DROP FOREIGN KEY FK_66BAC7EF94A4C7D4');
        $this->addSql('ALTER TABLE master__email_recipient DROP FOREIGN KEY FK_8A5C4BC4A832C1C9');
        $this->addSql('ALTER TABLE master__email DROP FOREIGN KEY FK_9C4A37C6B1254A89');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09176DFE85');
        $this->addSql('ALTER TABLE customer__notifies DROP FOREIGN KEY FK_D4DC51699395C3F3');
        $this->addSql('ALTER TABLE email__recipient DROP FOREIGN KEY FK_892BA88B9395C3F3');
        $this->addSql('ALTER TABLE email__recipient DROP FOREIGN KEY FK_892BA88BEA675D86');
        $this->addSql('ALTER TABLE email__log DROP FOREIGN KEY FK_5D6251BAB1254A89');
        $this->addSql('ALTER TABLE notification__notify DROP FOREIGN KEY FK_CD5680FCEF1A9D84');
        $this->addSql('ALTER TABLE building DROP FOREIGN KEY FK_E16F61D4F5B7AF75');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494D2A7E12');
        $this->addSql('ALTER TABLE customer__apartment DROP FOREIGN KEY FK_FD2BCDCB4D2A7E12');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E094D2A7E12');
        $this->addSql('ALTER TABLE email__log DROP FOREIGN KEY FK_5D6251BA4D2A7E12');
        $this->addSql('ALTER TABLE email__auto DROP FOREIGN KEY FK_A6D2146E4D2A7E12');
        $this->addSql('ALTER TABLE building__subscription DROP FOREIGN KEY FK_840161F54D2A7E12');
        $this->addSql('ALTER TABLE building__posts DROP FOREIGN KEY FK_12D38C8D4D2A7E12');
        $this->addSql('ALTER TABLE building__referral DROP FOREIGN KEY FK_A1A5DC1D4D2A7E12');
        $this->addSql('ALTER TABLE building__module_access DROP FOREIGN KEY FK_7E4029834D2A7E12');
        $this->addSql('ALTER TABLE building__affiliate DROP FOREIGN KEY FK_3AAE6B574D2A7E12');
        $this->addSql('ALTER TABLE media__image DROP FOREIGN KEY FK_F37C721D4D2A7E12');
        $this->addSql('ALTER TABLE building__referral DROP FOREIGN KEY FK_A1A5DC1D9F12C49A');
        $this->addSql('ALTER TABLE master__posts DROP FOREIGN KEY FK_F385F148C7034EA5');
        $this->addSql('ALTER TABLE building__posts DROP FOREIGN KEY FK_12D38C8DC7034EA5');
        $this->addSql('DROP TABLE device__promotion_visit');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE device__page_views');
        $this->addSql('DROP TABLE user__device');
        $this->addSql('DROP TABLE master__posts');
        $this->addSql('DROP TABLE master__email');
        $this->addSql('DROP TABLE master__email_recipient');
        $this->addSql('DROP TABLE master__email_automated');
        $this->addSql('DROP TABLE customer__notifies');
        $this->addSql('DROP TABLE customer__apartment');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE email__recipient');
        $this->addSql('DROP TABLE email__log');
        $this->addSql('DROP TABLE email__auto');
        $this->addSql('DROP TABLE building__subscription');
        $this->addSql('DROP TABLE building__posts');
        $this->addSql('DROP TABLE building__referral');
        $this->addSql('DROP TABLE notification__notify');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE building__address');
        $this->addSql('DROP TABLE building');
        $this->addSql('DROP TABLE building__module_access');
        $this->addSql('DROP TABLE building__affiliate');
        $this->addSql('DROP TABLE media__image');
    }
}
