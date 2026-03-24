<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\web\IdentityInterface;

/**
 * @param $id
 */
class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    public $password; // временное поле для хранения пароля при создании/изменении
    public $auth_key; // временное поле для хранения пароля при создании/изменении

    /**
     * @return array[]
     */

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'], // если нужно
                ],
                // Опционально: задайте формат значения
                // 'value' => new \yii\db\Expression('NOW()'), // для DATETIME
            ],
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Обрабатываем created_at
            if (!empty($this->created_at)) {
                if (is_numeric($this->created_at)) {
                    $this->created_at = date('Y-m-d H:i:s', (int)$this->created_at);
                }
                // Дополнительно можно проверять другие форматы
            }

            // Аналогично для updated_at, если нужно
            if (!empty($this->updated_at) && is_numeric($this->updated_at)) {
                $this->updated_at = date('Y-m-d H:i:s', (int)$this->updated_at);
            }
            return true;
        }
        return false;
    }
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{users}}';
    }

    /**
     * @param $id
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);

    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $login
     * @return User
     */
    public static function findByLogin(string $login): User
    {
        return static::findOne(['username' => $login]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Устанавливает пароль, хешируя его перед сохранением в БД
     * @param string $password Пароль в открытом виде
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Генерирует случайный ключ аутентификации (для «Запомнить меня»)
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getFormattedCreatedAt()
    {
        return Yii::$app->formatter->asDateTime($this->created_at, 'php:d.m.Y');
    }
}
