<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011123623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget_revenue (id INT AUTO_INCREMENT NOT NULL, budget_id INT DEFAULT NULL, exercise_id INT DEFAULT NULL, bank_account_id INT DEFAULT NULL, bank_id INT DEFAULT NULL, cash_desk_id INT DEFAULT NULL, settled_by_id INT DEFAULT NULL, user_id INT DEFAULT NULL, institution_id INT NOT NULL, year_id INT DEFAULT NULL, reference VARCHAR(100) NOT NULL, settled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', request_amount DOUBLE PRECISION DEFAULT NULL, validated_amount DOUBLE PRECISION DEFAULT NULL, vat DOUBLE PRECISION DEFAULT NULL, applicant VARCHAR(100) DEFAULT NULL, reason VARCHAR(100) DEFAULT NULL, is_cash TINYINT(1) NOT NULL, is_validated TINYINT(1) NOT NULL, is_open TINYINT(1) NOT NULL, is_edited TINYINT(1) NOT NULL, is_enable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2B92266436ABA6B8 (budget_id), INDEX IDX_2B922664E934951A (exercise_id), INDEX IDX_2B92266412CB990C (bank_account_id), INDEX IDX_2B92266411C8FB41 (bank_id), INDEX IDX_2B9226642F15CD02 (cash_desk_id), INDEX IDX_2B922664C55A90E1 (settled_by_id), INDEX IDX_2B922664A76ED395 (user_id), INDEX IDX_2B92266410405986 (institution_id), INDEX IDX_2B92266440C1FEA7 (year_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B92266436ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B922664E934951A FOREIGN KEY (exercise_id) REFERENCES budget_exercise (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B92266412CB990C FOREIGN KEY (bank_account_id) REFERENCES treasury_bank_account (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B92266411C8FB41 FOREIGN KEY (bank_id) REFERENCES treasury_bank (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B9226642F15CD02 FOREIGN KEY (cash_desk_id) REFERENCES treasury_cash_desk (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B922664C55A90E1 FOREIGN KEY (settled_by_id) REFERENCES security_user (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B922664A76ED395 FOREIGN KEY (user_id) REFERENCES security_user (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B92266410405986 FOREIGN KEY (institution_id) REFERENCES security_institution (id)');
        $this->addSql('ALTER TABLE budget_revenue ADD CONSTRAINT FK_2B92266440C1FEA7 FOREIGN KEY (year_id) REFERENCES security_year (id)');
        $this->addSql('ALTER TABLE budget_line ADD is_type TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE school_class CHANGE school_id school_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE school_class_program ADD branch_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE school_class_program ADD CONSTRAINT FK_7B8ABC46DCD6CC49 FOREIGN KEY (branch_id) REFERENCES security_branch (id)');
        $this->addSql('CREATE INDEX IDX_7B8ABC46DCD6CC49 ON school_class_program (branch_id)');
        $this->addSql('ALTER TABLE school_fee ADD unit_id INT DEFAULT NULL, ADD default_quantity DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE school_fee ADD CONSTRAINT FK_BAECE91EF8BD700D FOREIGN KEY (unit_id) REFERENCES product_unit (id)');
        $this->addSql('CREATE INDEX IDX_BAECE91EF8BD700D ON school_fee (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B92266436ABA6B8');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B922664E934951A');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B92266412CB990C');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B92266411C8FB41');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B9226642F15CD02');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B922664C55A90E1');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B922664A76ED395');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B92266410405986');
        $this->addSql('ALTER TABLE budget_revenue DROP FOREIGN KEY FK_2B92266440C1FEA7');
        $this->addSql('DROP TABLE budget_revenue');
        $this->addSql('ALTER TABLE budget_line DROP is_type');
        $this->addSql('ALTER TABLE school_class CHANGE school_id school_id INT NOT NULL');
        $this->addSql('ALTER TABLE school_class_program DROP FOREIGN KEY FK_7B8ABC46DCD6CC49');
        $this->addSql('DROP INDEX IDX_7B8ABC46DCD6CC49 ON school_class_program');
        $this->addSql('ALTER TABLE school_class_program DROP branch_id');
        $this->addSql('ALTER TABLE school_fee DROP FOREIGN KEY FK_BAECE91EF8BD700D');
        $this->addSql('DROP INDEX IDX_BAECE91EF8BD700D ON school_fee');
        $this->addSql('ALTER TABLE school_fee DROP unit_id, DROP default_quantity');
    }
}
