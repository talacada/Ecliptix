<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261120151456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_elixir DROP CONSTRAINT fk_3cf83d8e3db201ca');
        $this->addSql('DROP INDEX idx_3cf83d8e3db201ca');
        $this->addSql('ALTER TABLE active_elixir RENAME COLUMN item_definition_id TO elixir_definition_id');
        $this->addSql('ALTER TABLE active_elixir ADD CONSTRAINT FK_3CF83D8EF8671818 FOREIGN KEY (elixir_definition_id) REFERENCES item_definition (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3CF83D8EF8671818 ON active_elixir (elixir_definition_id)');
        $this->addSql('ALTER TABLE "character" ADD crit INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE active_elixir DROP CONSTRAINT FK_3CF83D8EF8671818');
        $this->addSql('DROP INDEX IDX_3CF83D8EF8671818');
        $this->addSql('ALTER TABLE active_elixir RENAME COLUMN elixir_definition_id TO item_definition_id');
        $this->addSql('ALTER TABLE active_elixir ADD CONSTRAINT fk_3cf83d8e3db201ca FOREIGN KEY (item_definition_id) REFERENCES item_definition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_3cf83d8e3db201ca ON active_elixir (item_definition_id)');
        $this->addSql('ALTER TABLE character DROP crit');
    }
}
