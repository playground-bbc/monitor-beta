<?php

use yii\db\Migration;

/**
 * Class m201016_191002_presentation
 */
class m201016_191002_presentation extends Migration
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

        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%presentation}}',[
            'id'              => $this->primaryKey(),
            'userId'          => $this->integer()->notNull(),
            'name'            => $this->string()->notNull(),
            'head_title'      => $this->string()->notNull(),
            'title'           => $this->string()->notNull(),
            'date'            => $this->integer()->notNull(),
            'url_sheet'       => $this->string(),
            'url_presentation'=> $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'updated'         => $this->smallInteger(1)->defaultValue(0),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%presentation}}', [
            'userId'          => 1,
            'name'            => 'Reporte Mensual LG Octubre',
            'head_title'      => 'Reporte LG',
            'title'           => 'Octubre 2020',
            'date'            => 1559312912,
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

         // creates index for column `userId`
        $this->createIndex(
            'idx-userId_presentation',
            'presentation',
            'userId'
        );

        $this->addForeignKey(
            'userId_presentation',
            'presentation',
            'userId',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%presentation}}');
    }

}
