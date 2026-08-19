<?php

use yii\helpers\Html;

?>
<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="/" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-block"><a href="/" class="nav-link"><?= Yii::t('app', 'My Requests') ?> </a></li>
            <li class="nav-item d-none d-md-block"><a href="/" class="nav-link"><?= Yii::t('app', 'My Something Else') ?></a></li>

        </ul>
        <!--begin::End Navbar Links-->
        <ul class="navbar-nav ms-auto">
            <!--begin::Navbar Search-->
            <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="bi bi-search"></i>
                </a>
            </li>
            <!--end::Navbar Search-->
            <!--begin::Messages Dropdown Menu-->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-chat-text"></i>
                    <span class="navbar-badge badge text-bg-danger">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <a href="#" class="dropdown-item">
                        <!--begin::Message-->
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img
                                        src="/img/user1-128x128.jpg"
                                        alt="User Avatar"
                                        class="img-size-50 rounded-circle me-3"
                                />
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="dropdown-item-title">
                                    Brad Diesel
                                    <span class="float-end fs-7 text-danger"
                                    ><i class="bi bi-star-fill"></i
                                        ></span>
                                </h3>
                                <p class="fs-7">Call me whenever you can...</p>
                                <p class="fs-7 text-secondary">
                                    <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                </p>
                            </div>
                        </div>
                        <!--end::Message-->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!--begin::Message-->
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img
                                        src="/img/user8-128x128.jpg"
                                        alt="User Avatar"
                                        class="img-size-50 rounded-circle me-3"
                                />
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="dropdown-item-title">
                                    John Pierce
                                    <span class="float-end fs-7 text-secondary">
                          <i class="bi bi-star-fill"></i>
                        </span>
                                </h3>
                                <p class="fs-7">I got your message bro</p>
                                <p class="fs-7 text-secondary">
                                    <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                </p>
                            </div>
                        </div>
                        <!--end::Message-->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <!--begin::Message-->
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img
                                        src="/img/user3-128x128.jpg"
                                        alt="User Avatar"
                                        class="img-size-50 rounded-circle me-3"
                                />
                            </div>
                            <div class="flex-grow-1">
                                <h3 class="dropdown-item-title">
                                    Nora Silvester
                                    <span class="float-end fs-7 text-warning">
                          <i class="bi bi-star-fill"></i>
                        </span>
                                </h3>
                                <p class="fs-7">The subject goes here</p>
                                <p class="fs-7 text-secondary">
                                    <i class="bi bi-clock-fill me-1"></i> 4 Hours Ago
                                </p>
                            </div>
                        </div>
                        <!--end::Message-->
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer"><?= Yii::t('app', 'See All Messages') ?></a>
                </div>
            </li>
            <!--end::Messages Dropdown Menu-->
            <!--begin::Notifications Dropdown Menu-->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-bell-fill"></i>
                    <span class="navbar-badge badge text-bg-warning">15</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header"><?= Yii::t('app', 'Notifications') ?></span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-envelope me-2"></i> 4 <?= Yii::t('app', 'new messages') ?>
                        <span class="float-end text-secondary fs-7">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-people-fill me-2"></i> 8 <?= Yii::t('app', 'friend requests') ?>
                        <span class="float-end text-secondary fs-7">12 hours</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-file-earmark-fill me-2"></i> 3 <?= Yii::t('app', 'new reports') ?>
                        <span class="float-end text-secondary fs-7">2 days</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer"> <?= Yii::t('app', 'See All Notifications') ?> </a>
                </div>
            </li>
            <!--end::Notifications Dropdown Menu-->
            <!--begin::Fullscreen Toggle-->
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>
            <!--end::Fullscreen Toggle-->
            <!--begin::Language Menu Dropdown-->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-globe"></i>
                    <span class="d-none d-md-inline"><?= Yii::t('app', Yii::$app->language === 'en-US' ? 'English' : 'Russian') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <?= \yii\helpers\Html::a(
                            '<i class="bi bi-flag"></i> ' . Yii::t('app', 'Russian'),
                            ['/site/set-language'],
                            [
                                'class' => 'dropdown-item' . (Yii::$app->language === 'ru-RU' ? ' active' : ''),
                                'data' => ['method' => 'post'],
                                'data-language' => 'ru-RU',
                            ]
                        ) ?>
                    </li>
                    <li>
                        <?= \yii\helpers\Html::a(
                            '<i class="bi bi-flag"></i> ' . Yii::t('app', 'English'),
                            ['/site/set-language'],
                            [
                                'class' => 'dropdown-item' . (Yii::$app->language === 'en-US' ? ' active' : ''),
                                'data' => ['method' => 'post'],
                                'data-language' => 'en-US',
                            ]
                        ) ?>
                    </li>
                </ul>
            </li>
            <!--end::Language Menu Dropdown-->
            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img
                            src="/img/user2-160x160.jpg"
                            class="user-image rounded-circle shadow"
                            alt="User Image"
                    />
                    <span class="d-none d-md-inline"><?= YII::$app->user->identity->login?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!--begin::User Image-->
                    <li class="user-header text-bg-primary">
                        <img
                                src="/img/user2-160x160.jpg"
                                class="rounded-circle shadow"
                                alt="User Image"
                        />
                        <p>
                            <?= YII::$app->user->identity->login?>
                            <small>Member since Nov. 2023</small>
                        </p>
                    </li>
                    <!--end::User Image-->
                    <!--begin::Menu Body-->
                    <li class="user-body">
                        <!--begin::Row-->
                        <div class="row">
                            <div class="col-4 text-center"><a href="#"><?= Yii::t('app', 'Followers') ?></a></div>
                            <div class="col-4 text-center"><a href="#"><?= Yii::t('app', 'Sales') ?></a></div>
                            <div class="col-4 text-center"><a href="#"><?= Yii::t('app', 'Friends') ?></a></div>
                        </div>
                        <!--end::Row-->
                    </li>
                    <!--end::Menu Body-->
                    <!--begin::Menu Footer-->
                    <li class="user-footer">
                        <a href="#" class="btn btn-default btn-flat"><?= Yii::t('app', 'Profile') ?></a>
                        <!-- Или через Html::a с data-method -->
                        <?= Html::a(Yii::t('app', 'Sign out'), ['/site/logout'], ['data' => ['method' => 'post'], 'class' => 'btn btn-default btn-flat float-end"']) ?>
<!--                        <a href="#" class="btn btn-default btn-flat float-end">Sign out</a>-->
                    </li>
                    <!--end::Menu Footer-->
                </ul>
            </li>
            <!--end::User Menu Dropdown-->
        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
<!--end::Header-->