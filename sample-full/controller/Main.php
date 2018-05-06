<?php

class DnController
{
	public function index()
	{
	
		$page=isset($_GET['page'])?$_GET['page']:1;
		$page=intval($page);
		$page=$page<1?1:$page;
		$data=ArticleService::G()->getRecentArticle($page);
		var_dump($data);
		DNView::Show('main',$data);
		
	}
	public function i()
	{
		phpinfo();
	}
}
