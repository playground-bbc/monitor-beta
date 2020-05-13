<?php

use yii\db\Migration;

/**
 * Class m200423_171117_mDictionaries
 */
class m200423_171117_mDictionaries extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_dictionaries}}', [
            'id'                    => $this->primaryKey(),
            'name'                  => $this->string(45)->notNull(),
            'color'                 => $this->string(45),
            'createdAt'             => $this->integer(11),
            'updatedAt'             => $this->integer(11),
            'createdBy'             => $this->integer(11),
            'updatedBy'             => $this->integer(11),

        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%m_dictionaries}}');
    }
    
}
