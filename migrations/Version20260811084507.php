<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260811084507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "character" ALTER prestige_points DROP DEFAULT');
        $this->addSql('ALTER TABLE email_verification_token RENAME COLUMN exipres_at TO expires_at');
        $this->addSql('ALTER INDEX idx_f56f2103ac24f853 RENAME TO IDX_F56F21031136BE75');
        $this->addSql('ALTER INDEX idx_f56f2103d956f010 RENAME TO IDX_F56F21036A5458E8');
        $this->addSql('ALTER INDEX uniq_f56f2103ac24f853d956f010 RENAME TO UNIQ_F56F21031136BE756A5458E8');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character ALTER prestige_points SET DEFAULT 0');
        $this->addSql('ALTER TABLE email_verification_token RENAME COLUMN expires_at TO exipres_at');
        $this->addSql('ALTER INDEX idx_f56f21036a5458e8 RENAME TO idx_f56f2103d956f010');
        $this->addSql('ALTER INDEX uniq_f56f21031136be756a5458e8 RENAME TO uniq_f56f2103ac24f853d956f010');
        $this->addSql('ALTER INDEX idx_f56f21031136be75 RENAME TO idx_f56f2103ac24f853');
    }
}
