<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417184244 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cart_content DROP FOREIGN KEY FK_51FF8AE6C8A81A9');
        $this->addSql('DROP INDEX IDX_51FF8AE6C8A81A9 ON cart_content');
        $this->addSql('ALTER TABLE cart_content CHANGE products_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_content ADD CONSTRAINT FK_51FF8AE4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_51FF8AE4584665A ON cart_content (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE cart_content DROP FOREIGN KEY FK_51FF8AE4584665A');
        $this->addSql('DROP INDEX IDX_51FF8AE4584665A ON cart_content');
        $this->addSql('ALTER TABLE cart_content CHANGE product_id products_id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_content ADD CONSTRAINT FK_51FF8AE6C8A81A9 FOREIGN KEY (products_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_51FF8AE6C8A81A9 ON cart_content (products_id)');
    }
}
