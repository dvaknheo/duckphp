<?php declare(strict_types=1);

?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?=__h($csrf_token)?>">
    <title>简单用户系统演示</title>
</head>
<body>
    <div id="app">
页眉
<hr>
        <main class="py-4">

<div class="container">
<a href="<?=__url('register')?>">注册</a>
<form method="post" action="<?= __h($url_login); ?>"
    <?= $csrf_field ?>
<?php if (isset($error)) { ?>
    <div><b>错误： <?= __h($error);?></b></div>
<?php }?>
    <label><?= __hl('用户名'); ?></label>
    <div>
        <input name="name" value="<?= __h($name)?>" autofocus>
    </div>
    <label><?= __hl('密码'); ?></label>
    <div>
        <input type="password" name="password">
    </div>
    <div>
        <button type="submit">
            <?= __hl('登录'); ?>
        </button>
    </div>
</form>

        </main>
<hr>
页脚
    </div>
</body>
</html>