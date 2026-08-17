<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель организации
 *
 * @property int $id
 * @property string $name Название организации
 * @property string|null $inn ИНН организации
 * @property string|null $kpp КПП организации
 * @property string|null $ogrn ОГРН организации
 * @property string|null $legal_address Юридический адрес
 * @property string|null $actual_address Фактический адрес
 * @property string|null $phone Телефон
 * @property string|null $email Email
 * @property string|null $website Сайт
 * @property string|null $director_name ФИО директора
 * @property int $status Статус (1 - активна, 0 - неактивна)
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 */
class Organization extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%organizations}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'email', 'website', 'director_name'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 12],
            [['kpp'], 'string', 'max' => 9],
            [['ogrn'], 'string', 'max' => 15],
            [['phone'], 'string', 'max' => 20],
            [['legal_address', 'actual_address'], 'safe'],
            [['status'], 'integer'],
            [['created_by', 'updated_by'], 'integer'],
            [['inn'], 'unique', 'message' => 'Организация с таким ИНН уже существует'],
            [['email'], 'email'],
            [['website'], 'url', 'defaultScheme' => 'https'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название организации',
            'inn' => 'ИНН',
            'kpp' => 'КПП',
            'ogrn' => 'ОГРН',
            'legal_address' => 'Юридический адрес',
            'actual_address' => 'Фактический адрес',
            'phone' => 'Телефон',
            'email' => 'Email',
            'website' => 'Сайт',
            'director_name' => 'ФИО директора',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Создатель',
            'updated_by' => 'Редактор',
        ];
    }

    /**
     * Возвращает пользователя-создателя
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Возвращает пользователя-редактора
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Возвращает статус в виде строки
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->status === self::STATUS_ACTIVE ? 'Активна' : 'Неактивна';
    }

    /**
     * {@inheritdoc}
     * @return OrganizationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrganizationQuery(get_called_class());
    }
}
