<?php

use yii\db\Migration;

/**
 * Class m190813_203806_Alert_Config
 */
class m190813_203806_Alert_Config extends Migration
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

        $this->createTable('{{%alert_config}}', [
            'id'                  => $this->primaryKey(),
            'alertId'             => $this->integer(11)->notNull()->unique(),
            'uuid'                => $this->string(),
            'product_description' => $this->string(40)->notNull(),
            'competitors'         => $this->string(40)->notNull(),
            'urls'                => $this->text(),
            'country'             => $this->json(),
            'url_drive'           => $this->string(40),
            'start_date'          => $this->integer(),
            'end_date'            => $this->integer(),
            'createdAt'           => $this->integer(),
            'updatedAt'           => $this->integer(),
            'createdBy'           => $this->integer(),
            'updatedBy'           => $this->integer(),

        ], $tableOptions);


        $this->insert('{{%alert_config}}', [
            'alertId'             => 1,
            'uuid'                => 'Boom_1559312912',
            'product_description' => 'tecnology,Home entretaiment',
            'competitors'         => 'Sansung,Iphone,Hyundai',
            'country'             => null,
            'start_date'          => '1575226766',
            'end_date'            => '1576177166',
            'createdAt'           => '1565888154',
            'updatedAt'           => '1565888154',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

         // creates index for column `alertId`
        $this->createIndex(
            'idx-alert_config-alertId',
            'alert_config',
            'alertId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-alert_config-alertId',
            'alert_config',
            'alertId',
            'alerts',
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
        $this->dropTable('{{%alert_Config}}');
    }
}
