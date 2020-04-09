<?php

use yii\db\Migration;

/**
 * Class m200406_204351_Attachments
 */
class m200406_204351_Attachments extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_attachments}}',[
            'id'              => $this->primaryKey(),
            'content_id'      => $this->integer()->notNull(),
            'title'           => $this->string(),
            'type'            => $this->string(),
            'src_url'         => $this->text(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        /*$this->insert('{{%w_attachments}}', [
            'content_id'    => 1,
            'title'         => 'some name',
            'type'          => 'video',
            'src_url'       => 'some url',
            'createdAt'     => '1488153462',
            'updatedAt'     => '1488153462',
            'createdBy'     => '1',
            'updatedBy'     => '1',
        ]);*/

         // creates index for column `content_id`
        $this->createIndex(
            'idx-w_attachments-content_id',
            'w_attachments',
            'content_id'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-w_attachments-content_id',
            'w_attachments',
            'content_id',
            'w_content',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%w_attachments}}');
    }
    
}
