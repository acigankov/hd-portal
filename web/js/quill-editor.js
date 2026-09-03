/**
 * Quill.js Editor Initialization
 * Автоматически инициализирует Quill редактор для всех textarea с классом quill-editor
 */

(function() {
    'use strict';
    
    // Флаг, чтобы избежать повторной инициализации
    if (window.quillEditorInitialized) {
        return;
    }
    window.quillEditorInitialized = true;
    
    // Конфигурация Quill по умолчанию
    var defaultOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ]
        },
        placeholder: 'Введите текст...',
        formats: [
            'header', 'bold', 'italic', 'underline', 'strike',
            'list', 'bullet', 'ordered',
            'color', 'background',
            'align',
            'link', 'image'
        ]
    };
    
    /**
     * Инициализация Quill редактора для textarea
     * @param {HTMLElement} textarea - элемент textarea
     * @param {Object} options - опции Quill
     */
    function initQuillEditor(textarea, options) {
        if (!textarea || !textarea.id) {
            return;
        }
        
        // Проверяем, не был ли уже инициализирован редактор
        if (textarea.nextElementSibling && 
            textarea.nextElementSibling.classList.contains('ql-container')) {
            return;
        }
        
        var containerId = textarea.id + '-quill';
        var container = document.createElement('div');
        container.id = containerId;
        
        // Скрываем оригинальную textarea
        textarea.style.display = 'none';
        
        // Вставляем контейнер после textarea
        textarea.parentNode.insertBefore(container, textarea.nextSibling);
        
        // Создаем экземпляр Quill
        var quillOptions = Object.assign({}, defaultOptions, options || {});
        var quill = new Quill('#' + containerId, quillOptions);
        
        // Синхронизируем содержимое Quill с textarea
        quill.on('text-change', function() {
            textarea.value = quill.root.innerHTML;
        });
        
        // Устанавливаем начальное значение
        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }
        
        // Сохраняем ссылку на экземпляр Quill в элементе textarea
        textarea.quillEditor = quill;
        
        console.log('Quill editor initialized for:', textarea.id);
    }
    
    /**
     * Инициализация всех редакторов на странице
     */
    function initAllEditors() {
        // Находим все textarea с классом quill-editor или data-quill атрибутами
        var textareas = document.querySelectorAll('textarea.quill-editor, textarea[data-quill]');
        
        textareas.forEach(function(textarea) {
            var options = {};
            
            // Получаем опции из data-атрибутов
            if (textarea.dataset.quillOptions) {
                try {
                    options = JSON.parse(textarea.dataset.quillOptions);
                } catch (e) {
                    console.warn('Invalid quill-options for textarea:', textarea.id);
                }
            }
            
            initQuillEditor(textarea, options);
        });
    }
    
    /**
     * Получить экземпляр Quill для textarea
     * @param {string|HTMLElement} element - ID или элемент textarea
     * @returns {Quill|null}
     */
    function getEditor(element) {
        var textarea = typeof element === 'string' ? document.getElementById(element) : element;
        return textarea && textarea.quillEditor ? textarea.quillEditor : null;
    }
    
    /**
     * Обновить содержимое редактора
     * @param {string|HTMLElement} element - ID или элемент textarea
     * @param {string} html - HTML содержимое
     */
    function setEditorContent(element, html) {
        var quill = getEditor(element);
        if (quill) {
            quill.root.innerHTML = html;
        } else {
            var textarea = typeof element === 'string' ? document.getElementById(element) : element;
            if (textarea) {
                textarea.value = html;
            }
        }
    }
    
    /**
     * Получить содержимое редактора
     * @param {string|HTMLElement} element - ID или элемент textarea
     * @returns {string}
     */
    function getEditorContent(element) {
        var quill = getEditor(element);
        if (quill) {
            return quill.root.innerHTML;
        } else {
            var textarea = typeof element === 'string' ? document.getElementById(element) : element;
            return textarea ? textarea.value : '';
        }
    }
    
    // Экспортируем функции в глобальную область видимости
    window.QuillEditor = {
        init: initQuillEditor,
        initAll: initAllEditors,
        get: getEditor,
        setContent: setEditorContent,
        getContent: getEditorContent
    };
    
    // Автоматическая инициализация после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllEditors);
    } else {
        initAllEditors();
    }
    
    // Также инициализируем при загрузке через PJAX или AJAX
    document.addEventListener('DOMNodeInserted', function(e) {
        if (e.target.tagName === 'TEXTAREA' && 
            (e.target.classList.contains('quill-editor') || e.target.hasAttribute('data-quill'))) {
            setTimeout(initAllEditors, 100);
        }
    });
    
})();
