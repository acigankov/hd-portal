<?php

use yii\db\Migration;
use yii\base\Security;

/**
 * Добавление 10 пользователей: 1 админ и 9 операторов
 */
class m260820_130000_seed_users_data extends Migration
{
    public function safeUp()
    {
        $security = new Security();
        
        $users = [
            // Администратор
            [
                'login' => 'admin',
                'password' => 'Admin@123',
                'role' => 'admin',
                'email' => 'admin@example.com',
                'status' => 1,
            ],
            // Операторы
            [
                'login' => 'operator1',
                'password' => 'Oper@12345',
                'role' => 'operator',
                'email' => 'operator1@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator2',
                'password' => 'Oper@23456',
                'role' => 'operator',
                'email' => 'operator2@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator3',
                'password' => 'Oper@34567',
                'role' => 'operator',
                'email' => 'operator3@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator4',
                'password' => 'Oper@45678',
                'role' => 'operator',
                'email' => 'operator4@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator5',
                'password' => 'Oper@56789',
                'role' => 'operator',
                'email' => 'operator5@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator6',
                'password' => 'Oper@67890',
                'role' => 'operator',
                'email' => 'operator6@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator7',
                'password' => 'Oper@78901',
                'role' => 'operator',
                'email' => 'operator7@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator8',
                'password' => 'Oper@89012',
                'role' => 'operator',
                'email' => 'operator8@example.com',
                'status' => 1,
            ],
            [
                'login' => 'operator9',
                'password' => 'Oper@90123',
                'role' => 'operator',
                'email' => 'operator9@example.com',
                'status' => 1,
            ],
        ];

        foreach ($users as $user) {
            $this->insert('{{%users}}', [
                'login' => $user['login'],
                'password_hash' => $security->generatePasswordHash($user['password']),
                'email' => $user['email'],
                'status' => $user['status'],
                'role' => $user['role'],
                'auth_key' => $security->generateRandomString(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%users}}', ['role' => 'admin']);
        $this->delete('{{%users}}', ['role' => 'operator']);
    }
}
