<?php declare(strict_types=1);
use UserSystemDemo\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
登录成功，这里是主页<br>
<a href="<?= V::URL('password')?>">修改密码</a><br>
<a href="<?=$url_logout?>">登出</a><br>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>