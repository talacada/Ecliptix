<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261005123729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_verification_token DROP CONSTRAINT fk_c4995c671136be75');
        $this->addSql('ALTER TABLE email_verification_token ADD CONSTRAINT FK_C4995C671136BE75 FOREIGN KEY (character_id) REFERENCES character (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE password_reset_token DROP CONSTRAINT fk_6b7ba4b61136be75');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B61136BE75 FOREIGN KEY (character_id) REFERENCES character (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_verification_token DROP CONSTRAINT FK_C4995C671136BE75');
        $this->addSql('ALTER TABLE email_verification_token ADD CONSTRAINT fk_c4995c671136be75 FOREIGN KEY (character_id) REFERENCES "character" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE password_reset_token DROP CONSTRAINT FK_6B7BA4B61136BE75');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT fk_6b7ba4b61136be75 FOREIGN KEY (character_id) REFERENCES "character" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
