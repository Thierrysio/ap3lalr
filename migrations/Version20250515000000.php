<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250515000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute la relation participants entre épreuve et utilisateur et remplace la durée par un entier';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE epreuve_user (epreuve_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_C736F6D3AB990336 (epreuve_id), INDEX IDX_C736F6D3A76ED395 (user_id), PRIMARY KEY(epreuve_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE epreuve CHANGE duree duree INT NOT NULL');
        $this->addSql('ALTER TABLE epreuve_user ADD CONSTRAINT FK_C736F6D3AB990336 FOREIGN KEY (epreuve_id) REFERENCES epreuve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE epreuve_user ADD CONSTRAINT FK_C736F6D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE epreuve_user DROP FOREIGN KEY FK_C736F6D3AB990336');
        $this->addSql('ALTER TABLE epreuve_user DROP FOREIGN KEY FK_C736F6D3A76ED395');
        $this->addSql('DROP TABLE epreuve_user');
        $this->addSql('ALTER TABLE epreuve CHANGE duree duree DATETIME NOT NULL');
    }
}
