<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230126174252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE applied_candidate (id INT AUTO_INCREMENT NOT NULL, candidate_id INT NOT NULL, offer_id INT NOT NULL, validated TINYINT(1) NOT NULL, INDEX IDX_FD80737A91BD8781 (candidate_id), INDEX IDX_FD80737A53C674EE (offer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidate (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, fist_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, cv_name VARCHAR(255) DEFAULT NULL, activated TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_C8B28E44A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, title VARCHAR(255) NOT NULL, post_date DATETIME NOT NULL, week_hours INT NOT NULL, net_salary INT NOT NULL, description LONGTEXT NOT NULL, validated TINYINT(1) NOT NULL, archived TINYINT(1) NOT NULL, INDEX IDX_29D6873E64D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE applied_candidate ADD CONSTRAINT FK_FD80737A91BD8781 FOREIGN KEY (candidate_id) REFERENCES candidate (id)');
        $this->addSql('ALTER TABLE applied_candidate ADD CONSTRAINT FK_FD80737A53C674EE FOREIGN KEY (offer_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE candidate ADD CONSTRAINT FK_C8B28E44A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE applied_candidate DROP FOREIGN KEY FK_FD80737A91BD8781');
        $this->addSql('ALTER TABLE applied_candidate DROP FOREIGN KEY FK_FD80737A53C674EE');
        $this->addSql('ALTER TABLE candidate DROP FOREIGN KEY FK_C8B28E44A76ED395');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E64D218E');
        $this->addSql('DROP TABLE applied_candidate');
        $this->addSql('DROP TABLE candidate');
        $this->addSql('DROP TABLE offer');
    }
}
