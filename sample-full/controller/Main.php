<?php
class DnController
{
	public function __construct()
	{
		//把错误处理都放在这里。
	}
	public function index()
	{
		$page=isset($_GET['page'])?$_GET['page']:1;
		$page=intval($page);
		$page=$page<1?1:$page;
		$data=ArticleService::G()->getRecentArticle($page);
		$user=SessionService::G()->getCurrentUser();
		$data['user']=$user;
		
		$data['url_reg']=URL('/reg');
		$data['url_login']=URL('/login');
		$data['url_logout']=URL('/logout');
		DNView::Show('main',$data);
		
	}
	public function reg()
	{
		$data=array();
		DNView::Show('user/reg',$data);
	}
	public function login()
	{
		$data=array();
		DNView::Show('user/login',$data);
	}
	public function logout()
	{
		SessionService::G()->logout();
		
		DNView::return_route_to('/');
	}
	
	public function do_reg()
	{
		$user=UserService::G()->reg($_POST['username'],$_POST['password']);
		SessionService::G()->setCurrentUser($user);
		DNView::return_route_to('/');
	}
	public function do_login()
	{
		$user=UserService::G()->login($_POST['username'],$_POST['password']);
		SessionService::G()->setCurrentUser($user);
		
		DNView::return_route_to('/');
	}
}
