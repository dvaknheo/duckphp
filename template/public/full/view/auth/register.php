<?php declare(strict_types=1);
use UserSystemDemo\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<form method="POST" action="<?= V::H($url_register); ?>">
    <?= $csrf_field ?>
<?php if (isset($error)) { ?>
    <div><b>错误： <?= V::H($error);?></b></div>
<?php }?>
    <label><?= V::HL('用户名'); ?></label>
    <div>
        <input name="name" value="<?= V::H($name)?>" autofocus>
    </div>
    <label><?= V::HL('密码'); ?></label>
    <div>
        <input type="password" name="password">
    </div>
    <label><?= V::HL('重复密码'); ?></label>
    <div>
        <input type="password" name="password_confirm">
    </div>
    <div>
        <button type="submit">
            <?= V::HL('注册'); ?>
        </button>
    </div>
</form>

</div>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>