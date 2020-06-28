<?php

use yii\db\Migration;

/**
 * Class m200628_185855_Alerts_Mentions_words
 */
class m200628_185855_Alerts_Mentions_words extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%alerts_mencions_words}}',[
            'id'              => $this->primaryKey(),
            'alert_mentionId' => $this->integer()->notNull(),
            'mention_socialId' => $this->bigInteger(64)->defaultValue(0),
            'name'            => $this->string(),
            'weight'          => $this->integer()->defaultValue(1),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `alert_mentionId`
        $this->createIndex(
            'idx-alerts_mencions_words',
            'alerts_mencions_words',
            'alert_mentionId'
        );

        // add foreign key for table `alerts_mencions`
        $this->addForeignKey(
            'fk-alerts_mencions_words',
            'alerts_mencions_words',
            'alert_mentionId',
            'alerts_mencions',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%alertsalerts_mencions_words}}');
    }

}
