<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hello DuckPhp!</title>
</head>
<body>
<h1>欢迎使用Simple Blog 安装程序 </h1>
<?php
if($is_installed){
?>
你已经安装了本程序， 请删除设置文件再来。
<?php
}else if($done){
?>
<meta http-equiv="refresh" content="5; url=<?= __domain(true).__url('');?>">
安装完成，5秒后跳转回首页
<?php
}else if($is_file_error){
?>
写入设置文件失败，
请把下列内容复制到设置文件<br /> 
<?=__h($file)?>
<textarea>
    <?=__h($setting)?>
</textarea>
<?php
}else{
?>
设置数据库
<form class="form-horizontal" method="post">
<?php
    if($is_db_error){
?>
    <div class="form-group">
        <label class="col-sm-2 control-label">错误信息</label>
        <div class="col-sm-10">
            <?=$error_message?>
        </div>
    </div>
<?php
    }
?>
    <input type="hidden" name="csrf" value="<?=$csrf_token?>" />
    <div class="form-group">
        <label class="col-sm-2 control-label">数据库服务器</label>
        <div class="col-sm-10">
            <input type="text" name="host" class="form-control" placeholder="数据库服务器" value="<?=__h($database['host'])?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">数据库端口号</label>
        <div class="col-sm-10">
            <input type="text" name="port" class="form-control" value="<?=__h($database['port'])?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">数据库名</label>
        <div class="col-sm-10">
            <input type="text" name="dbname" class="form-control" value="<?=__h($database['dbname'])?>" placeholder="不存在会自动创建">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">数据库用户名</label>
        <div class="col-sm-10">
            <input type="text" name="username" class="form-control" value="<?=__h($database['username'])?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">数据库密码</label>
        <div class="col-sm-10">
            <input type="text" name="password" class="form-control" value="<?=__h($database['password'])?>">
        </div>
    </div>
    <div class="form-group">
        <div class="panel-button text-center">
            <input type="submit" class="btn btn-info" value="保存" />
        </div>
    </div>
</form>
<?php
}
?>
</body>

</html>