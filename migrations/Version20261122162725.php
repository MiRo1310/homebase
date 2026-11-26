<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261122162725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE device_update (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, old_version VARCHAR(255) DEFAULT NULL, new_version VARCHAR(255) NOT NULL, origin VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, device_id INT NOT NULL, INDEX IDX_4B35446394A4C7D4 (device_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE device_update ADD CONSTRAINT FK_4B35446394A4C7D4 FOREIGN KEY (device_id) REFERENCES device (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device_update DROP FOREIGN KEY FK_4B35446394A4C7D4');
        $this->addSql('DROP TABLE device_update');
    }
}
