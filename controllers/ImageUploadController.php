<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\BadRequestHttpException;

/**
 * Controller для загрузки изображений через Quill.js
 */
class ImageUploadController extends Controller
{
    /**
     * Загрузка изображения для Quill.js редактора
     * @return string JSON ответ с URL загруженного изображения
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';
        
        // Проверяем, что запрос POST
        if (!Yii::$app->request->isPost) {
            return ['error' => 'Только POST запросы разрешены'];
        }
        
        // Получаем файл из запроса
        $file = UploadedFile::getInstanceByName('image');
        
        if (!$file) {
            return ['error' => 'Файл не найден'];
        }
        
        // Проверка типа файла
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->extension);
        
        if (!in_array($extension, $allowedExtensions)) {
            return ['error' => 'Недопустимый тип файла. Разрешены: ' . implode(', ', $allowedExtensions)];
        }
        
        // Проверка размера файла (максимум 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file->size > $maxSize) {
            return ['error' => 'Файл слишком большой. Максимальный размер: 5MB'];
        }
        
        // Создаем директорию для загрузки, если она не существует
        $uploadPath = Yii::getAlias('@webroot/uploads/images');
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        // Генерируем уникальное имя файла
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $filePath = $uploadPath . '/' . $fileName;
        
        // Сохраняем файл
        if ($file->saveAs($filePath)) {
            // Возвращаем URL к файлу
            $imageUrl = Yii::getAlias('@web/uploads/images/' . $fileName);
            return [
                'success' => true,
                'url' => $imageUrl,
                'fileName' => $fileName
            ];
        } else {
            return ['error' => 'Ошибка при сохранении файла'];
        }
    }
}
