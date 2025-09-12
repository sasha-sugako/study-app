<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510143444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bonus (id SERIAL NOT NULL, owner_id INT NOT NULL, type VARCHAR(100) NOT NULL, granted_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_used BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9F987F7A7E3C61F9 ON bonus (owner_id)');
        $this->addSql('COMMENT ON COLUMN bonus.granted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE card (id SERIAL NOT NULL, deck_id INT DEFAULT NULL, front_side TEXT NOT NULL, back_side TEXT NOT NULL, to_learn TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_learned TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, learn_score INT DEFAULT NULL, front_image VARCHAR(255) DEFAULT NULL, back_image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_161498D3111948DC ON card (deck_id)');
        $this->addSql('COMMENT ON COLUMN card.to_learn IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN card.last_learned IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE TABLE deck (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_private BOOLEAN NOT NULL, about TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4FAC3637727ACA70 ON deck (parent_id)');
        $this->addSql('CREATE INDEX IDX_4FAC36377E3C61F9 ON deck (owner_id)');
        $this->addSql('CREATE TABLE deck_category (deck_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(deck_id, category_id))');
        $this->addSql('CREATE INDEX IDX_5F7BAAE3111948DC ON deck_category (deck_id)');
        $this->addSql('CREATE INDEX IDX_5F7BAAE312469DE2 ON deck_category (category_id)');
        $this->addSql('CREATE TABLE goal (id SERIAL NOT NULL, owner_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, target_cards INT DEFAULT NULL, achieved_cards INT DEFAULT NULL, target_tests INT DEFAULT NULL, achieved_tests INT DEFAULT NULL, completed BOOLEAN NOT NULL, bonus_granted BOOLEAN NOT NULL, is_current BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FCDCEB2E7E3C61F9 ON goal (owner_id)');
        $this->addSql('COMMENT ON COLUMN goal.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN goal.end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE notification (id SERIAL NOT NULL, person_to_notificate_id INT NOT NULL, message VARCHAR(255) NOT NULL, is_read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CA2A823CAB ON notification (person_to_notificate_id)');
        $this->addSql('COMMENT ON COLUMN notification.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE review (id SERIAL NOT NULL, deck_id INT NOT NULL, reviewed_by_id INT NOT NULL, rate INT NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_794381C6111948DC ON review (deck_id)');
        $this->addSql('CREATE INDEX IDX_794381C6FC6B21F1 ON review (reviewed_by_id)');
        $this->addSql('CREATE TABLE test (id SERIAL NOT NULL, deck_id INT NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, number_of_questions INT NOT NULL, qurrent_question INT NOT NULL, finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, types_of_questions JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D87F7E0C111948DC ON test (deck_id)');
        $this->addSql('COMMENT ON COLUMN test.started_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN test.finished_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE test_result (id SERIAL NOT NULL, test_id INT NOT NULL, card_id INT DEFAULT NULL, user_answer JSON DEFAULT NULL, correct_answer JSON NOT NULL, question_type INT NOT NULL, question_number INT NOT NULL, is_correct BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84B3C63D1E5D0459 ON test_result (test_id)');
        $this->addSql('CREATE INDEX IDX_84B3C63D4ACC9A20 ON test_result (card_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, login VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, last_active TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, days_without_break INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649AA08CB10 ON "user" (login)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".last_active IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE bonus ADD CONSTRAINT FK_9F987F7A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637727ACA70 FOREIGN KEY (parent_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36377E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deck_category ADD CONSTRAINT FK_5F7BAAE3111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE deck_category ADD CONSTRAINT FK_5F7BAAE312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE goal ADD CONSTRAINT FK_FCDCEB2E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2A823CAB FOREIGN KEY (person_to_notificate_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test ADD CONSTRAINT FK_D87F7E0C111948DC FOREIGN KEY (deck_id) REFERENCES deck (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D1E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bonus DROP CONSTRAINT FK_9F987F7A7E3C61F9');
        $this->addSql('ALTER TABLE card DROP CONSTRAINT FK_161498D3111948DC');
        $this->addSql('ALTER TABLE deck DROP CONSTRAINT FK_4FAC3637727ACA70');
        $this->addSql('ALTER TABLE deck DROP CONSTRAINT FK_4FAC36377E3C61F9');
        $this->addSql('ALTER TABLE deck_category DROP CONSTRAINT FK_5F7BAAE3111948DC');
        $this->addSql('ALTER TABLE deck_category DROP CONSTRAINT FK_5F7BAAE312469DE2');
        $this->addSql('ALTER TABLE goal DROP CONSTRAINT FK_FCDCEB2E7E3C61F9');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CA2A823CAB');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6111948DC');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6FC6B21F1');
        $this->addSql('ALTER TABLE test DROP CONSTRAINT FK_D87F7E0C111948DC');
        $this->addSql('ALTER TABLE test_result DROP CONSTRAINT FK_84B3C63D1E5D0459');
        $this->addSql('ALTER TABLE test_result DROP CONSTRAINT FK_84B3C63D4ACC9A20');
        $this->addSql('DROP TABLE bonus');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE deck');
        $this->addSql('DROP TABLE deck_category');
        $this->addSql('DROP TABLE goal');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE test');
        $this->addSql('DROP TABLE test_result');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
