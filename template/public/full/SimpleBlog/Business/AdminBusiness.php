<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Model\SettingModel;
use SimpleBlog\Model\ActionLogModel;
use SimpleBlog\Model\ArticleModel;
use SimpleBlog\Model\UserModel;
use SimpleBlog\Model\CommentModel;

class AdminBusiness extends BaseBusiness
{
    public function reset()
    {
        $password = mt_rand(100000, 999999);
        $init_password = $password;
        $password = password_hash(''.$password,PASSWORD_DEFAULT);
        
        SettingModel::G()->set('admin_password', $password);
        return $init_password;
    }
    
    public function changePassword($password)
    {
        $password = password_hash($password);
        SettingModel::G()->set('admin_password', $password);
        return $flag;
    }
    public function login($password)
    {
        $old_password = SettingModel::G()->get('admin_password');
        $flag = password_verify($password, $old_password);
        ActionLogModel::G()->log("管理员登录".($flag?"成功":"失败"), "管理员登录");
        return $flag;
    }
    //////////各种读取列表
    public function getArticle($id)
    {
        $ret = ArticleModel::G()->get($id);
        return $ret;
    }
    public function getUserList($page = 1, $page_size = 10)
    {
        return UserModel::G()->getList($page, $page_size);
    }
    public function getCommentList($page = 1, $page_size = 10)
    {
        return CommentModel::G()->getList($page, $page_size);
    }
    public function getLogList($page = 1, $page_size = 10)
    {
        return ActionLogModel::G()->getList($page, $page_size);
    }
    //////////各种操作
    public function addArticle($title, $content)
    {
        $id = ArticleModel::G()->addData($title, $content);
        ActionLogModel::G()->log("添加文章 {$id}", "添加文章");
        return $id;
    }
    public function updateArticle($id, $title, $content)
    {
        $ret = ArticleModel::G()->updateData($id, $title, $content);
        ActionLogModel::G()->log("编辑 ID 为 {$id},原标题，原内容，更改后标题，更改后内容", "编辑文章");
    }
    public function deleteArticle($id)
    {
        $ret = ArticleModel::G()->delete($id);
        ActionLogModel::G()->log("删除 {$id}，结果", "删除文章");
    }
    ///
    public function deleteUser($id)
    {
        $ret = UserModel::G()->delete($id);
        ActionLogModel::G()->log("删除 {$id}，结果", "删除用户");
    }
    public function deleteComment()
    {
        $ret = M\UserModel::G()->delete($id);
        ActionLogModel::G()->log("删除 {$id}，结果", "删除评论");
    }
}
