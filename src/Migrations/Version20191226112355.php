<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191226112355 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phone DROP FOREIGN KEY FK_444F97DD71179CD6');
        $this->addSql('DROP INDEX IDX_444F97DD71179CD6 ON phone');
        $this->addSql('ALTER TABLE phone DROP name_id');
        $this->addSql('ALTER TABLE users ADD phones_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9EBFCD53E FOREIGN KEY (phones_id) REFERENCES phone (id)');
        $this->addSql('CREATE INDEX IDX_1483A5E9EBFCD53E ON users (phones_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phone ADD name_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE phone ADD CONSTRAINT FK_444F97DD71179CD6 FOREIGN KEY (name_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_444F97DD71179CD6 ON phone (name_id)');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9EBFCD53E');
        $this->addSql('DROP INDEX IDX_1483A5E9EBFCD53E ON users');
        $this->addSql('ALTER TABLE users DROP phones_id');
    }
}
