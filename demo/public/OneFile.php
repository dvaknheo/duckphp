<?php
use \DNMVCS\DNMVCS as DN;

require(__DIR__.'/../headfile/headfile.php');

global $view_data;
$view_data=[];
/////////////////////
function get_data()
{
	return isset(DN::SG()->_SESSION['content'])?DN::SG()->_SESSION['content']:'';
}
function add_data($content)
{
	DN::SG()->_SESSION['content']=$content;
}
function update_data($content)
{
	DN::SG()->_SESSION['content']=$content;
}
function delete_data()
{
	unset(DN::SG()->_SESSION['content']);
}
/////////////
function action_index()
{
	global $view_data;
	$view_data['content']=nl2br(DN::H(get_data()));
	$view_data['url_add']=DN::URL('add');
	$view_data['url_edit']=DN::URL('edit');
	$token=DN::SG()->_SESSION['token']=md5(mt_rand());
	$view_data['url_del']=DN::URL('del?token='.$token);
	
}
function action_add()
{
	$data=['x'=>'add'];
	
	DN::Show($data);
	//view_add($data);
}
function action_edit()
{
	$data=['x'=>'add'];
	$data['content']=DN::H(get_data());

	DN::Show($data);

}
function action_del()
{
	$old_token=DN::SG()->_SESSION['token'];
	$new_token=DN::SG()->_GET['token'];
	$flag=($old_token==$new_token)?true:false;
	if($flag){
		unset(DN::SG()->_SESSION['content']);
	}
	unset(DN::SG()->_SESSION['token']);
	$data['msg']=$flag?'':'验证失败';
	$data['url_back']=DN::URL('');
	
	DN::Show($data,'dialog');
}
function action_do_edit()
{
	update_data(DN::SG()->_POST['content']);
	$data=[];
	$data['url_back']=DN::URL('');
	DN::Show($data,'dialog');
}
function action_do_add()
{
	add_data(DN::SG()->_POST['content']);
	$data=[];
	$data['url_back']=DN::URL('');
	DN::Show($data,'dialog');
}
function URL($url){return DN::URL($url);}
function H($str){return DN::H($str);}
////////////////////////////////////
$options=[];
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "<div>Don't run the template file directly </div>"; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $options['setting_file_basename']=''; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $options['is_dev']=true; }

DN::RunOneFileMode($options);

if(!$view_data){return;} 
extract($view_data);

function view_header($view_data=[]){ extract($view_data);
?>
<!doctype html>
<html>
 <meta charset="UTF-8">
<head><title>DNMVCS 单一页面演示</title></head>
<body>
<fieldset>
	<legend>DNMVCS 单一页面演示</legend>
	<div style="border:1px red solid;">
<?php
}
view_header($view_data);
//function main(){
?>
	<h1>首页</h1>
<?php if($content===''){
?>
	还没有内容，
	<a href="<?=$url_add?>">添加内容</a>
<?php
	}else{
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
function view_add($view_data){ extract($view_data);
?>
	<h1>添加</h1>
	<form method="post" >
		<div><textarea name="content"></textarea></div>
		<input type="submit" />
	</form>
<?php
}
function view_edit($view_data){ extract($view_data);
?>
	编辑
	<form method="post">
		<div><textarea name="content"><?=$content?></textarea></div>
		<input type="submit" />
	</form>
<?php

}
function view_dialog($view_data){ extract($view_data);
?>
	<?php if(!$msg){?>已经完成<?php }else{ echo $msg;}?> <a href="<?=$url_back?>">返回主页</a>
<?php

}

function view_footer($view_data=[]){ extract($view_data);
?>
	<hr />
	</div>
</fieldset>
</body>
</html>
<?php
}
view_footer($view_data);
