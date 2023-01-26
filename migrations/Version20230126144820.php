<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230126144820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recruter (user_id INT NOT NULL, address_id INT DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, activated TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_F633FB4DF5B7AF75 (address_id), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recruter ADD CONSTRAINT FK_F633FB4DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE recruter ADD CONSTRAINT FK_F633FB4DF5B7AF75 FOREIGN KEY (address_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recruter DROP FOREIGN KEY FK_F633FB4DA76ED395');
        $this->addSql('ALTER TABLE recruter DROP FOREIGN KEY FK_F633FB4DF5B7AF75');
        $this->addSql('DROP TABLE recruter');
    }
}
