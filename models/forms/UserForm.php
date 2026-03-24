<?php

namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class UserForm extends Model
{
    public $login;
    public $email;
    public $password;
    public $auth_key;
    public $access_token;

    public function rules()
    {
        return [
            [['login', 'email', 'password'], 'required'],
            ['login', 'string', 'max' => 255],
            ['login', 'unique', 'targetClass' => User::class, 'message' => 'Этот логин уже занят.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['password', 'string', 'min' => 6],
            [['auth_key', 'access_token'], 'string', 'max' => 64],
            [['auth_key', 'access_token'], 'unique'],
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
