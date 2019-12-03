<?php
die("暂时无效");
use \DuckPhp\App;

$options=[];
require_once(__DIR__.'/../headfile/headfile.php');
//* DuckPhp TO DELETE
if (defined('DuckPhp_WARNING_IN_TEMPLATE')) {
    $options['is_debug']=true;
    $options['skip_setting_file']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
//*/
global $view_data;
$view_data=[];
/////////////////////
function get_data()
{
    return isset(App::SG()->_SESSION['content'])?App::SG()->_SESSION['content']:'';
}
function add_data($content)
{
    App::SG()->_SESSION['content']=$content;
}
function update_data($content)
{
    App::SG()->_SESSION['content']=$content;
}
function delete_data()
{
    unset(App::SG()->_SESSION['content']);
}
/////////////
function action_index()
{
    global $view_data;
    $view_data['content']=nl2br(App::H(get_data()));
    $view_data['url_add']=App::URL('add');
    $view_data['url_edit']=App::URL('edit');
    $token=App::SG()->_SESSION['token']=md5(mt_rand());
    $view_data['url_del']=App::URL('del?token='.$token);
}
function action_add()
{
    $data=['x'=>'add'];
    
    App::Show($data);
    //view_add($data);
}
function action_edit()
{
    $data=['x'=>'add'];
    $data['content']=App::H(get_data());

    App::Show($data);
}
function action_del()
{
    $old_token=App::SG()->_SESSION['token'];
    $new_token=App::SG()->_GET['token'];
    $flag=($old_token==$new_token)?true:false;
    if ($flag) {
        unset(App::SG()->_SESSION['content']);
    }
    unset(App::SG()->_SESSION['token']);
    $data['msg']=$flag?'':'验证失败';
    $data['url_back']=App::URL('');
    
    App::Show($data, 'dialog');
}
function action_do_edit()
{
    update_data(App::SG()->_POST['content']);
    $data=[];
    $data['url_back']=App::URL('');
    App::Show($data, 'dialog');
}
function action_do_add()
{
    add_data(App::SG()->_POST['content']);
    $data=[];
    $data['url_back']=App::URL('');
    App::Show($data, 'dialog');
}
function URL($url)
{
    return App::URL($url);
}
function H($str)
{
    return App::H($str);
}
////////////////////////////////////


App::RunOneFileMode($options);

if (!$view_data) {
    return;
}
extract($view_data);

function view_header($view_data=[])
{
    extract($view_data); ?>
<!doctype html>
<html>
 <meta charset="UTF-8">
<head><title>DuckPhp 单一页面演示</title></head>
<body>
<fieldset>
	<legend>DuckPhp 单一页面演示</legend>
	<div style="border:1px red solid;">
<?php
}
view_header($view_data);
//function main(){
?>
	<h1>首页</h1>
<?php if ($content==='') {
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
// }// main
function view_add($view_data)
{
    extract($view_data); ?>
	<h1>添加</h1>
	<form method="post" >
		<div><textarea name="content"></textarea></div>
		<input type="submit" />
	</form>
<?php
}
function view_edit($view_data)
{
    extract($view_data); ?>
	编辑
	<form method="post">
		<div><textarea name="content"><?=$content?></textarea></div>
		<input type="submit" />
	</form>
<?php
}
function view_dialog($view_data)
{
    extract($view_data); ?>
	<?php if (!$msg) {
        ?>已经完成<?php
    } else {
        echo $msg;
    } ?> <a href="<?=$url_back?>">返回主页</a>
<?php
}

function view_footer($view_data=[])
{
    extract($view_data); ?>
	<hr />
	</div>
</fieldset>
</body>
</html>
<?php
}
view_footer($view_data);
