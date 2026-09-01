<?php

use yii\db\Migration;
use yii\base\Security;

/**
 * Добавление 10 пользователей: 1 админ и 9 операторов
 * Данные максимально приближены к реальности
 */
class m260820_130000_seed_users_data extends Migration
{
    public function safeUp()
    {
        $security = new Security();
        
        $users = [
            // Администратор
            [
                'login' => 'a.volkov',
                'password' => 'Volkov@2024',
                'role' => 'admin',
                'email' => 'alexandr.volkov@company.local',
                'status' => 1,
            ],
            // Операторы
            [
                'login' => 'e.smirnova',
                'password' => 'Smirnova@2024',
                'role' => 'operator',
                'email' => 'elena.smirnova@company.local',
                'status' => 1,
            ],
            [
                'login' => 'd.kuznetsov',
                'password' => 'Kuznetsov@2024',
                'role' => 'operator',
                'email' => 'dmitry.kuznetsov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'o.popova',
                'password' => 'Popova@2024',
                'role' => 'operator',
                'email' => 'olga.popova@company.local',
                'status' => 1,
            ],
            [
                'login' => 'm.sokolov',
                'password' => 'Sokolov@2024',
                'role' => 'operator',
                'email' => 'maxim.sokolov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'n.lebedeva',
                'password' => 'Lebedeva@2024',
                'role' => 'operator',
                'email' => 'natalia.lebedeva@company.local',
                'status' => 1,
            ],
            [
                'login' => 'i.kozlov',
                'password' => 'Kozlov@2024',
                'role' => 'operator',
                'email' => 'ivan.kozlov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'a.novikova',
                'password' => 'Novikova@2024',
                'role' => 'operator',
                'email' => 'anna.novikova@company.local',
                'status' => 1,
            ],
            [
                'login' => 's.morozov',
                'password' => 'Morozov@2024',
                'role' => 'operator',
                'email' => 'sergey.morozov@company.local',
                'status' => 1,
            ],
            [
                'login' => 't.fedorova',
                'password' => 'Fedorova@2024',
                'role' => 'operator',
                'email' => 'tatiana.fedorova@company.local',
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
        $logins = [
            'a.volkov', 'e.smirnova', 'd.kuznetsov', 'o.popova', 'm.sokolov',
            'n.lebedeva', 'i.kozlov', 'a.novikova', 's.morozov', 't.fedorova'
        ];
        $this->delete('{{%users}}', ['login' => $logins]);
    }
}
