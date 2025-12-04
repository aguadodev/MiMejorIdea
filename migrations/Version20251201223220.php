<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251201223220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, address VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, street VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, postcode VARCHAR(20) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE perfil_personal (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) DEFAULT NULL, apellidos VARCHAR(100) DEFAULT NULL, fecha_nacimiento DATE DEFAULT NULL, telefono VARCHAR(20) DEFAULT NULL, user_id INT NOT NULL, home_location_id INT DEFAULT NULL, work_location_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1D374821A76ED395 (user_id), UNIQUE INDEX UNIQ_1D374821BB811CC3 (home_location_id), INDEX IDX_1D374821198CC14D (work_location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE perfil_personal ADD CONSTRAINT FK_1D374821A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE perfil_personal ADD CONSTRAINT FK_1D374821BB811CC3 FOREIGN KEY (home_location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE perfil_personal ADD CONSTRAINT FK_1D374821198CC14D FOREIGN KEY (work_location_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE perfil_personal DROP FOREIGN KEY FK_1D374821A76ED395');
        $this->addSql('ALTER TABLE perfil_personal DROP FOREIGN KEY FK_1D374821BB811CC3');
        $this->addSql('ALTER TABLE perfil_personal DROP FOREIGN KEY FK_1D374821198CC14D');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE perfil_personal');
    }
}
