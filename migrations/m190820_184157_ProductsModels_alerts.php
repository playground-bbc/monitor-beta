<?php

use yii\db\Migration;

/**
 * Class m190820_184157_ProductsModels_alerts
 */
class m190820_184157_ProductsModels_alerts extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%products_models_alerts}}', [
            'id'              => $this->primaryKey(),
            'alertId'         => $this->integer()->notNull(),
            'product_modelId' => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),
        ]);

        $this->insert('{{%products_models_alerts}}', [
            'alertId'         => 1,
            'product_modelId' => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => '1',
            'updatedBy'       => '1',
        ]);

        $this->insert('{{%products_models_alerts}}', [
            'alertId'         => 1,
            'product_modelId' => 2,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => '1',
            'updatedBy'       => '1',
        ]);


        // creates index for column `idAlert`
        $this->createIndex(
            'idx-products_models_alerts-alerts',
            'products_models_alerts',
            'alertId'
        );

        // add foreign key for table `{{%Alerts}}`
        $this->addForeignKey(
            'fk-products_models_alerts-alerts',
            'products_models_alerts',
            'alertId',
            'alerts',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // creates index for column `product_modelId`
        $this->createIndex(
            'idx-products_models_model-products_models',
            'products_models_alerts',
            'product_modelId'
        );

        // add foreign key for table `{{%products_models}}`
        $this->addForeignKey(
            'fk-products_models_model-products_models',
            'products_models_alerts',
            'product_modelId',
            'products_models',
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
        $this->dropTable('{{%products_models_alerts}}');
    }    
}
