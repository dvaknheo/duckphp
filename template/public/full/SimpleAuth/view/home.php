<?php declare(strict_types=1);
use SimpleAuth\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
登录成功，这里是主页<br>
<a href="<?= __url('password')?>">修改密码</a><br>
<a href="<?=$url_logout?>">登出</a><br>
<?php V::stopSection(); ?>
<?php __display('layouts/app');?>