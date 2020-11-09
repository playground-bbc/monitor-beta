<?php

use yii\db\Migration;

/**
 * Class m201016_204924_page_element
 */
class m201016_204924_page_element extends Migration
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

        $this->createTable('{{%page_element}}',[
            'id'              => $this->primaryKey(),
            'pageId'          => $this->integer()->notNull(),
            'name'            => $this->string(),
            'value'           => $this->string(),
            'status'          => $this->smallInteger(1)->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

         // creates index for column `pageId`
         $this->createIndex(
            'idx-page_page_element',
            'page_element',
            'pageId'
        );

        $this->addForeignKey(
            'page_page_element',
            'page_element',
            'pageId',
            'page',
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
        $this->dropTable('{{%page_element}}');
    }

    
}
