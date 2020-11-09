<?php

use yii\db\Migration;

/**
 * Class m201016_203030_page
 */
class m201016_203030_page extends Migration
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

        $this->createTable('{{%page}}',[
            'id'              => $this->primaryKey(),
            'sectionId'       => $this->integer()->notNull(),
            'title'           => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%page}}', [
            'sectionId'       => 1,
            'title'           => 'REPORTES Y TENDENCIAS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%page}}', [
            'sectionId'       => 1,
            'title'           => 'NOTICIAS: MARCAS DESTACADAS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%page}}', [
            'sectionId'       => 1,
            'title'           => 'SOCIAL MEDIA TRENDS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);


        $this->insert('{{%page}}', [
            'sectionId'       => 2,
            'title'           => 'SAMSUNG / EMBAJADOR',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%page}}', [
            'sectionId'       => 2,
            'title'           => 'SAMSUNG / CONCURSOS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

        $this->insert('{{%page}}', [
            'sectionId'       => 2,
            'title'           => 'SAMSUNG / ANUNCIOS',
            'status'          => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);

     
         // creates index for column `sectionId`
        $this->createIndex(
            'idx-section_page',
            'page',
            'sectionId'
        );

        $this->addForeignKey(
            'section_page',
            'page',
            'sectionId',
            'section',
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
        $this->dropTable('{{%page}}');
    }
}
