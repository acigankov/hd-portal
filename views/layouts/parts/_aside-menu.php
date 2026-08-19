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
            <p><?= Yii::t('app', 'Dashboard') ?></p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-list"></i>
            <p><?= Yii::t('app', 'Requests') ?></p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-text"></i>
            <p><?= Yii::t('app', 'Tasks') ?></p>
        </a>
    </li>
    <li class="nav-item">
        <a href="/" class="nav-link ">
            <i class="nav-icon bi bi-card-text"></i>
            <p><?= Yii::t('app', 'Problems') ?></p>
        </a>
    </li>

    <?php if (Yii::$app->user->can('admin')) : ?>

        <li class="nav-header"><?= Yii::t('app', 'Administration') ?></li>

                <li class="nav-item">
                    <a href="/organization" class="nav-link ">
                        <i class="nav-icon bi bi-building"></i>
                        <p><?= Yii::t('app', 'Organizations') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/employee-group" class="nav-link ">
                        <i class="nav-icon bi bi-people"></i>
                        <p><?= Yii::t('app', 'Employee Groups') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/" class="nav-link ">
                        <i class="nav-icon bi bi-gear"></i>
                        <p><?= Yii::t('app', 'Settings') ?></p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="/rbac/" class="nav-link ">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p><?= Yii::t('app', 'Users') ?></p>
                    </a>
                </li>

    <?php endif; ?>
</ul>
<!--end::Sidebar Menu-->