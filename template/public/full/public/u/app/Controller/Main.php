<?php
namespace UUU\Controller;
use UUU\Base\ControllerHelper as C;

use UUU\Service\SessionService;
use UUU\Service\ArticleService;
use UUU\Service\UserService;

class Main
{
	public function __construct()
	{
	}
	public function index()
	{
		$page=intval(C::SG()->_GET['page']??1);
		$page=($page>1)?:1;

		$user=SessionService::G()->getCurrentUser();
		
		list($articles,$total)=ArticleService::G()->getRecentArticle($page);
		C::RecordsetH($articles,['title']);
		C::RecordsetURL($articles,['url'=>'article/{id}']);


		$url_reg=C::URL('reg');
		$url_login=C::URL('login');
		$url_logout=C::URL('logout');
		$url_admin=C::URL('admin');
		
		C::Show(get_defined_vars(),'main');
	}
	public function article()
	{
		$user=SessionService::G()->getCurrentUser();
		
		$id=intval(C::SG()->_GET['id']??1);
		$page=intval(C::SG()->_GET['page']??1);
		$page=($page>1)?:1;
		$page_size=10;
		
		$article=ArticleService::G()->getArticleFullInfo($id,$page,$page_size);
		if(!$article){
			C::Exit404();
		}
		C::RecordsetH($article['comments'],['content','username']);
		$html_pager=C::Pager()::Render($article['comments_total']);
		$url_add_comment=C::URL('addcomment');
		C::Show(get_defined_vars(),'article');
	}
	public function reg()
	{
		C::setViewWrapper('user/inc_head.php','user/inc_foot.php');
		C::Show(get_defined_vars(),'user/reg');
	}
	public function login()
	{
		C::setViewWrapper('user/inc_head.php','user/inc_foot.php');
		C::Show(get_defined_vars(),'user/login');
	}
	public function logout()
	{
		SessionService::G()->logout();
		C::ExitRouteTo('');
	}
	
	public function do_reg()
	{
		try{
			$user=UserService::G()->reg(C::SG()->_POST['username'],C::SG()->_POST['password']);
		}catch(\Exception $ex){
			C::G()->assignViewData('error_info',$ex->getMessage());
			return $this->reg();
		}
		SessionService::G()->setCurrentUser($user);
		C::ExitRouteTo('');
	}
	public function do_login()
	{
		try{
			$user=UserService::G()->login(DN::SG()->_POST['username'],DN::SG()->_POST['password']);
		}catch(\Exception $ex){
			DN::G()->assignViewData('error_info',$ex->getMessage());
			return $this->login();
		}
		SessionService::G()->setCurrentUser($user);
		C::ExitRouteTo('');
	}
	public function do_addcomment()
	{
		$user=SessionService::G()->getCurrentUser();
		UserService::G()->addComment($user['id'],DN::SG()->_POST['article_id'],DN::SG()->_POST['content']);
		C::ExitRouteTo('');
	}
	public function do_delcomment()
	{
		$user=SessionService::G()->getCurrentUser();
		UserService::G()->deleteCommentByUser($user['id'],DN::SG()->_POST['id']);
		C::ExitRouteTo('');
	}
	public function dump()
	{

		$ret=[];
		$tables=['Articles'];
		foreach($tables as $table){
			try{
				$sql="SHOW CREATE TABLE $table";
				$data=DN::DB()->fetch($sql);
				$str=$data['Create Table'];
				$str=preg_replace('/AUTO_INCREMENT=\d+/','AUTO_INCREMENT=1',$str);
				$ret[$table]=$str;
			}catch(\PDOException $ex){}
		}
		var_dump($ret);
		return $ret;
	}
}