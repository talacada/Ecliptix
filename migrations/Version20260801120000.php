<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260801120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert default races and appearance options';
    }

    public function up(Schema $schema): void
    {
        // Races
        $this->addSql("INSERT INTO race (id, name) VALUES (1, 'Human')");
        $this->addSql("INSERT INTO race (id, name) VALUES (2, 'Elf')");
        $this->addSql("INSERT INTO race (id, name) VALUES (3, 'Dwarf')");
        $this->addSql("INSERT INTO race (id, name) VALUES (4, 'Demon')");

        // Human — race_id = 1
        // Hair
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (1, 1, 'hair', 'Short', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (2, 1, 'hair', 'Long', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (3, 1, 'hair', 'Bald', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (4, 1, 'hair', 'Ponytail', 4)");
        // Eyes
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (5, 1, 'eyes', 'Blue', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (6, 1, 'eyes', 'Brown', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (7, 1, 'eyes', 'Green', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (8, 1, 'eyes', 'Gray', 4)");
        // Mouth
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (9, 1, 'mouth', 'Smile', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (10, 1, 'mouth', 'Serious', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (11, 1, 'mouth', 'Open', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (12, 1, 'mouth', 'Grin', 4)");
        // Nose
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (13, 1, 'nose', 'Straight', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (14, 1, 'nose', 'Snub', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (15, 1, 'nose', 'Aquiline', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (16, 1, 'nose', 'Broad', 4)");
        // Ears
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (17, 1, 'ears', 'Normal', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (18, 1, 'ears', 'Small', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (19, 1, 'ears', 'Pointed', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (20, 1, 'ears', 'Round', 4)");

        // Elf — race_id = 2
        // Hair
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (21, 2, 'hair', 'Long', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (22, 2, 'hair', 'Flowing', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (23, 2, 'hair', 'Braid', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (24, 2, 'hair', 'Silver', 4)");
        // Eyes
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (25, 2, 'eyes', 'Emerald', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (26, 2, 'eyes', 'Sapphire', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (27, 2, 'eyes', 'Amber', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (28, 2, 'eyes', 'Violet', 4)");
        // Mouth
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (29, 2, 'mouth', 'Elegant', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (30, 2, 'mouth', 'Mysterious', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (31, 2, 'mouth', 'Subtle Smile', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (32, 2, 'mouth', 'Proud', 4)");
        // Nose
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (33, 2, 'nose', 'Slender', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (34, 2, 'nose', 'Pointed', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (35, 2, 'nose', 'Delicate', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (36, 2, 'nose', 'Narrow', 4)");
        // Ears
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (37, 2, 'ears', 'Long Pointed', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (38, 2, 'ears', 'Medium Pointed', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (39, 2, 'ears', 'Curved', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (40, 2, 'ears', 'Small Pointed', 4)");

        // Dwarf — race_id = 3
        // Hair
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (41, 3, 'hair', 'Bushy', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (42, 3, 'hair', 'Braided Beard', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (43, 3, 'hair', 'Short', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (44, 3, 'hair', 'Mohawk', 4)");
        // Eyes
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (45, 3, 'eyes', 'Deep Brown', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (46, 3, 'eyes', 'Black', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (47, 3, 'eyes', 'Stone Gray', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (48, 3, 'eyes', 'Coal', 4)");
        // Mouth
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (49, 3, 'mouth', 'Grumpy', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (50, 3, 'mouth', 'Cunning Smile', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (51, 3, 'mouth', 'Frowning', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (52, 3, 'mouth', 'Laughing', 4)");
        // Nose
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (53, 3, 'nose', 'Large', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (54, 3, 'nose', 'Bulbous', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (55, 3, 'nose', 'Broken', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (56, 3, 'nose', 'Wide', 4)");
        // Ears
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (57, 3, 'ears', 'Round', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (58, 3, 'ears', 'Small', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (59, 3, 'ears', 'Hairy', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (60, 3, 'ears', 'Stout', 4)");

        // Demon — race_id = 4
        // Hair
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (61, 4, 'hair', 'Spiky', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (62, 4, 'hair', 'Horns', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (63, 4, 'hair', 'Bald with Horns', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (64, 4, 'hair', 'Fiery', 4)");
        // Eyes
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (65, 4, 'eyes', 'Red', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (66, 4, 'eyes', 'Burning Orange', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (67, 4, 'eyes', 'Black Void', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (68, 4, 'eyes', 'Purple', 4)");
        // Mouth
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (69, 4, 'mouth', 'Fanged', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (70, 4, 'mouth', 'Cruel Smile', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (71, 4, 'mouth', 'Snarling', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (72, 4, 'mouth', 'Twisted', 4)");
        // Nose
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (73, 4, 'nose', 'Hooked', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (74, 4, 'nose', 'Flat', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (75, 4, 'nose', 'Pointed', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (76, 4, 'nose', 'Reptilian', 4)");
        // Ears
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (77, 4, 'ears', 'Bat-like', 1)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (78, 4, 'ears', 'Pointed', 2)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (79, 4, 'ears', 'Jagged', 3)");
        $this->addSql("INSERT INTO appearance_option (id, race_id, type, label, sort_order) VALUES (80, 4, 'ears', 'Horned', 4)");

        // Reset sequences to match inserted IDs
        $this->addSql("SELECT setval('race_id_seq', (SELECT MAX(id) FROM race))");
        $this->addSql("SELECT setval('appearance_option_id_seq', (SELECT MAX(id) FROM appearance_option))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM appearance_option WHERE id BETWEEN 1 AND 80');
        $this->addSql('DELETE FROM race WHERE id BETWEEN 1 AND 4');
    }
}