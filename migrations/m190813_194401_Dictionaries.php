<?php

use yii\db\Migration;

/**
 * Class m190813_194401_Dictionaries
 */
class m190813_194401_Dictionaries extends Migration
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

        $this->createTable('{{%dictionaries}}', [
            'id'                    => $this->primaryKey(),
            'name'                  => $this->string(45)->notNull()->unique(),
            'color'                 => $this->string(45)->notNull(),
            'createdAt'             => $this->integer(11),
            'updatedAt'             => $this->integer(11),
            'createdBy'             => $this->integer(11),
            'updatedBy'             => $this->integer(11),

        ], $tableOptions);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'Free Words',
            'color'                 => '#f5ebeb',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Positivos MH Series',
            'color'                 => '#0e6ae1',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario MH Series',
            'color'                 => '#14d299',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Kws Positivos',
            'color'                 => '#96b011',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Frases Negativas',
            'color'                 => '#e82626',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Kws Negativos',
            'color'                 => '#c7871a',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Negativos MH Series',
            'color'                 => '#ae540a',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%dictionaries}}', [
            'name'                  => 'diccionario Frases Positivas',
            'color'                 => '#321863',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        // $this->insert('{{%dictionaries}}', [
        //     'name'                  => 'Product description',
        //     'color'                 => '#f7978f',
        //     'createdAt'             => '1488153462',
        //     'updatedAt'             => '1488153462',
        //     'createdBy'             => '1',
        //     'updatedBy'             => '1',
        // ]);

        // $this->insert('{{%dictionaries}}', [
        //     'name'                  => 'Product Competition',
        //     'color'                 => '#f27979',
        //     'createdAt'             => '1488153462',
        //     'updatedAt'             => '1488153462',
        //     'createdBy'             => '1',
        //     'updatedBy'             => '1',
        // ]);

        // $this->insert('{{%dictionaries}}', [
        //     'name'                  => 'Neutral Words',
        //     'color'                 => '#f27980',
        //     'createdAt'             => '1488153462',
        //     'updatedAt'             => '1488153462',
        //     'createdBy'             => '1',
        //     'updatedBy'             => '1',
        // ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%dictionaries}}');
    }

}
