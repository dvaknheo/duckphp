<?php
use UserSystemDemo\Base\Helper\ViewHelper as V;

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?=V::H($csrf_token)?>">
    <title>简单用户系统演示</title>
</head>
<body>
    <div id="app">
页眉
<hr>
        <main class="py-4">
            <?= V::yieldContent('content'); ?>
        </main>
<hr>
页脚
    </div>
</body>
</html>