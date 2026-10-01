<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//autoload file
$autoload_file = __DIR__.'../vendor/autoload.php';
if (is_file($autoload_file)) {
    require_once $autoload_file;
} else {
    $autoload_file = __DIR__.'/../../vendor/autoload.php';
    if (is_file($autoload_file)) {
        require_once $autoload_file;
    }
}
////////////////////////////////////////
use DuckPhp\DuckPhp;
use DuckPhp\Core\View;
use DuckPhp\Foundation\Controller\ControllerHelper as Helper;



//// This example goes to extremes: no classes at all, pure functions.
////[[[[
//// This part is written by the core engineer.
function RunByDuckPhp()
{
    $options = [];
    $options['is_debug'] = true;
    $options['namespace'] = '\\';               // do not rewrite it into a controller class of the same level
    $options['path_info_compact_enable'] = true;    // no routing configuration needed

    // `path` + `lang_default`: find `demo/config/lang-*.php` (and the rest of
    // demo's config) instead of resolving them against the current directory.
    $options['path'] = __DIR__ . '/../';
    $options['lang_default'] = 'en';

    $options['ext'][\DuckPhp\Ext\EmptyView::class] = true; // for GetRunResult();
    $options['ext'][\DuckPhp\Ext\RouteHookFunctionRoute::class] = true; // this is the extension we use
    $flag = DuckPhp::RunQuickly($options);
    
    return $flag;
}
function GetRunResult()
{
    $ret = View::_()->getViewData();
    return $ret;
}
function POST($k = null, $v = null)
{
    return Helper::POST($k, $v);
}
if (!function_exists('__show')) {
    function __show(...$args)
    {
        Helper::Show(...$args);
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
    if (POST()) {
        return action_do_add();
    }
    $data = ['x' => 'add'];
    
    __show($data);
}
function action_edit()
{
    if (POST()) {
        return action_do_edit();
    }
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
    $data['msg'] = $flag?'':__l('traditional.verify_failed');
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
    // we ended up in a 404
}
$xxx = GetRunResult();
extract($xxx);

error_reporting(error_reporting() & ~E_NOTICE);

if (isset($view_header)) {
    ?>
<!doctype html>
<html>
 <meta charset="UTF-8">
<head><title><?=__l('traditional.page_title')?></title></head>
<body>
<?php
    echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE
?>
<fieldset>
	<legend><?=__l('traditional.page_title')?></legend>
	<div style="border:1px red solid;">
<?php
}
if ($view === 'index') {
    ?>
	<h1><?=__l('traditional.home')?></h1>
<?php
    if ($content === '') {
        ?>
	<?=__l('traditional.empty')?>
	<a href="<?=$url_add?>"><?=__l('traditional.add_content')?></a>
<?php
    } else {
        ?>
	<?=__l('traditional.has_content')?>
	<div style="border:1px gray solid;" ><?=$content?></div>
	<a href="<?=$url_edit?>"><?=__l('traditional.edit_content')?></a>
	<a href="<?=$url_del?>"><?=__l('traditional.delete_content')?></a>
<?php
    } ?>
<?php
}
if ($view === 'add') {
    ?>
	<h1><?=__l('traditional.add')?></h1>
	<form method="post" >
		<div><textarea name="content"></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if ($view === 'edit') {
    ?>
	<?=__l('traditional.edit')?>
	<form method="post">
		<div><textarea name="content"><?=$content?></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if ($view === 'dialog') { ?>
	<?php if (!($msg ?? false)) {?><?=__l('traditional.done')?><?php } else {
    echo $msg;
} ?> <a href="<?=$url_back?>"><?=__l('traditional.back_home')?></a>
<?php
}

if (isset($view_footer)) {
    ?>
	<hr />
	</div>
</fieldset>
</body>
</html>
<?php
}
