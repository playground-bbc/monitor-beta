<?php

use yii\db\Migration;

/**
 * Class m200423_160625_mTopicsStadistic
 */
class m200423_160625_mTopicsStadistic extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_topics_stadistics}}',[
            'id'              => $this->primaryKey(),
            'topicId'         => $this->integer(),
            'resourceId'      => $this->integer()->notNull(),
            'locationId'      => $this->integer(),
            'wordId'          => $this->integer()->notNull(),
          //  'attachmentId'    => $this->integer()->notNull(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `topicId`
        $this->createIndex(
            'idx-m_topics_stadistics-topicId',
            'm_topics_stadistics',
            'topicId'
        );

        // add foreign key for table `mTopics`
        $this->addForeignKey(
            'fk-m_topics_stadistics-topicId',
            'm_topics_stadistics',
            'topicId',
            'm_topics',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // creates index for column `resourceId`
        $this->createIndex(
            'idx-m_topics_stadistics-resourceId',
            'm_topics_stadistics',
            'resourceId'
        );

        // add foreign key for table `m_resources`
        $this->addForeignKey(
            'fk-m_topics_stadistics-resourceId',
            'm_topics_stadistics',
            'resourceId',
            'm_resources',
            'id',
            'CASCADE',
            'CASCADE'
        );

        /*// creates index for column `locationId`
        $this->createIndex(
            'idx-m_topics_stadistics-locationId',
            'm_topics_stadistics',
            'locationId'
        );

        // add foreign key for table `m_locations`
        $this->addForeignKey(
            'fk-m_topics_stadistics-locationId',
            'm_topics_stadistics',
            'locationId',
            'm_locations',
            'id',
            'CASCADE',
            'CASCADE'
        );*/

         // creates index for column `wordId`
        $this->createIndex(
            'idx-m_topics_stadistics_wordId',
            'm_topics_stadistics',
            'wordId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-m_topics_stadistics_wordId',
            'm_topics_stadistics',
            'wordId',
            'm_words',
            'id',
            'CASCADE',
            'CASCADE'
        );


        /*// creates index for column `attachmentId`
        $this->createIndex(
            'idx-m_topics_stadistics-attachmentId',
            'm_topics_stadistics',
            'attachmentId'
        );

        // add foreign key for table `m_attachments`
        $this->addForeignKey(
            'fk-m_topics_stadistics-attachmentId',
            'm_topics_stadistics',
            'attachmentId',
            'm_attachments',
            'id',
            'CASCADE',
            'CASCADE'
        );
*/

    }

    public function down()
    {
        $this->dropTable('{{%m_topics_stadistics}}');
    }
    
}
