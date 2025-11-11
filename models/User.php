<?php

namespace app\models;

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
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
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
}
