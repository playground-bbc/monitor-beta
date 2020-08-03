<?php

use yii\db\Migration;

/**
 * Class m191231_142330_history_search
 */
class m191231_142330_history_search extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%history_search}}',[
            'id'              => $this->primaryKey(),
            'alertId'         => $this->integer()->notNull(),
            'search_data'     => $this->json(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `alertId`
        $this->createIndex(
            'idx-history_search-alertId',
            'history_search',
            'alertId',
            true // unique
        );

        // add foreign key for table `alerts`
        $this->addForeignKey(
            'fk-history_search-alertId',
            'history_search',
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
    public function down()
    {
        $this->dropTable('{{%history_search}}');
    }

}
