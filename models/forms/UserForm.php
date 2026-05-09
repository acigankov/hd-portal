<?php

namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class UserForm extends Model
{
    public $login;
    public $email;
    public $password;
    public $status;
    public $auth_key;
    public $access_token;

    private $userId; // ID текущего пользователя для исключения из проверки

    public function rules()
    {
        return [
            [['login', 'email', 'password'], 'required'],
            ['login', 'string', 'max' => 255],
            ['login', 'unique', 'targetClass' => User::class, 'message' => 'Этот логин уже занят.', 'on' => 'create'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            // Исключаем текущего пользователя из проверки уникальности
            ['login', 'unique',
                'targetClass' => \app\models\User::class,
                'targetAttribute' => 'login',
                'filter' => ['!=', 'id', $this->userId],
                'message' => 'Этот логин уже занят',
                'on' => 'update'
            ],
            ['status', 'in', 'range' => [0, 1]],
            ['password', 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 6, 'on' => 'create'],
            //[['auth_key', 'access_token'], 'string', 'max' => 64],
            //[['auth_key', 'access_token'], 'unique'],

        ];
    }

    public function attributeLabels()
    {
        return [
            'login' => 'Логин пользователя',
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'create' => ['login', 'email', 'password', 'status'],
            'update' => ['login', 'email', 'status'],
        ];
    }

    /**
     * Заполняет форму данными из пользователя
     */
    public function loadFromUser($user)
    {
        $this->userId = $user->id; // Сохраняем ID для исключения из проверки
        $this->login = $user->login;
        $this->email = $user->email;
        $this->status = $user->status;
        // password, auth_key, access_token не загружаем
    }

    /**
     * Сохраняет данные формы в пользователя
     */
    public function saveToUser($user)
    {
        $user->login = $this->login;
        $user->email = $this->email;
        $user->status = $this->status;

        // Пароль обновляем только если он был заполнен
        if (!empty($this->password)) {
            $user->setPassword($this->password);
        }

        return $user->save();
    }

    /**
     * Сохраняет пользователя в базу данных
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new User();
        $user->login = $this->login;
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateAccessToken();
        return $user->save();
    }
}
