<?php

use yii\db\Migration;

/**
 * Class m200423_153039_mTopicResource
 */
class m200423_153039_mTopicResource extends Migration
{
    

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_topic_resources}}',[
            'id'              => $this->primaryKey(),
            'topicId'         => $this->integer()->notNull(),
            'resourceId'      => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%m_topic_resources}}', [
            'topicId'         => 1,
            'resourceId'      => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_topic_resources}}', [
            'topicId'         => 1,
            'resourceId'      => 2,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_topic_resources}}', [
            'topicId'         => 1,
            'resourceId'      => 3,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        // creates index for column `topicId`
        $this->createIndex(
            'idx-m_topic_resources-m_topic',
            'm_topic_resources',
            'topicId'
        );

        // add foreign key for table `m_topic`
        $this->addForeignKey(
            'fk-m_topic_resources-m_topic',
            'm_topic_resources',
            'topicId',
            'm_topics',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `resourceId`
        $this->createIndex(
            'idx-m_topic_resources-m_resources',
            'm_topic_resources',
            'resourceId'
        );

        // add foreign key for table `resources`
        $this->addForeignKey(
            'fk-topic_resources-m_resources',
            'm_topic_resources',
            'resourceId',
            'm_resources',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%m_topic_resources}}');
    }
    
}
