<?php

use yii\db\Migration;

/**
 * Class m200423_143348_mTopics
 */
class m200423_143348_mTopics extends Migration
{
    

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_topics}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'name'            => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'end_date'        => $this->integer(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%m_topics}}', [
            'userId'          => 1,
            'name'            => 'Tiempos de Covid',
            'status'          => 1,
            'end_date'        => 1592924191,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

         // creates index for column `userId`
        $this->createIndex(
            'idx-usertopic_userId_topic',
            'alerts',
            'userId'
        );

        $this->addForeignKey(
            'usertopic_userId_topic',
            'm_topics',
            'userId',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%m_topics}}');
    }
    
}
