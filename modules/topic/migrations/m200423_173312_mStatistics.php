<?php

use yii\db\Migration;

/**
 * Class m200423_173312_mStatistics
 */
class m200423_173312_mStatistics extends Migration
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

        $this->createTable('{{%m_statistics}}',[
            'id'              => $this->primaryKey(),
            'topicStaticId'   => $this->integer()->notNull(),
            'total'           => $this->integer()->notNull(),
            'timespan'        => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `topicStaticId`
        $this->createIndex(
            'idx-m_statistics-topicStaticId',
            'm_statistics',
            'topicStaticId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-m_statistics-topicStaticId',
            'm_statistics',
            'topicStaticId',
            'm_topics_stadistics',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%m_statistics}}');
    }
    
}
