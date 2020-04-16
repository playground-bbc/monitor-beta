<?php

use yii\db\Migration;

/**
 * Class m200406_204328_Insights
 */
class m200406_204328_Insights extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_insights}}',[
            'id'              => $this->primaryKey(),
            'content_id'      => $this->integer()->notNull(),
            'name'            => $this->string(),
            'title'           => $this->string(),
            'description'     => $this->text(),
            'insights_id'     => $this->string(),
            'period'          => $this->string(),
            'value'           => $this->integer(),
            '_like'           => $this->integer()->defaultValue(0),
            '_love'           => $this->integer()->defaultValue(0),
            '_wow'           => $this->integer()->defaultValue(0),
            '_haha'           => $this->integer()->defaultValue(0),
            '_sorry'           => $this->integer()->defaultValue(0),
            '_anger'           => $this->integer()->defaultValue(0),
            'end_time'        => $this->integer(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        /*$this->insert('{{%w_insights}}', [
            'content_id'    => 1,
            'name'          => 'page_impressions',
            'title'         => 'Daily Total Impressions',
            'description'   => 'Daily: The number of times any content)',
            'insights_id'   => '169441517247/insights/page_impressions/day',
            'period'        => 'day',
            'value'         => 43622,
            '_like'         => 43622,
            '_love'         => 43622,
            'end_time'      => '1488153462',
            'createdAt'     => '1488153462',
            'updatedAt'     => '1488153462',
            'createdBy'     => '1',
            'updatedBy'     => '1',
        ]);
*/
         // creates index for column `content_id`
        $this->createIndex(
            'idx-w_insights-content_id',
            'w_insights',
            'content_id'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-w_insights-w_insights',
            'w_insights',
            'content_id',
            'w_content',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%w_insights}}');
    }
    
}
