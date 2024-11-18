<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004102159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE products_size (products_id INT NOT NULL, size_id INT NOT NULL, INDEX IDX_7BD7EA606C8A81A9 (products_id), INDEX IDX_7BD7EA60498DA827 (size_id), PRIMARY KEY(products_id, size_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE size (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE products_size ADD CONSTRAINT FK_7BD7EA606C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products_size ADD CONSTRAINT FK_7BD7EA60498DA827 FOREIGN KEY (size_id) REFERENCES size (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products_size DROP FOREIGN KEY FK_7BD7EA606C8A81A9');
        $this->addSql('ALTER TABLE products_size DROP FOREIGN KEY FK_7BD7EA60498DA827');
        $this->addSql('DROP TABLE products_size');
        $this->addSql('DROP TABLE size');
    }
}
