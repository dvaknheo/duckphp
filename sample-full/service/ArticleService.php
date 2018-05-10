<?php
class ArticleService extends DNService
{
	public function getRecentArticle($page=1)
	{
		$ret=array(
			't'=>DATE(DATE_ATOM),
		);
		return $ret;
	}
	public function getList()
	{
	}
	public function getArticle($id)
	{
		$ret=ArticleModel::G()->getData();
		return $ret;
	}
	public function addArticle($uid,$title,$content)
	{
		ArticleModel::G()->addData($uid,$title,$content);
	}
	public function updateArticle($uid,$id,$title,$content)
	{
		ArticleModel::G()->updateArticle($uid,$id,$title,$content);
	}
	public function deleteArticle($uid,$id)
	{
		ArticleModel::G()->deleteArticle($uid,$id);
	}
}