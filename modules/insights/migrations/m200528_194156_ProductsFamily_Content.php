<?php

use yii\db\Migration;

/**
 * ./yii migrate --migrationPath=@app/modules/insights/migrations  --interactive=0
 * Class m200528_194156_ProductsFamily_Content
 */
class m200528_194156_ProductsFamily_Content extends Migration
{

    
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_products_family_content}}',[
            'id'              => $this->primaryKey(),
            'contentId'       => $this->integer()->notNull(),
            'serieId'        => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

         // creates index for column `contentId`
         $this->createIndex(
            'idx-products_family_content_w_content',
            'w_products_family_content',
            'contentId'
        );

        // add foreign key for table `w_content`
        $this->addForeignKey(
            'fk-products_family_content_w_content',
            'w_products_family_content',
            'contentId',
            'w_content',
            'id',
            'CASCADE',
            'CASCADE'
            
        );

        // creates index for column `serieId`
        $this->createIndex(
            'idx-products_family_content_w_products_family',
            'w_products_family_content',
            'serieId'
        );

        // add foreign key for table `products_family`
        $this->addForeignKey(
            'fk-products_family_content_w_products_family',
            'w_products_family_content',
            'serieId',
            'products_series',
            'id'
            
        );


    }

    public function down()
    {
        $this->dropTable('{{%w_products_family_content}}');
    }
    
}
