<?php
namespace UUU\Service;
use UUU\Base\BaseService;

use UUU\Model as M;

class ArticleService extends BaseService
{
	public function getRecentArticle()
	{
		$ret=M\ArticleModel::G()->getList(1,10);
		return $ret;
	}
	public function getArticleList($page=1,$page_size=10)
	{
		$ret=M\ArticleModel::G()->getList($page,$page_size);
		return $ret;
	}
	public function getArticle($id,$comment_pge=1,$page_size=10)
	{
		$ret=M\ArticleModel::G()->get($id);
		if(!$ret){return array();}
		$ret['comments']=M\CommentExModel::G()->getListByArticle($id,$comment_pge);
		
		return $ret;
	}
	public function getArticleFullInfo($id,$page=1,$page_size=10)
	{
		$art=M\ArticleModel::G()->get($id);
		list($comments,$total)=M\CommentExModel::G()->getListByArticle($id,$page,$page_size);
		$art['comments']=$comments;
		$art['comments_total']=$total;
		return $art;
		
	}
}