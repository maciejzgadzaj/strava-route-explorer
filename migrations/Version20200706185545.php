<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200706185545 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

//        $this->addSql('ALTER TABLE route_tag DROP PRIMARY KEY');
//        $this->addSql('ALTER TABLE route_tag CHANGE route_id route_id VARCHAR(255) NOT NULL');
//        $this->addSql('ALTER TABLE route_tag ADD PRIMARY KEY (route_id) REFERENCES route (route_id)');

//        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C42079FE6BCB8B');

        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C42079FE6BCB8B');
        $this->addSql('ALTER TABLE route_starred_by DROP FOREIGN KEY FK_80F4F26734ECB4E6');
        $this->addSql('ALTER TABLE route_starred_by DROP FOREIGN KEY FK_80F4F267FE6BCB8B');
        $this->addSql('ALTER TABLE route_tag DROP FOREIGN KEY FK_210074E934ECB4E6');
        $this->addSql('ALTER TABLE route_tag DROP FOREIGN KEY FK_210074E9BAD26311');

        $this->addSql('ALTER TABLE athlete CHANGE id id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE route CHANGE id id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE route CHANGE athlete_id athlete_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE route_starred_by CHANGE route_id route_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE route_starred_by CHANGE athlete_id athlete_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE route_tag CHANGE route_id route_id VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE route ADD CONSTRAINT fk_route_athlete_id FOREIGN KEY (athlete_id) REFERENCES athlete(id)');
        $this->addSql('ALTER TABLE route_starred_by ADD CONSTRAINT fk_route_starred_by_route_id FOREIGN KEY (route_id) REFERENCES route(id)');
        $this->addSql('ALTER TABLE route_starred_by ADD CONSTRAINT fk_route_starred_by_athlete_id FOREIGN KEY (athlete_id) REFERENCES athlete(id)');
        $this->addSql('ALTER TABLE route_tag ADD CONSTRAINT fk_route_tag_route_id FOREIGN KEY (route_id) REFERENCES route(id)');
        $this->addSql('ALTER TABLE route_tag ADD CONSTRAINT fk_route_tag_tag_id FOREIGN KEY (tag_id) REFERENCES tag(id)');
    }

    public function down(Schema $schema) : void
    {
    }
}
