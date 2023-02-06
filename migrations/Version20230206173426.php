<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230206173426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recruter ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recruter ADD CONSTRAINT FK_F633FB4DF5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F633FB4DF5B7AF75 ON recruter (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recruter DROP FOREIGN KEY FK_F633FB4DF5B7AF75');
        $this->addSql('DROP INDEX UNIQ_F633FB4DF5B7AF75 ON recruter');
        $this->addSql('ALTER TABLE recruter DROP address_id');
    }
}
