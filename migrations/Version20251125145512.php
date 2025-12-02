<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125145512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE capitaine (id INT AUTO_INCREMENT NOT NULL, nb_joker INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE epreuve (id INT AUTO_INCREMENT NOT NULL, le_score_id INT DEFAULT NULL, nom_epreuve VARCHAR(255) NOT NULL, libelle VARCHAR(255) NOT NULL, duree INT NOT NULL, difficulte INT NOT NULL, point_epreuve DOUBLE PRECISION NOT NULL, lieu_epreuve VARCHAR(255) NOT NULL, type_epreuve VARCHAR(255) NOT NULL, nb_indice_agagner INT NOT NULL, date_epreuve_debut DATETIME NOT NULL, date_epreuve_fin DATETIME NOT NULL, coeff_annee DOUBLE PRECISION NOT NULL, INDEX IDX_D6ADE47FDCA521B8 (le_score_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE epreuve_user (epreuve_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_F4EBAB89AB990336 (epreuve_id), INDEX IDX_F4EBAB89A76ED395 (user_id), PRIMARY KEY(epreuve_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, max_joueurs INT NOT NULL, point DOUBLE PRECISION NOT NULL, nom_equipe VARCHAR(255) NOT NULL, statut TINYINT(1) NOT NULL, nb_indice INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe_user (equipe_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_84DA47B76D861B89 (equipe_id), INDEX IDX_84DA47B7A76ED395 (user_id), PRIMARY KEY(equipe_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau_scolaire (id INT AUTO_INCREMENT NOT NULL, nom_classe VARCHAR(255) NOT NULL, annee VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE point_epreuve (id INT AUTO_INCREMENT NOT NULL, lieu_epreuve VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, les_equipes_id INT DEFAULT NULL, score DOUBLE PRECISION NOT NULL, INDEX IDX_329937514C2F1CA4 (les_equipes_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE surveillant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE surveillant_epreuve (surveillant_id INT NOT NULL, epreuve_id INT NOT NULL, INDEX IDX_CEA263B2AA23F281 (surveillant_id), INDEX IDX_CEA263B2AB990336 (epreuve_id), PRIMARY KEY(surveillant_id, epreuve_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, statut TINYINT(1) NOT NULL, point DOUBLE PRECISION NOT NULL, INDEX IDX_8D93D649A76ED395 (user_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE epreuve ADD CONSTRAINT FK_D6ADE47FDCA521B8 FOREIGN KEY (le_score_id) REFERENCES score (id)');
        $this->addSql('ALTER TABLE epreuve_user ADD CONSTRAINT FK_F4EBAB89AB990336 FOREIGN KEY (epreuve_id) REFERENCES epreuve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE epreuve_user ADD CONSTRAINT FK_F4EBAB89A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B76D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE equipe_user ADD CONSTRAINT FK_84DA47B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_329937514C2F1CA4 FOREIGN KEY (les_equipes_id) REFERENCES equipe (id)');
        $this->addSql('ALTER TABLE surveillant_epreuve ADD CONSTRAINT FK_CEA263B2AA23F281 FOREIGN KEY (surveillant_id) REFERENCES surveillant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE surveillant_epreuve ADD CONSTRAINT FK_CEA263B2AB990336 FOREIGN KEY (epreuve_id) REFERENCES epreuve (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A76ED395 FOREIGN KEY (user_id) REFERENCES niveau_scolaire (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE epreuve DROP FOREIGN KEY FK_D6ADE47FDCA521B8');
        $this->addSql('ALTER TABLE epreuve_user DROP FOREIGN KEY FK_F4EBAB89AB990336');
        $this->addSql('ALTER TABLE epreuve_user DROP FOREIGN KEY FK_F4EBAB89A76ED395');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B76D861B89');
        $this->addSql('ALTER TABLE equipe_user DROP FOREIGN KEY FK_84DA47B7A76ED395');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_329937514C2F1CA4');
        $this->addSql('ALTER TABLE surveillant_epreuve DROP FOREIGN KEY FK_CEA263B2AA23F281');
        $this->addSql('ALTER TABLE surveillant_epreuve DROP FOREIGN KEY FK_CEA263B2AB990336');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A76ED395');
        $this->addSql('DROP TABLE capitaine');
        $this->addSql('DROP TABLE epreuve');
        $this->addSql('DROP TABLE epreuve_user');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE equipe_user');
        $this->addSql('DROP TABLE niveau_scolaire');
        $this->addSql('DROP TABLE organisateur');
        $this->addSql('DROP TABLE point_epreuve');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE surveillant');
        $this->addSql('DROP TABLE surveillant_epreuve');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
