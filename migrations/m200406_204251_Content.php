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
        $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin ENGINE=InnoDB';
        }

        $this->createTable('{{%w_content}}',[
            'id'              => $this->primaryKey(),
            'type_content_id' => $this->integer()->notNull(),
            'resource_id'     => $this->integer()->notNull(),
            'message'         => $this->string(),
            'permalink'       => $this->string(),
            'image_url'       => $this->string(),
            'timespan'        => $this->integer(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);
        // page facebook
        $this->insert('{{%w_content}}', [
            'type_content_id' => 1,
            'resource_id'     => 5,
            'message'         => 'Facebook Un espacio para las grandes tareas en comunidad',
            'permalink'       => 'https://scontent.fscl18-1.fna.fbcdn.net/v/t1.0-1/cp0/p50x50/11007738_10153308610022248_1355704547189043642_n.png?_nc_cat=108&_nc_sid=dbb9e7&_nc_ohc=V7XGyragdpcAX9GbNJI&_nc_ht=scontent.fscl18-1.fna&oh=b350d8b3ec37926a772c3aa3ab52fcd0&oe=5EB1B686',
            'image_url'       => 'https://scontent.fscl18-1.fna.fbcdn.net/v/t1.0-1/cp0/p50x50/11007738_10153308610022248_1355704547189043642_n.png?_nc_cat=108&_nc_sid=dbb9e7&_nc_ohc=V7XGyragdpcAX9GbNJI&_nc_ht=scontent.fscl18-1.fna&oh=b350d8b3ec37926a772c3aa3ab52fcd0&oe=5EB1B686',
            'timespan'        => '1488153462',
            'createdAt'       => '1488153462',
            'updatedAt'       => '1488153462',
            'createdBy'       => '1',
            'updatedBy'       => '1',
        ]);
        // post instagram
        $this->insert('{{%w_content}}', [
            'type_content_id' => 2,
            'resource_id'     => 6,
            'message'         => 'Aunque ahora estés en casa, puedes seguir compartiendo con las personas que más quieres y hacer lo que disfrutas, a través de la tecnología. 🙌🏼😃 Desliza y descubre nuestros tips  #QuédateEnCasa. ❤️',
            'permalink'       => 'https://www.instagram.com/p/B-pVNECJZ2j/',
            'image_url'       => 'https://www.instagram.com/p/B-pVNECJZ2j/',
            'timespan'        => '1488153462',
            'createdAt'       => '1488153462',
            'updatedAt'       => '1488153462',
            'createdBy'       => '1',
            'updatedBy'       => '1',
        ]);

        // story instagram
        $this->insert('{{%w_content}}', [
            'type_content_id' => 3,
            'resource_id'     => 6,
            'message'         => 'some title',
            'permalink'       => 'some url',
            'image_url'       => 'some url',
            'timespan'        => '1488153462',
            'createdAt'       => '1488153462',
            'updatedAt'       => '1488153462',
            'createdBy'       => '1',
            'updatedBy'       => '1',
        ]);

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
