<?php

use yii\db\Migration;

/**
 * Class m201016_191001_section_type
 */
class m201016_191001_section_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%section_type}}',[
            'id'              => $this->primaryKey(),
            'name'            => $this->string()->notNull(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%section_type}}', [
            'name'           => 'static',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%section_type}}', [
            'name'           => 'fixed',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%section_type}}', [
            'name'           => 'dinamic',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%section_type}}', [
            'name'           => 'sac',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%section_type}}');

    }
}
