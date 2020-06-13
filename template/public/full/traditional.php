<?php declare(strict_types=1);
use DuckPhp\App;

//未完工
require(__DIR__.'/../../../autoload.php');  // @DUCKPHP_HEADFILE
//// 这个例子极端点，没用任何类，全函数模式。

////[[[[
class MyView extends \DuckPhp\Core\View
{
    public function _Show($data = [], $view)
    {
        $this->data = array_merge($this->data, $data);
        $this->data['view']=$view;
    }
    public function _Display($view, $data = null)
    {
        $this->data = isset($data)?$data:$this->data;
        $this->data['skip_head_foot']=true;
        $this->data['view']=$view;
    }
}
function onInit()
{
    \DuckPhp\Core\View::G(MyView::G());
    \DuckPhp\Ext\RouteHookOneFileMode::G()->init(App::G()->options,App::G());
    App::G()->add404RouteHook(function(){
        $path_info=App::G()->getPathInfo();
        $path_info=ltrim($path_info,'/');
        $path_info=empty($path_info)?'index':$path_info;
        
        $post_prefix=!empty($_POST)?'do_':'';
        $callback="action_{$post_prefix}{$path_info}";
        
        if(is_callable($callback)){
            ($callback)();
            return true;
        }
        action_index();
        return true;
    });
}
function POST($k,$v=null)
{
    return App::POST($k,$v);
}
function AllViewData()
{
    $view=&View::G()->data['view'];
    $view=isset($view)?$view:'';
    if(substr($view,0,strlen('Main/'))==='Main/'){
        $view=substr($view,strlen('Main/'));
    }
    
    return View::G()->data;
}
if (!function_exists('__show')) {
    function __show(...$args)
    {
        return App::Show(...$args);
    }
}

////]]]]
function get_data()
{
    return$_SESSION['content'] ?? '';
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
    
    $token =$_SESSION['token'] = md5(''.mt_rand());
    
    $data['url_del'] = __url('del?token='.$token);

    __show($data,'');
}
function action_add()
{
    $data = ['x' => 'add'];
    
    __show($data);
}
function action_edit()
{
    $data = ['x' => 'add'];
    $data['content'] = __h(get_data());

    __show($data);
}
function action_del()
{
    $old_token =$_SESSION['token'];
    $new_token =$_GET['token'];
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
$options = [];
$options['skip_setting_file'] = true;
$options['is_debug'] = true;
$flag=App::RunQuickly($options,'onInit');
if(!$flag){
    return;
}
extract(AllViewData());
error_reporting(error_reporting() & ~E_NOTICE);

if(!empty($skip_head_foot)){
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
if($view===''){
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
    }
?>
<?php
}
if($view==='add'){
?>
	<h1>添加</h1>
	<form method="post" >
		<div><textarea name="content"></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if($view==='edit'){
 ?>
	编辑
	<form method="post">
		<div><textarea name="content"><?=$content?></textarea></div>
		<input type="submit" />
	</form>
<?php
}
if($view==='dialog'){ ?>
	<?php if (!($msg??false)) {?>已经完成<?php } else {
        echo $msg;
    } ?> <a href="<?=$url_back?>">返回主页</a>
<?php
}

if(!empty($skip_head_foot)){
?>
	<hr />
	</div>
</fieldset>
</body>
</html>
<?php
}
