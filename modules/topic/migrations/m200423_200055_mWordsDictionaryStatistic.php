<?php

use yii\db\Migration;

/**
 * Class m200423_200055_mWordsDictionaryStatistic
 */
class m200423_200055_mWordsDictionaryStatistic extends Migration
{
    /**
     * {@inheritdoc}
     */
    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_words_dictionary_statistic}}',[
            'id'              => $this->primaryKey(),
            'keywordId'       => $this->integer()->notNull(),
            'statisticId'     => $this->integer()->notNull(),
            'count'           => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `keywordId`
        $this->createIndex(
            'idx-words_dictionary_statistic_keywords',
            'm_words_dictionary_statistic',
            'keywordId'
        );

        // add foreign key for table `m_keywords`
        $this->addForeignKey(
            'fk-words_dictionary_statistic_keywords',
            'm_words_dictionary_statistic',
            'keywordId',
            'm_keywords',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `statisticId`
        $this->createIndex(
            'idx-words_dictionary_statistic_statisticId',
            'm_words_dictionary_statistic',
            'statisticId'
        );

        // add foreign key for table `m_statistics`
        $this->addForeignKey(
            'fk-words_dictionary_statistic_statisticId',
            'm_words_dictionary_statistic',
            'statisticId',
            'm_statistics',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%m_words_dictionary_statistic}}');
    }
    
}
