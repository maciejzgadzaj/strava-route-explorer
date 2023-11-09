<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231104133749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updating those MySQL tables to my liking';
    }

    public function up(Schema $schema): void
    {
        // Pluralize column names.
        $this->addSql('RENAME TABLE route TO routes');
        $this->addSql('RENAME TABLE athlete TO athletes');

        // Delete indexes to prepare to change column types.
        $this->addSql('ALTER TABLE routes DROP FOREIGN KEY fk_route_athlete_id');
        $this->addSql('ALTER TABLE route_starred_by DROP FOREIGN KEY fk_route_starred_by_athlete_id');
        $this->addSql('ALTER TABLE route_starred_by DROP FOREIGN KEY fk_route_starred_by_route_id');

        // Change column types.
        $this->addSql('ALTER TABLE athletes MODIFY id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE routes MODIFY id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE routes MODIFY athlete_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE route_starred_by MODIFY athlete_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE route_starred_by MODIFY route_id BIGINT NOT NULL');

        // Recreate deleted indexes.
        $this->addSql('ALTER TABLE routes ADD FOREIGN KEY (athlete_id) REFERENCES athletes(id)');
        $this->addSql('ALTER TABLE route_starred_by ADD FOREIGN KEY (athlete_id) REFERENCES athletes(id)');
        $this->addSql('ALTER TABLE route_starred_by ADD FOREIGN KEY (route_id) REFERENCES routes(id)');

        // Rename columns.
        $this->addSql('ALTER TABLE routes CHANGE ascent elevation_gain DECIMAL(10,2)');

        // Change column order.
        $this->addSql('ALTER TABLE routes MODIFY COLUMN type TINYINT NOT NULL AFTER name');
        $this->addSql('ALTER TABLE routes MODIFY COLUMN sub_type TINYINT NOT NULL AFTER type');
        $this->addSql('ALTER TABLE routes MODIFY COLUMN public TINYINT AFTER sub_type');
        $this->addSql('ALTER TABLE routes MODIFY COLUMN created_at DATETIME NOT NULL AFTER tags');
        $this->addSql('ALTER TABLE routes MODIFY COLUMN updated_at DATETIME NOT NULL AFTER created_at');
        $this->addSql('ALTER TABLE athletes MODIFY COLUMN access_token VARCHAR(255) AFTER last_sync');

        // Add new columns.
        $this->addSql('ALTER TABLE routes ADD private TINYINT(1) DEFAULT NULL AFTER sub_type');
        $this->addSql('ALTER TABLE routes ADD map_url TEXT DEFAULT NULL AFTER end');
        $this->addSql('ALTER TABLE athletes ADD country VARCHAR(255) AFTER email');
        $this->addSql('ALTER TABLE athletes ADD roles JSON NOT NULL AFTER refresh_token');
        $this->addSql('UPDATE athletes SET roles = \'["ROLE_USER"]\'');
        $this->addSql('UPDATE athletes SET roles = \'["ROLE_SUPER_ADMIN"]\' WHERE id=1295877');
    }

    public function down(Schema $schema): void
    {
    }
}
