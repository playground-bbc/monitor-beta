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
            'fk-idx-products_models_model-products_models',
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
        // drops foreign key for table `{{%Alerts}}`
        $this->dropForeignKey(
            '{{%fk-products_models_alerts-alerts}}',
            '{{%products_models_alerts}}'
        );

        // drops index for column `idAlert`
        $this->dropIndex(
            '{{%idx-products_models_model-products_models}}',
            '{{%products_models_alerts}}'
        );

        // drops foreign key for table `{{%products_models}}`
        $this->dropForeignKey(
            '{{%fk-idx-products_models_model-products_models}}',
            '{{%products_models_alerts}}'
        );

        // drops index for column `product_modelId`
        $this->dropIndex(
            '{{%idx-products_models_model-products_models}}',
            '{{%products_models_alerts}}'
        );

        $this->dropTable('{{%products_models_alerts}}');
    }    
}
