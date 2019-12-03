<?php
namespace UUU\Service;
use DNMVCS as DN;
use UUU\Model as M;

class AdminService
{
	use \DNMVCS\DNSingleton;

	public function reset()
	{
		$password=mt_rand(100000,999999);
		$init_password=$password;
		$password=password_hash($password, PASSWORD_BCRYPT);
		
		M\SettingModel::G()->set('admin_password',$password);
		return $init_password;
	}
	
	public function changePassword($password)
	{
		$password=password_hash($password, PASSWORD_BCRYPT);
		M\SettingModel::G()->set('admin_password',$password);
		return $flag;
	}
	public function login($password)
	{
		$old_password=M\SettingModel::G()->get('admin_password');
		$flag=password_verify($password,$old_password);

		M\ActionLogModel::G()->log("管理员登录".($flag?"成功":"失败"),"管理员登录");
		return $flag;
	}
	//////////各种读取列表
	public function getArticle($id)
	{
		$ret=M\ArticleModel::G()->get($id);
		return $ret;
	}
	public function getUserList($page=1,$page_size=10)
	{
		return M\UserModel::G()->getList($page,$page_size);
	}
	public function getCommentList($page=1,$page_size=10)
	{
		return M\CommentModel::G()->getList($page,$page_size);

	}
	public function getLogList($page=1,$page_size=10)
	{
		return M\ActionLogModel::G()->getList($page,$page_size);
	}
	//////////各种操作
	public function addArticle($title,$content)
	{
		$ret=M\ArticleModel::G()->addData($title,$content);
		M\ActionLogModel::G()->log("添加文章 {$id}","添加文章");
		return $ret;
	}
	public function updateArticle($id,$title,$content)
	{
		$ret=M\ArticleModel::G()->updateData($id,$title,$content);
		M\ActionLogModel::G()->log("编辑 ID 为 {$id},原标题，原内容，更改后标题，更改后内容","编辑文章");
	}
	public function deleteArticle($id)
	{
		$ret=M\ArticleModel::G()->delete($id);
		M\ActionLogModel::G()->log("删除 {$id}，结果","删除文章");
	}
	///
	public function deleteUser($id)
	{
		$ret=M\UserModel::G()->delete($id);
		M\ActionLogModel::G()->log("删除 {$id}，结果","删除用户");
	}
	public function deleteComment()
	{
		$ret=M\UserModel::G()->delete($id);
		M\ActionLogModel::G()->log("删除 {$id}，结果","删除评论");
	}
}