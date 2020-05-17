<?php
use yii\db\Migration;

/**
 * Class m190813_215104_credencials_api
 */
class m190813_215104_Credencials_Api extends Migration
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


        $this->createTable('{{%credencials_api}}',[
            'id'                  => $this->primaryKey(),
            'userId'              => $this->integer()->notNull(),
            'resourceId'          => $this->integer()->notNull(),
            'name_app'            => $this->string(45),
            'api_key'             => $this->string(134),
            'api_secret_key'      => $this->string(220),
            'access_secret_token' => $this->string(220),
            'bearer_token'        => $this->string(305),
            'apiLogin'            => $this->string(220),
            'status'              => $this->integer()->defaultValue(0),
            'expiration_date'     => $this->integer(),
            'createdAt'           => $this->integer(),
            'updatedAt'           => $this->integer(),
            'createdBy'           => $this->integer(),
            'updatedBy'           => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 1,
            'name_app'            => 'monitor-twitter',
            'api_key'             => 'jaBukwgz5AticPEL78bU2sntR',
            'api_secret_key'      => 'oti77aVehVd1gPTfWf7k0rJGEB1yhBgyc6qrCkA3pnospfffoi',
            'access_secret_token' => 'TTdlPqtbByToHaReoou7LBSOAYPa4uS7WQKqn3xx',
            'bearer_token'        => '',
            'expiration_date'     => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 2,
            'name_app'            => 'monitor-livechat',
            'api_key'             => 'b1f9cbda5e332b55d03fa2a5deb4c037',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'expiration_date'     => 'encrycpt here',
            'apiLogin'            => 'eduardo@montana-studio.com',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        
/*
        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 3,
            'name_app'            => 'monitor-livechat-conversations',
            'api_key'             => 'encrycpt here',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'expiration_date'     => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);*/

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 5,
            'name_app'            => 'monitor-facebook',
            'api_key'             => 'encrycpt here',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'expiration_date'     => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

        /*$this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 3,
            'name_app'            => 'monitor-instagram',
            'api_key'             => 'encrycpt here',
            'api_secret_key'      => 'encrycpt here',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'expiration_date'     => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);*/

        $this->insert('{{%credencials_api}}', [
            'userId'              => 1,
            'resourceId'          => 9,
            'name_app'            => 'monitor-drive',
            'api_key'             => 'monitor-beta-lg',
            'api_secret_key'      => '1LBf9kTwPswIQuvNx0xH8RiMBZiXNZeBGi_QjTrHVwAc',
            'access_secret_token' => 'encrycpt here',
            'bearer_token'        => 'encrycpt here',
            'apiLogin'            => 'encrycpt here',
            'createdAt'           => '1488153462',
            'updatedAt'           => '1488153462',
            'createdBy'           => '1',
            'updatedBy'           => '1',
        ]);

         // creates index for column `userId`
        $this->createIndex(
            'idx-credencial_api_userId',
            'credencials_api',
            'userId'
        );

        $this->addForeignKey(
            'credencial_api_userId',
            'credencials_api',
            'userId',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );


         // creates index for column `resourceId`
        $this->createIndex(
            'idx-credencial_api_resourceId',
            'credencials_api',
            'resourceId'
        );

        $this->addForeignKey(
            'credencial_api_resourceId',
            'credencials_api',
            'resourceId',
            'resources',
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
        $this->dropTable('{{%credencials_api}}');
    }

}
