<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191031124952 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE unite_militaire (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, identifier INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile (id INT AUTO_INCREMENT NOT NULL, unitemilitaire_id INT NOT NULL, name VARCHAR(50) NOT NULL, identifier INT NOT NULL, INDEX IDX_8157AA0FED097BE (unitemilitaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plane (id INT AUTO_INCREMENT NOT NULL, profile_id INT DEFAULT NULL, kills INT NOT NULL, money INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_C1B32D80CCFA12B8 (profile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0FED097BE FOREIGN KEY (unitemilitaire_id) REFERENCES unite_militaire (id)');
        $this->addSql('ALTER TABLE plane ADD CONSTRAINT FK_C1B32D80CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE profile DROP FOREIGN KEY FK_8157AA0FED097BE');
        $this->addSql('ALTER TABLE plane DROP FOREIGN KEY FK_C1B32D80CCFA12B8');
        $this->addSql('DROP TABLE unite_militaire');
        $this->addSql('DROP TABLE profile');
        $this->addSql('DROP TABLE plane');
    }
}
