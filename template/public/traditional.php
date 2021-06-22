<?php declare(strict_types=1);
use DuckPhp\Core\View;
use DuckPhp\DuckPhp;

require(__DIR__.'/../../autoload.php');  // @DUCKPHP_HEADFILE
//// 这个例子极端点，没用任何类，全函数模式。

////[[[[
//// 这部分是核心程序员写的。
function RunByDuckPhp()
{
    $options = [];
    $options['is_debug'] = true;
    $options['namespace'] = '\\';               // 不要替换成同级别的控制器类
    $options['path_info_compact_enable'] = true;    // 不用配置路由

    $options['ext'][\DuckPhp\Ext\EmptyView::class] = true; // for AllViewData();
    $options['ext'][\DuckPhp\Ext\RouteHookFunctionRoute::class] = true; // 我们用这个扩展
    $flag = DuckPhp::RunQuickly($options);
    return $flag;
}
function GetRunResult()
{
    return DuckPhp::getViewData();
}
function POST($k, $v = null)
{
    return DuckPhp::POST($k, $v);
}
if (!function_exists('__show')) {
    function __show(...$args)
    {
        return DuckPhp::Show(...$args);
    }
}

////]]]]
function get_data()
{
    return $_SESSION['content'] ?? '';
}
function add_data($content)
{
    $_SESSION['content'] = $content;
}
function update_data($content)
{
    $_SESSION['content'] = $content;
}
function delete_data()
{
    unset($_SESSION['content']);
    //unset($_SESSION['content']);
}
/////////////
function action_index()
{
    $data['content'] = nl2br(__h(get_data()));
    $data['url_add'] = __url('add');
    $data['url_edit'] = __url('edit');
    
    $token = $_SESSION['token'] = md5(''.mt_rand());
    
    $data['url_del'] = __url('del?token='.$token);

    __show($data, 'index');
}
function action_add()
{
    $data = ['x' => 'add'];
    
    __show($data);
}
function action_edit()
{
    $data = ['x' => 'edit'];
    $data['content'] = __h(get_data());

    __show($data);
}
function action_del()
{
    $old_token = $_SESSION['token'];
    $new_token = $_GET['token'];
    $flag = ($old_token === $new_token)?true:false;
    if ($flag) {
        unset($_SESSION['content']);
    }
    unset($_SESSION['token']);
    $data['msg'] = $flag?'':'验证失败';
    $data['url_back'] = __url('');
    
    __show($data, 'dialog');
}
function action_do_edit()
{
    update_data(POST('content'));
    $data = [];
    $data['url_back'] = __url('');
    __show($data, 'dialog');
}
function action_do_add()
{
    add_data(POST('content'));
    $data = [];
    $data['url_back'] = __url('');
    __show($data, 'dialog');
}
////////////////////////////////////
session_start();
$flag = RunByDuckPhp();
if (!$flag) {
    // 我们 404 了
}
extract(GetRunResult());

error_reporting(error_reporting() & ~E_NOTICE);

if (isset($view_head)) {
    ?>
<!doctype html>
<html>
 <meta charset="UTF-8">
<head><title>DuckPhp 单一页面演示</title></head>
<body>
<?php
    echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
?>
<fieldset>
	<legend>DuckPhp 单一页面演示</legend>
	<div style="border:1px red solid;">
<?php
}
if ($view === 'index') {
    ?>
	<h1>首页</h1>
<?php
    if ($content === '') {
        ?>
	还没有内容，
	<a href="<?=$url_add?>">添加内容</a>
<?php
    } else {
        ?>
	已经输入，内容为
	<div style="border:1px gray solid;" ><?=$content?></div>
	<a href="<?=$url_edit?>">编辑内容</a>
	<a href="<?=$url_del?>">删除内容（已做GET安全处理）</a>
<?php
    } ?>
<?php
}
if ($view === 'add') {
    ?>
	<h1>添加</h1>
	<form method="post" >
		<div><textarea name="content"></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if ($view === 'edit') {
    ?>
	编辑
	<form method="post">
		<div><textarea name="content"><?=$content?></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if ($view === 'dialog') { ?>
	<?php if (!($msg ?? false)) {?>已经完成<?php } else {
    echo $msg;
} ?> <a href="<?=$url_back?>">返回主页</a>
<?php
}

if (isset($view_foot)) {
    ?>
	<hr />
	</div>
</fieldset>
</body>
</html>
<?php
}
