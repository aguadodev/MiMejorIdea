<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209235913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE viaje (id INT AUTO_INCREMENT NOT NULL, fecha_hora DATETIME NOT NULL, plazas SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, start_location_id INT NOT NULL, end_location_id INT NOT NULL, conductor_id INT NOT NULL, INDEX IDX_1D41ED165C3A313A (start_location_id), INDEX IDX_1D41ED16C43C7F1 (end_location_id), INDEX IDX_1D41ED16A49DECF0 (conductor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE viaje ADD CONSTRAINT FK_1D41ED165C3A313A FOREIGN KEY (start_location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE viaje ADD CONSTRAINT FK_1D41ED16C43C7F1 FOREIGN KEY (end_location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE viaje ADD CONSTRAINT FK_1D41ED16A49DECF0 FOREIGN KEY (conductor_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE viaje DROP FOREIGN KEY FK_1D41ED165C3A313A');
        $this->addSql('ALTER TABLE viaje DROP FOREIGN KEY FK_1D41ED16C43C7F1');
        $this->addSql('ALTER TABLE viaje DROP FOREIGN KEY FK_1D41ED16A49DECF0');
        $this->addSql('DROP TABLE viaje');
    }
}
