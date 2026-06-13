<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260613164544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_offer ADD bonus_damage INT NOT NULL');
        $this->addSql('ALTER TABLE shop_offer ADD bonus_crit INT NOT NULL');
        $this->addSql('ALTER TABLE shop_offer ADD bonus_health INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_offer DROP bonus_damage');
        $this->addSql('ALTER TABLE shop_offer DROP bonus_crit');
        $this->addSql('ALTER TABLE shop_offer DROP bonus_health');
    }
}
