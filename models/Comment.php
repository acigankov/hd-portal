<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель комментария
 *
 * @property int $id
 * @property string $entity_type Тип сущности (task, ticket, issue)
 * @property int $entity_id ID сущности
 * @property string $text Текст комментария
 * @property int|null $parent_id ID родительского комментария
 * @property int $author_id ID автора комментария
 * @property int $is_edited Флаг редактирования
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * 
 * @property User $author
 * @property Comment $parent
 * @property Comment[] $replies
 */
class Comment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%comments}}';
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
            [['entity_type', 'entity_id', 'text', 'author_id'], 'required'],
            [['entity_type'], 'string', 'max' => 50],
            [['entity_id', 'parent_id', 'author_id'], 'integer'],
            [['text'], 'safe'],
            [['is_edited'], 'boolean'],
            [['entity_type'], 'in', 'range' => ['task', 'ticket', 'issue']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'entity_type' => Yii::t('app', 'Entity Type'),
            'entity_id' => Yii::t('app', 'Entity ID'),
            'text' => Yii::t('app', 'Text'),
            'parent_id' => Yii::t('app', 'Parent Comment'),
            'author_id' => Yii::t('app', 'Author'),
            'is_edited' => Yii::t('app', 'Is Edited'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * Возвращает автора комментария
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Возвращает родительский комментарий
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_id']);
    }

    /**
     * Возвращает ответы на комментарий
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Проверяет, может ли текущий пользователь редактировать комментарий
     * @return bool
     */
    public function canEdit()
    {
        return !Yii::$app->user->isGuest && Yii::$app->user->id == $this->author_id;
    }

    /**
     * Проверяет, может ли текущий пользователь удалять комментарий
     * @return bool
     */
    public function canDelete()
    {
        return !Yii::$app->user->isGuest && (Yii::$app->user->id == $this->author_id || Yii::$app->user->can('admin'));
    }

    /**
     * {@inheritdoc}
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
