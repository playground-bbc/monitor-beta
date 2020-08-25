<?php

use yii\db\Migration;

/**
 * Class m200807_205308_alerts_keywords
 */
class m200807_205308_alerts_keywords extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%alerts_keywords}}',[
            'id'              => $this->primaryKey(),
            'alertId'         => $this->integer()->notNull(),
            'keywordId'       => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        // creates index for column `alertId`
        $this->createIndex(
            'idx-alerts_keywords_alertId',
            'alerts_keywords',
            'alertId'
        );

        // add foreign key for table `alert_config`
        $this->addForeignKey(
            'fk-alerts_keywords_alertId',
            'alerts_keywords',
            'alertId',
            'alerts',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `keyword`
        $this->createIndex(
            'idx-alerts_keywords_keywordId',
            'alerts_keywords',
            'keywordId'
        );

        // add foreign key for table `resources`
        $this->addForeignKey(
            'fk-alerts_keywords_keywordId',
            'alerts_keywords',
            'keywordId',
            'keywords',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%alerts_keywords}}');
    }
    
}
