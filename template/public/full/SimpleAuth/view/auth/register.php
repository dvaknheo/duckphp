<?php declare(strict_types=1);
use SimpleAuth\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<form method="POST" action="<?= __h($url_register); ?>">
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
    <label><?= __hl('重复密码'); ?></label>
    <div>
        <input type="password" name="password_confirm">
    </div>
    <div>
        <button type="submit">
            <?= __hl('注册'); ?>
        </button>
    </div>
</form>

</div>
<?php V::stopSection(); ?>
<?php __display('layouts/app');?>