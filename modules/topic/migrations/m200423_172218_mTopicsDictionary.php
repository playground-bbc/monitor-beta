<?php

use yii\db\Migration;

/**
 * Class m200423_172218_mTopicsDictionary
 */
class m200423_172218_mTopicsDictionary extends Migration
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

        $this->createTable('{{%m_topics_dictionary}}',[
            'id'              => $this->primaryKey(),
            'topicId'         => $this->integer()->notNull(),
            'dictionaryID'    => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `topicId`
        $this->createIndex(
            'idx-m_topics_dictionary-m_topic',
            'm_topics_dictionary',
            'topicId'
        );

        // add foreign key for table `m_topic`
        $this->addForeignKey(
            'fk-m_topics_dictionary-m_topic',
            'm_topics_dictionary',
            'topicId',
            'm_topics',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // creates index for column `dictionaryID`
        $this->createIndex(
            'idx-m_topics_dictionary-m_dictionary',
            'm_topics_dictionary',
            'dictionaryID'
        );

        // add foreign key for table `m_dictionaries`
        $this->addForeignKey(
            'fk-m_topics_dictionary-m_dictionary',
            'm_topics_dictionary',
            'dictionaryID',
            'm_dictionaries',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%m_topics_dictionary}}');
    }
    
}
