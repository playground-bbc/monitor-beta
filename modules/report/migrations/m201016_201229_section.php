<?php

use yii\db\Migration;

/**
 * Class m201016_201229_section
 */
class m201016_201229_section extends Migration
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

        $this->createTable('{{%section}}',[
            'id'              => $this->primaryKey(),
            'presentationId'  => $this->integer()->notNull(),
            'typeSection'     => $this->integer()->notNull(),
            'head_title'      => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%section}}', [
            'presentationId'  => 1,
            'typeSection'     => 1,
            'head_title'      => 'TRENDS Y NOTICIAS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        

        $this->insert('{{%section}}', [
            'presentationId'  => 1,
            'typeSection'     => 1,
            'head_title'      => 'COMPETENCIA',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        

         // creates index for column `presentationId`
        $this->createIndex(
            'idx-presentation_section',
            'section',
            'presentationId'
        );

        $this->addForeignKey(
            'presentation_section',
            'section',
            'presentationId',
            'presentation',
            'id',
            'CASCADE',
            'CASCADE'
        );

         // creates index for column `presentationId`
         $this->createIndex(
            'idx-type_section',
            'section',
            'typeSection'
        );

        $this->addForeignKey(
            'type_section',
            'section',
            'typeSection',
            'section_type',
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
        $this->dropTable('{{%section}}');
    }

}
