<?php

use yii\db\Migration;

/**
 * Class mm200423_150418_mLocations
 */
class m200423_150418_mLocations extends Migration
{
        // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_locations}}', [
            'id'          => $this->primaryKey(),
            'parentId'    => $this->integer(),
            'name'        => $this->string(40)->notNull(),
            'woeid'       => $this->integer()->notNull(),
            'createdAt'   => $this->integer(),
            'updatedAt'   => $this->integer(),
            'createdBy'   => $this->integer(),
            'updatedBy'   => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%m_locations}}', [
            'name'            => 'Chile',
            'woeid'           => 23424782,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_locations}}', [
            'name'            => 'United States',
            'woeid'           => 23424977,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

    }

    public function down()
    {
        $this->dropTable('{{%m_locations}}');
    }
}
