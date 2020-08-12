<?php

use yii\db\Migration;

/**
 * Class m200406_204251_Content
 */
class m200406_204251_Content extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        //$tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_content}}',[
            'id'              => $this->primaryKey(),
            'type_content_id' => $this->integer()->notNull(),
            'resource_id'     => $this->integer()->notNull(),
            'content_id'      => $this->string()->notNull(),
            'message'         => $this->text(),
            'permalink'       => $this->text(),
            'image_url'       => $this->text(),
            'timespan'        => $this->integer(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        // creates index for column `type_content_id`
        $this->createIndex(
            'idx-content-type_content_id',
            'w_content',
            'type_content_id'
        );

        // add foreign key for table `w_type_content`
        $this->addForeignKey(
            'fk-content-type_content_id',
            'w_content',
            'type_content_id',
            'w_type_content',
            'id',
            'CASCADE',
            'CASCADE'
        );


        // creates index for column `resource_id`
        $this->createIndex(
            'idx-content-resources_resourcesId',
            'w_content',
            'resource_id'
        );

        // add foreign key for table `resources`
        $this->addForeignKey(
            'fk-content-resources_resourcesId',
            'w_content',
            'resource_id',
            'resources',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    public function down()
    {
        $this->dropTable('{{%w_content}}');
    }
    
}
