<?php
use Project\Base\Helper\ViewHelper as V;

?><!doctype html>
<html lang="<?=$locale  ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?=V::H($csrf_token)?>">

    <title><?= V::H($app_name); ?></title>

    <!-- Scripts -->
    <script src="<?= V::H($asset_js); ?>" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="<?= V::H($asset_css); ?>" rel="stylesheet">
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="<?= V::H($url_root); ?>">
                    <?= V::H($app_name); ?>

                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?= V::H('Toggle navigation'); ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        <?php if($is_guest): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= V::H($url_login); ?>"><?= V::H('Login'); ?></a>
                            </li>
                            <?php if($has_route_register): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= V::H($url_register); ?>"><?= V::H('Register'); ?></a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <?= V::H($user_name); ?> <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?= V::H($url_logout); ?>"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <?= V::H('Logout'); ?>

                                    </a>

                                    <form id="logout-form" action="<?= V::H($url_logout); ?>" method="POST" style="display: none;">
                                        <?= $csrf_field ?>
                                    </form>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <?= V::yieldContent('content'); ?>
        </main>
    </div>
</body>
</html>