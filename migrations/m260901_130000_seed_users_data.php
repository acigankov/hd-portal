<?php

use yii\db\Migration;
use yii\base\Security;

/**
 * Добавление 10 пользователей: 1 админ и 9 операторов.
 *
 * Пароли НЕ хранятся в коде. Для каждого пользователя генерируется
 * случайный пароль, который один раз печатается в вывод миграции —
 * его нужно сохранить в менеджере паролей и сменить при первом входе.
 * Единый пароль для всех можно задать переменной SEED_DEFAULT_PASSWORD
 * (только для локальной разработки).
 */
class m260901_130000_seed_users_data extends Migration
{
    public function safeUp()
    {
        $security = new Security();
        
        $users = [
            // Администратор
            [
                'login' => 'a.volkov',
                'name' => 'Александр Волков',
                'role' => 'admin',
                'email' => 'alexandr.volkov@company.local',
                'status' => 1,
            ],
            // Операторы
            [
                'login' => 'e.smirnova',
                'name' => 'Елена Смирнова',
                'role' => 'operator',
                'email' => 'elena.smirnova@company.local',
                'status' => 1,
            ],
            [
                'login' => 'd.kuznetsov',
                'name' => 'Дмитрий Кузнецов',
                'role' => 'operator',
                'email' => 'dmitry.kuznetsov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'o.popova',
                'name' => 'Ольга Попова',
                'role' => 'operator',
                'email' => 'olga.popova@company.local',
                'status' => 1,
            ],
            [
                'login' => 'm.sokolov',
                'name' => 'Максим Соколов',
                'role' => 'operator',
                'email' => 'maxim.sokolov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'n.lebedeva',
                'name' => 'Наталья Лебедева',
                'role' => 'operator',
                'email' => 'natalia.lebedeva@company.local',
                'status' => 1,
            ],
            [
                'login' => 'i.kozlov',
                'name' => 'Иван Козлов',
                'role' => 'operator',
                'email' => 'ivan.kozlov@company.local',
                'status' => 1,
            ],
            [
                'login' => 'a.novikova',
                'name' => 'Анна Новикова',
                'role' => 'operator',
                'email' => 'anna.novikova@company.local',
                'status' => 1,
            ],
            [
                'login' => 's.morozov',
                'name' => 'Сергей Морозов',
                'role' => 'operator',
                'email' => 'sergey.morozov@company.local',
                'status' => 1,
            ],
            [
                'login' => 't.fedorova',
                'name' => 'Татьяна Федорова',
                'role' => 'operator',
                'email' => 'tatiana.fedorova@company.local',
                'status' => 1,
            ],
        ];

        $credentials = [];

        foreach ($users as $user) {
            // Пароль берётся из окружения (локальная разработка) либо генерируется случайно
            $password = $_ENV['SEED_DEFAULT_PASSWORD'] ?? $security->generateRandomString(12);
            $credentials[$user['login']] = $password;

            $this->insert('{{%users}}', [
                'login' => $user['login'],
                'name' => $user['name'],
                'password_hash' => $security->generatePasswordHash($password),
                'email' => $user['email'],
                'status' => $user['status'],
                'role' => $user['role'],
                'auth_key' => $security->generateRandomString(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "\nСозданные пользователи (пароли показываются один раз, сохраните их):\n";
        foreach ($credentials as $login => $password) {
            echo sprintf("  %-14s %s\n", $login, $password);
        }
        echo "\n";
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
