<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$baseBath = '@web';

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/img/logo/logo.svg')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode(Yii::$app->params['appName'] . ' | ' . $this->title)?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    <!--begin::Primary Meta Tags-->
    <meta name="title" content="<?= Html::encode(Yii::$app->params['appName'] . ' | ' . $this->title)?>" />
    <meta name="author" content="<?= Html::encode(Yii::$app->params['appAuthor'])?>" />
    <meta name="description" content="<?= Html::encode(Yii::$app->params['appName'])?>" />
    <meta name="keywords" content="<?= Html::encode(Yii::$app->params['appName'])?>" />
    <!--end::Primary Meta Tags-->
    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <!--end::Accessibility Features-->
    <!--begin::Fonts-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
            integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
            crossorigin="anonymous"
            media="print"
            onload="this.media='all'"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
            crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
            crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!-- apexcharts -->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
            integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
            crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
            integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
            crossorigin="anonymous"
    />


    <?php $this->head() ?>


</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">

<?php $this->beginBody() ?>


<div class="app-wrapper">
    <?= $this->render('parts/_header')?>
    <!--begin::Sidebar-->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
            <!--begin::Brand Link-->
            <a href="/" class="brand-link">
                <!--begin::Brand Image-->
                <img
                        src="<?php echo Yii::getAlias('@web/img/logo/logo.svg');?>"
                        alt="Logo"
                        class="brand-image"
                />
                <!--end::Brand Image-->
                <!--begin::Brand Text-->
                <span class="brand-text fw-light"><?= Html::encode(Yii::$app->params['appName'])?></span>
                <!--end::Brand Text-->
            </a>
            <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <?= $this->render('parts/_aside-menu')?>
            </nav>
        </div>
        <!--end::Sidebar Wrapper-->
    </aside>
    <!--end::Sidebar-->

    <!--begin::App Main-->
    <main class="app-main">

        <?= $content; ?>

    </main>
    <!--end::App Main-->

    <!--begin::Footer-->
        <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline"><?= Yii::t('app', 'To do - need to understand!') ?></div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
            &copy; 2025&nbsp;
            <?= Yii::t('app', 'Powered by') ?>
            <a href="<?= Html::encode(Yii::$app->params['appAuthorUrl'])?>" target="_blank" class="text-decoration-none"><?= Html::encode(Yii::$app->params['appAuthor'])?></a>.
        </strong>
        <!--end::Copyright-->
    </footer>
    <!--end::Footer-->

</div>



<script
        src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        crossorigin="anonymous"
></script>
<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        crossorigin="anonymous"
></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
