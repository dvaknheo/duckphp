<?php
namespace UUU\Controller;

use UUU\Base\ControllerHelper  as DN;
use UUU\Service as S;
use UUU\Service\AdminService;
use UUU\Service\SessionService;
use UUU\Service\ArticleService;

class admin
{
	public function __construct()
	{
		$method=DN::G()->getRouteCallingMethod();
		if(in_array($method,['login','do_login'])){
			return;
		}
		$flag=SessionService::G()->checkAdminLogin();
		if(!$flag){
			DN::ExitRouteTo('admin/login?r=admin/'.$method);
			return;
		}
		//如果没登录，到登录页面
		$data=[
			'url_articles'=>'admin/articles',
			'url_comments'=>'admin/comments',
			'url_users'=>'admin/users',
			'url_logs'=>'admin/logs',
			'url_logout'=>'admin/logout',
			'url_changepass'=>'admin/reset_password',
		];
		array_walk($data,function(&$v){$v=DN::URL($v);});
		DN::G()->setViewWrapper('admin/inc_head','admin/inc_foot');
		DN::G()->assignViewData($data);
	}
	public function index()
	{
		DN::Show([],'admin/main');
	}
	public function login()
	{
		$data=[];
		DN::Show($data);
	}
	public function do_login()
	{
		$pass=$_POST['password']??'';
		$r=$_REQUEST['r'];
		$flag=AdminService::G()->login($pass);
		if(!$flag){
			DN::ExitRouteTo('admin/login?r=admin/'.$method);
		}
		SessionService::G()->adminLogin();
		DN::ExitRouteTo($r);
	}
	public function logout()
	{
		SessionService::G()->adminLogout();
		DN::ExitRouteTo('/');
	}
	public function reset_password()
	{
		$data=[];
		DN::Show($data);
	}
	public function do_reset_password()
	{
		AdminService::G()->changePassword($_POST['password']);
		DN::ExitRouteTo('admin');
	}
	public function articles()
	{
		$url_add=DN::URL('admin/article_add');
		$page=intval($_GET['page']??1);
		$page=($page>1)?:1;
		list($list,$total)=ArticleService::G()->getArticleList($page);
		$list=DN::RecordsetURL($list,[
			'url_edit'=>'admin/article_edit?id={id}',
			'url_delete'=>'admin/article_delete?id={id}',
		]);
		DN::Show(get_defined_vars(),'admin/article_list');
	}
	public function article_add()
	{
		DN::Show(get_defined_vars());
	}
	public function do_article_add()
	{
		$title=$_POST['title'];
		$content=$_POST['content'];
		AdminService::G()->addArticle($title,$content);
		DN::ExitRouteTo('admin/articles');
	}
	public function article_edit()
	{
		$id=$_GET['id']??0;
		$article=AdminService::G()->getArticle($id);
		DN::ThrowOn(!$article,"找不到文章");
		$article['title']=DN::H($article['title']);
		$article['content']=DN::H($article['content']);
		DN::Show(get_defined_vars(),'admin/article_update');
	}
	public function do_article_edit()
	{
		$id=$_POST['id'];
		$title=$_POST['title'];
		$content=$_POST['content'];
		AdminService::G()->updateArticle($id,$title,$content);
		DN::ExitRouteTo('admin/articles');
	}
	public function do_article_delete()
	{
		$id=$_POST['id'];
		AdminService::G()->deleteArticle($id);
		DN::ExitRouteTo('admin/articles');
	}
	public function users()
	{
		$page=intval($_GET['page']??1);
		$page=($page>1)?:1;
		list($list,$total)=AdminService::G()->getUserList($page);

		DN::Show(get_defined_vars());
	}
	public function delete_user()
	{
		$id=$_POST['id'];
		AdminService::G()->deleteUser($id);
		DN::ExitRouteTo('admin/users');
	}
	public function logs()
	{
		$page=intval($_GET['page']??1);
		$page=($page>1)?:1;
		list($list,$total)=AdminService::G()->getLogList($page);
		
		$list=DN::RecordsetURL($list,[
			'url_edit'=>'admin/article_edit?id={id}',
			'url_delete'=>'admin/article_delete?id={id}',
		]);
		
		DN::Show(get_defined_vars());
	}
	public function comments()
	{
		$page=intval($_GET['page']??1);
		$page=($page>1)?:1;
		
		list($list,$total)=AdminService::G()->getCommentList($page);
		DN::Show(get_defined_vars());
	}
	public function delete_comments()
	{
		$id=$_POST['id'];
		AdminService::G()->deleteComment($id);
		
		DN::ExitRouteTo('admin/comments');
	}
	
}
