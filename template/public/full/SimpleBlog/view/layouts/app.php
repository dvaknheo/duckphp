<?php declare(strict_types=1);
use SimpleAuth\Base\Helper\ViewHelper as V;

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?=V::H($csrf_token)?>">
    <title>简单博客系统</title>
</head>
<body>
    <div id="app">
用户注册登录
<hr>
        <main class="py-4">
            <?= V::yieldContent('content'); ?>
        </main>
<hr>
用户注册登录
    </div>
</body>
</html>