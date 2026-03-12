<!--begin::Sidebar Menu-->
<ul
    class="nav sidebar-menu flex-column"
    data-lte-toggle="treeview"
    role="navigation"
    aria-label="Main navigation"
    data-accordion="false"
    id="navigation"
>

    <li class="nav-item">
        <a href="/" class="nav-link active">
            <i class="nav-icon bi bi-speedometer"></i>
            <p>Дашборд</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-list"></i>
            <p>Заявки</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-text"></i>
            <p>Задачи</p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-text"></i>
            <p>Проблемы</p>
        </a>
    </li>


    <?php if (Yii::$app->user->can('admin')) : ?>

        <li class="nav-header">Администрирование</li>

                <li class="nav-item">
                    <a href="/" class="nav-link ">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Настройки</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/rbac/" class="nav-link ">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>Пользователи</p>
                    </a>
                </li>

    <?php endif; ?>
</ul>
<!--end::Sidebar Menu-->