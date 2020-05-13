<?php

use yii\db\Migration;

/**
 * Class m200423_171707_mKeywords
 */
class m200423_171707_mKeywords extends Migration
{
    /**
     * {@inheritdoc}
     */

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_keywords}}', [
            'id'                    => $this->primaryKey(),
            'dictionaryId'          => $this->integer()->notNull(),
            'name'                  => $this->string(),
            'createdAt'             => $this->integer(),
            'updatedAt'             => $this->integer(),
            'createdBy'             => $this->integer(),
            'updatedBy'             => $this->integer(),

        ], $tableOptions);

        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-m_keywords-dictionaryId',
            'm_keywords',
            'dictionaryId'
        );

        // add foreign key for table `m_dictionaries`
        $this->addForeignKey(
            'fk-m_keywords-dictionaryId',
            'm_keywords',
            'dictionaryId',
            'm_dictionaries',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%m_keywords}}');
    }
    
}
