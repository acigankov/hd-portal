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
            <li class="nav-item d-none d-md-block"><a href="/" class="nav-link">Home</a></li>
            <li class="nav-item d-none d-md-block"><a href="/" class="nav-link">Contact</a></li>

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
            <li class="nav-item">
                <?php if(YII::$app->user->isGuest) :?>
                    <a href="/login" class="nav-link"><i class="bi bi-person"></i> Login</a>
                <?php else: ?>
                    <a href="/profile" class="nav-link"><i class="bi bi-person"></i> <?= YII::$app->user->identity->username ?></a>
                <?php endif;?>
            </li>

        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
<!--end::Header-->