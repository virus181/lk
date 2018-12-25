<?php

use app\models\User;
use yii\db\Migration;

class m170221_084749_add_root_user extends Migration
{
    public function safeUp()
    {
        if (User::findByEmail('79637692220@ya.ru') === null) {
            $email = '79637692220@ya.ru';
            $password = '12345';
            $passwordHash = Yii::$app->security->generatePasswordHash($password);
            $authKey = Yii::$app->security->generateRandomString();

            $this->insert('{{%user}}', [
                'email' => $email,
                'password_hash' => $passwordHash,
                'auth_key' => $authKey,
                'status' => 10,
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $this->insert('{{auth_item}}', [
                'name' => '/*',
                'type' => '1',
                'created_at' => time(),
                'updated_at' => time(),
            ]);

            $this->insert('{{auth_assignment}}', [
                'item_name' => '/*',
                'user_id' => '1',
                'created_at' => time(),
            ]);
        }
    }
}
