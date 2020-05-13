<?php

use yii\db\Migration;

/**
 * Class m200423_170419_mTopicsLocations
 */
class m200423_170419_mTopicsLocations extends Migration
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

        $this->createTable('{{%m_topics_location}}',[
            'id'              => $this->primaryKey(),
            'topicId'         => $this->integer()->notNull(),
            'locationId'      => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `topicId`
        $this->createIndex(
            'idx-m_topics_location-m_topic',
            'm_topics_location',
            'topicId'
        );

        // add foreign key for table `m_topic`
        $this->addForeignKey(
            'fk-m_topics_location-m_topic',
            'm_topics_location',
            'topicId',
            'm_topics',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `locationId`
        $this->createIndex(
            'idx-m_topic_resources-m_locations',
            'm_topics_location',
            'locationId'
        );

        // add foreign key for table `locations`
        $this->addForeignKey(
            'fk-m_topic_resources-m_locations',
            'm_topics_location',
            'locationId',
            'm_locations',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%m_topics_location}}');
    }
    
}
