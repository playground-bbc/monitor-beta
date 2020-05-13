<?php

use yii\db\Migration;

/**
 * Class m200423_143523_mUrlsTopics
 */
class m200423_143523_mUrlsTopics extends Migration
{
    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_urls_topics}}',[
            'id'              => $this->primaryKey(),
            'topicId'         => $this->integer()->notNull(),
            'url'             => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%m_urls_topics}}', [
            'topicId'         => 1,
            'url'             => 'http://testing-ground.scraping.pro/',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_urls_topics}}', [
            'topicId'         => 1,
            'url'             => 'http://testing-ground.scraping.pro/blocks',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);


        // creates index for column `topicId`
        $this->createIndex(
            'idx-m_urls_topics-topicId',
            'm_urls_topics',
            'topicId'
        );

        // add foreign key for table `m_urls_topics`
        $this->addForeignKey(
            'fk-m_urls_topics-topicId',
            'm_urls_topics',
            'topicId',
            'm_topics',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%m_urls_topics}}');
    }
    
}
