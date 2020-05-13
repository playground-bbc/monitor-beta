<?php

use yii\db\Migration;

/**
 * Class m200423_144434_mResources
 */
class m200423_144434_mResources extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%m_resources}}', [
            'id'          => $this->primaryKey(),
            'name'        => $this->string(40)->notNull(),
            'createdAt'   => $this->integer(),
            'updatedAt'   => $this->integer(),
            'createdBy'   => $this->integer(),
            'updatedBy'   => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%m_resources}}', [
            'name'            => 'Twitter',
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_resources}}', [
            'name'            => 'Paginas Webs',
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%m_resources}}', [
            'name'            => 'Instagram',
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

    }

    public function down()
    {
        $this->dropTable('{{%m_resources}}');
    }
    
}
