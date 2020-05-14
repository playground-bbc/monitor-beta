<?php

use yii\db\Migration;

/**
 * Class m190813_202212_Alerts_Resources
 */
class m190813_202212_Resources extends Migration
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

        $this->createTable('{{%resources}}', [
            'id'          => $this->primaryKey(),
            'resourcesId' => $this->integer()->notNull(),
            'name'        => $this->string(40)->notNull(),
            'createdAt'   => $this->integer(),
            'updatedAt'   => $this->integer(),
            'createdBy'   => $this->integer(),
            'updatedBy'   => $this->integer(),

        ], $tableOptions);
        // 1
        $this->insert('{{%resources}}', [
            'name'        => 'Twitter',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

        // 2
        $this->insert('{{%resources}}', [
            'name'        => 'Live Chat',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        // 3
        $this->insert('{{%resources}}', [
            'name'        => 'Live Chat Conversations',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        
        // 4
        $this->insert('{{%resources}}', [
            'name'        => 'Paginas Webs',
            'resourcesId' => '2',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        // 5
        $this->insert('{{%resources}}', [
            'name'        => 'Facebook Comments',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        // 6
        $this->insert('{{%resources}}', [
            'name'        => 'Instagram Comments',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        // 7
        $this->insert('{{%resources}}', [
            'name'        => 'Facebook Messages',
            'resourcesId' => '1',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

        // 8
        $this->insert('{{%resources}}', [
            'name'        => 'Excel Document',
            'resourcesId' => '3',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);
        // 9
        $this->insert('{{%resources}}', [
            'name'        => 'Excel Document Drive',
            'resourcesId' => '4',
            'createdAt'   => '1488153462',
            'updatedAt'   => '1488153462',
            'createdBy'   => '1',
            'updatedBy'   => '1',
        ]);

        // creates index for column `resourcesId`
        $this->createIndex(
            'idx-resources-type_resources_resourcesId',
            'resources',
            'resourcesId'
        );

        // add foreign key for table `type_resources`
        $this->addForeignKey(
            'fk-resources-type_resources_resourcesId',
            'resources',
            'resourcesId',
            'type_resources',
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
        $this->dropTable('{{%resources}}');
    }

}
