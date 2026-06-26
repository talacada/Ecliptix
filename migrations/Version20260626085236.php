<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260626085236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_definition ADD elixir_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE item_definition ADD percentage_bonus INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_definition ADD duration_seconds INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item_definition ADD item_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item_definition DROP elixir_type');
        $this->addSql('ALTER TABLE item_definition DROP percentage_bonus');
        $this->addSql('ALTER TABLE item_definition DROP duration_seconds');
        $this->addSql('ALTER TABLE item_definition DROP item_type');
    }
}
