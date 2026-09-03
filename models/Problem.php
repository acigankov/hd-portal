<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель проблемы
 *
 * @property int $id
 * @property string $problem_number Номер проблемы в формате prb#000001
 * @property string $title Название проблемы
 * @property string|null $description Описание проблемы
 * @property string|null $jira_ticket Ссылка на Jira тикет
 * @property int|null $category_id ID категории
 * @property int|null $status_id ID статуса
 * @property int|null $author_id ID автора проблемы
 * @property int|null $responsible_id ID ответственного
 * @property int $priority Приоритет (1-низкий, 2-средний, 3-высокий)
 * @property string|null $due_date Срок выполнения
 * @property int $status Статус записи (1 - активен, 0 - неактивен)
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 * 
 * @property Category $category
 * @property Status $status
 * @property Author $author
 * @property User $responsible
 * @property Ticket[] $tickets Привязанные заявки
 */
class Problem extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%problems}}';
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
            [['title'], 'required'],
            [['title', 'jira_ticket'], 'string', 'max' => 255],
            [['description'], 'safe'],
            [['category_id', 'status_id', 'author_id', 'responsible_id', 'priority', 'status', 'created_by', 'updated_by'], 'integer'],
            [['due_date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['problem_number'], 'string', 'max' => 20],
            [['problem_number'], 'unique', 'message' => 'Проблема с таким номером уже существует'],
            [['priority'], 'in', 'range' => [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH]],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'problem_number' => Yii::t('app', 'Problem Number'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'jira_ticket' => Yii::t('app', 'Jira Ticket'),
            'category_id' => Yii::t('app', 'Category'),
            'status_id' => Yii::t('app', 'Status'),
            'author_id' => Yii::t('app', 'Author'),
            'responsible_id' => Yii::t('app', 'Responsible'),
            'priority' => Yii::t('app', 'Priority'),
            'due_date' => Yii::t('app', 'Due Date'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Перед сохранением генерируем номер проблемы
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert && empty($this->problem_number)) {
                $this->problem_number = $this->generateProblemNumber();
            }
            return true;
        }
        return false;
    }

    /**
     * Генерирует номер проблемы в формате prb#000001
     * @return string
     */
    private function generateProblemNumber()
    {
        $lastProblem = self::find()->orderBy(['id' => SORT_DESC])->one();
        $nextNumber = 1;
        
        if ($lastProblem !== null && !empty($lastProblem->problem_number)) {
            // Извлекаем числовую часть из последнего номера
            $lastNumber = (int)substr($lastProblem->problem_number, 4);
            $nextNumber = $lastNumber + 1;
        }
        
        return 'prb#' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Возвращает категорию
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Возвращает статус
     * @return Status
     */
    public function getStatusById($id)
    {
        return Status::findOne(['id' => $id ]);
    }


    /**
     * Возвращает автора
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Возвращает ответственного
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(User::class, ['id' => 'responsible_id']);
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
     * Возвращает привязанные заявки
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['id' => 'ticket_id'])
            ->viaTable('{{%problem_tickets}}', ['problem_id' => 'id']);
    }

    /**
     * Возвращает метку приоритета
     * @return string
     */
    public function getPriorityLabel()
    {
        switch ($this->priority) {
            case self::PRIORITY_HIGH:
                return Yii::t('app', 'High');
            case self::PRIORITY_MEDIUM:
                return Yii::t('app', 'Medium');
            case self::PRIORITY_LOW:
            default:
                return Yii::t('app', 'Low');
        }
    }

    /**
     * Возвращает цвет для приоритета
     * @return string
     */
    public function getPriorityColor()
    {
        switch ($this->priority) {
            case self::PRIORITY_HIGH:
                return 'danger';
            case self::PRIORITY_MEDIUM:
                return 'warning';
            case self::PRIORITY_LOW:
            default:
                return 'info';
        }
    }

    /**
     * {@inheritdoc}
     * @return ProblemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProblemQuery(get_called_class());
    }
}
