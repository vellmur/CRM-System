<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200422172442 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE client__merchant');
        $this->addSql('DROP TABLE client__payment_settings');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84B239936447454A ON client__affiliate (referral_code)');
        $this->addSql('ALTER TABLE client DROP level, DROP delivery_price, DROP order_time, DROP same_day_orders');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client__merchant (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, merchant VARCHAR(1) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, merchant_key VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, merchant_pin VARCHAR(4) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, currency VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_sandbox TINYINT(1) NOT NULL, UNIQUE INDEX merchant_unique (client_id, merchant), INDEX IDX_B14BB08E19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE client__payment_settings (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, method INT NOT NULL, is_active TINYINT(1) NOT NULL, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX member_unique (client_id, method), INDEX IDX_2609750819EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE client__merchant ADD CONSTRAINT FK_B14BB08E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE client__payment_settings ADD CONSTRAINT FK_2609750819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client ADD level INT NOT NULL, ADD delivery_price NUMERIC(7, 2) NOT NULL, ADD order_time VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD same_day_orders TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX UNIQ_84B239936447454A ON client__affiliate');
    }
}
