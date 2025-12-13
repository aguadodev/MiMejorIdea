<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212194744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE viaje_solicitud (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, viaje_id INT NOT NULL, pasajero_id INT NOT NULL, INDEX IDX_D9B975B394E1E648 (viaje_id), INDEX IDX_D9B975B3704716FE (pasajero_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE viaje_solicitud ADD CONSTRAINT FK_D9B975B394E1E648 FOREIGN KEY (viaje_id) REFERENCES viaje (id)');
        $this->addSql('ALTER TABLE viaje_solicitud ADD CONSTRAINT FK_D9B975B3704716FE FOREIGN KEY (pasajero_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE viaje_solicitud DROP FOREIGN KEY FK_D9B975B394E1E648');
        $this->addSql('ALTER TABLE viaje_solicitud DROP FOREIGN KEY FK_D9B975B3704716FE');
        $this->addSql('DROP TABLE viaje_solicitud');
    }
}
