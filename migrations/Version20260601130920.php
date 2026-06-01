<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260601130920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "character" ADD backpack_capacity INT NOT NULL');
        $this->addSql('ALTER TABLE item_definition ADD rarity VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE item_definition ADD base_gold_price INT NOT NULL');
        $this->addSql('ALTER TABLE item_definition ADD base_diamond_price INT NOT NULL');
        $this->addSql('ALTER TABLE shop_offer ADD item_definition_id INT NOT NULL');
        $this->addSql('ALTER TABLE shop_offer DROP slot');
        $this->addSql('ALTER TABLE shop_offer ADD CONSTRAINT FK_EEC0DD6C3DB201CA FOREIGN KEY (item_definition_id) REFERENCES item_definition (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_EEC0DD6C3DB201CA ON shop_offer (item_definition_id)');
        $this->addSql('ALTER TABLE shop_rotation ADD rotation_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character DROP backpack_capacity');
        $this->addSql('ALTER TABLE item_definition DROP rarity');
        $this->addSql('ALTER TABLE item_definition DROP base_gold_price');
        $this->addSql('ALTER TABLE item_definition DROP base_diamond_price');
        $this->addSql('ALTER TABLE shop_offer DROP CONSTRAINT FK_EEC0DD6C3DB201CA');
        $this->addSql('DROP INDEX IDX_EEC0DD6C3DB201CA');
        $this->addSql('ALTER TABLE shop_offer ADD slot VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE shop_offer DROP item_definition_id');
        $this->addSql('ALTER TABLE shop_rotation DROP rotation_type');
    }
}
