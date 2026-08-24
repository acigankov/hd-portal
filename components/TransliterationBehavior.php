<?php

namespace app\components;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\base\Event;

/**
 * TransliterationBehavior автоматически транслитерирует русские символы в латиницу
 * для указанного атрибута модели при изменении другого атрибута (например, name -> code).
 *
 * Пример использования в модели:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'translit' => [
 *             'class' => TransliterationBehavior::class,
 *             'sourceAttribute' => 'name',
 *             'targetAttribute' => 'code',
 *             'onlyOnNewRecords' => false, // применять только к новым записям
 *         ],
 *     ];
 * }
 * ```
 */
class TransliterationBehavior extends Behavior
{
    /**
     * @var string имя атрибута-источника (например, 'name')
     */
    public $sourceAttribute;

    /**
     * @var string имя атрибута-цели для транслитерации (например, 'code')
     */
    public $targetAttribute;

    /**
     * @var bool применять только к новым записям (при создании)
     * Если true, то поле заполняется автоматически только при создании.
     * Если false, то поле заполняется при каждом изменении sourceAttribute.
     */
    public $onlyOnNewRecords = true;

    /**
     * @var bool разрешить ручное редактирование целевого поля
     * Если true, то автозаполнение происходит только если целевое поле пустое
     * или если изменилось исходное поле.
     */
    public $allowManualEdit = true;

    /**
     * @var array карта символов для транслитерации
     */
    private $translitMap = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    ];

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    /**
     * Обработчик события перед сохранением
     * @param Event $event
     */
    public function beforeSave($event)
    {
        $model = $this->owner;
        
        // Проверяем, является ли модель новой записью
        $isNewRecord = $model->getIsNewRecord();
        
        // Если onlyOnNewRecords = true, обрабатываем только новые записи
        if ($this->onlyOnNewRecords && !$isNewRecord) {
            return;
        }

        // Получаем значения атрибутов
        $sourceValue = $model->{$this->sourceAttribute};
        $targetValue = $model->{$this->targetAttribute};

        // Если allowManualEdit = true, заполняем только если целевое поле пустое
        // или если это новая запись
        if ($this->allowManualEdit) {
            if (!$isNewRecord && !empty($targetValue)) {
                // Поле уже заполнено и это не новая запись - не трогаем
                return;
            }
        }

        // Если исходное поле пустое, ничего не делаем
        if (empty($sourceValue)) {
            return;
        }

        // Транслитерируем и устанавливаем значение
        $model->{$this->targetAttribute} = $this->transliterate($sourceValue);
    }

    /**
     * Транслитерирует строку из кириллицы в латиницу
     * @param string $string исходная строка
     * @return string транслитерированная строка
     */
    public function transliterate($string)
    {
        // Применяем карту транслитерации
        $result = strtr($string, $this->translitMap);
        
        // Заменяем все не alphanumeric символы на дефис или удаляем
        $result = preg_replace('/[^a-zA-Z0-9_-]/', '-', $result);
        
        // Удаляем множественные дефисы
        $result = preg_replace('/-+/', '-', $result);
        
        // Удаляем дефисы в начале и конце
        $result = trim($result, '-');
        
        // Приводим к нижнему регистру
        $result = strtolower($result);
        
        return $result;
    }
}
