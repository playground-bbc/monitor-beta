<?php

use yii\db\Migration;

/**
 * Class m200526_145305_Logs_User
 */
class m200526_145305_Logs_User extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_logs}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'remote_addr'     => $this->string(),
            'log_date'        => $this->datetime(),
            'message'         => $this->text(),
            'user_agent'      => $this->json(),      
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

         // creates index for column `userId`
        $this->createIndex(
            'idx-user_logs',
            'user_logs',
            'userId'
        );

        $this->addForeignKey(
            'fx-user_logs',
            'user_logs',
            'userId',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%user_logs}}');
    }
    
}
