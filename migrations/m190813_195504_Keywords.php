<?php

use yii\db\Migration;

/**
 * Class m190813_195504_Keywords
 */
class m190813_195504_Keywords extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%keywords}}', [
            'id'                    => $this->primaryKey(),
            'dictionaryId'          => $this->integer(),
            'name'                  => $this->string()->notNull()->unique(),
            'createdAt'             => $this->integer(),
            'updatedAt'             => $this->integer(),
            'createdBy'             => $this->integer(),
            'updatedBy'             => $this->integer(),

        ], $tableOptions);


        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-dictionary-dictionaries',
            'keywords',
            'dictionaryId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-dictionary-dictionaries',
            'keywords',
            'dictionaryId',
            'dictionaries',
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
        $this->dropTable('{{%keywords}}');
    }

}
