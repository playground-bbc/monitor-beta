<?php

use yii\db\Migration;

/**
 * Class m190813_195501_Users
 */
class m190813_195501_Users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->insert('{{%users}}', [
            'username'      => 'admin',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // deathnote
            'password_hash' => '$2y$13$Xv3tYWezdvWV9GRUUv1/8.NEC8CX4fp2MRntK5L0EBJXgwy49IF.K',
            'email'         => 'spiderbbc@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);

        $this->insert('{{%users}}', [
            'username'      => 'mauro',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // mauro123
            'password_hash' => '$2y$13$J2tWG5KBTCC0aCx3EbT5XOjn2nGZ2qF/xCQNJ3UeIcHwHdfdV4QM6',
            'email'         => 'user1@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);

        $this->insert('{{%users}}', [
            'username'      => 'abraham',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // abraham123
            'password_hash' => '$2y$13$q9jvTqfk.qHr8HvNE3t4JOFgMTInkRxFv5oysDlaUNI6ETkY.df8W',
            'email'         => 'user2@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);

        $this->insert('{{%users}}', [
            'username'      => 'johanna',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // johanna123
            'password_hash' => '$2y$13$N/ADNnVF8LIB9CnFPBNgle4c6u8pe7260npSOEVcZOsTuCgUmN8um',
            'email'         => 'user3@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);


        $this->insert('{{%users}}', [
            'username'      => 'dafne',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // dafne123
            'password_hash' => '$2y$13$8ffoD8gftx/eHWAUv0wAl.f7weVMfaO7GhKVH6PafsyC0ARbvrTme',
            'email'         => 'user4@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);

        $this->insert('{{%users}}', [
            'username'      => 'david',
            'auth_key'      => 'tPwo4kDpN7JAz8Rrm9EwNAQ7q8F1p7FN',
            // david123
            'password_hash' => '$2y$13$67Dq3ZJh5Yw6b8a6NyEWnOUtrBqN4E/4QopbBmcKtQOaX1Apjp.pa',
            'email'         => 'user5@gmail.com',
            'status'        => 10,
            'created_at'    => 0,
            'updated_at'    => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190813_201201_Users cannot be reverted.\n";

        return false;
    }
    */
}
