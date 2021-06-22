<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Helper\ControllerHelper  as C;
use SimpleBlog\Business\AdminBusiness;
use SimpleBlog\Business\ArticleBusiness;

class admin
{
    public function __construct()
    {
        C::CheckInstall();
        
        $method = C::getRouteCallingMethod();
        if (in_array($method, ['login'])) {
            return;
        }
        $flag = C::SessionManager()->checkAdminLogin();
        if (!$flag) {
            C::ExitRouteTo('admin/login?r=admin/'.$method);
            return;
        }
        //如果没登录，到登录页面
        $data = [
            'url_articles' => 'admin/articles',
            'url_comments' => 'admin/comments',
            'url_users' => 'admin/users',
            'url_logs' => 'admin/logs',
            'url_logout' => 'admin/logout',
            'url_changepass' => 'admin/reset_password',
        ];
        array_walk($data, function (&$v) {
            $v = C::URL($v);
        });
        C::setViewHeadFoot('admin/inc_head', 'admin/inc_foot');
        C::assignViewData($data);
    }
    public function index()
    {
        C::Show([], 'admin/main');
    }
    public function login()
    {
        $data = [];
        C::Show($data);
    }
    public function do_login()
    {
        $pass = C::POST('password', '');
        $r = C::REQUEST('r','');
        
        $flag = AdminBusiness::G()->login($pass);
        if (!$flag) {
            $method = C::getRouteCallingMethod();
            C::ExitRouteTo('admin/login?r=admin/'.$method);
        }
        $r = ($r !== 'admin/login')?$r:'admin/index';

        C::SessionManager()->adminLogin();
        C::ExitRouteTo($r);
    }
    public function logout()
    {
        C::SessionManager()->adminLogout();
        C::ExitRouteTo('');
    }
    public function reset_password()
    {
        $data = [];
        C::Show($data);
    }
    public function do_reset_password()
    {
        AdminBusiness::G()->changePassword(POST('password'));
        C::ExitRouteTo('admin');
    }
    public function articles()
    {
        $url_add = C::URL('admin/article_add');
        list($list, $total) = ArticleBusiness::G()->getArticleList(C::PageNo());
        $list = C::RecordsetUrl($list, [
            'url_edit' => 'admin/article_edit?id={id}',
            'url_delete' => 'admin/article_delete?id={id}',
        ]);
        C::Show(get_defined_vars(), 'admin/article_list');
    }
    public function article_add()
    {
        C::Show(get_defined_vars());
    }
    public function do_article_add()
    {
        AdminBusiness::G()->addArticle(C::POST('title'), C::POST('content'));
        C::ExitRouteTo('admin/articles');
    }
    public function article_edit()
    {
        $article = AdminBusiness::G()->getArticle(C::GET('id',0));
        //C::ThrowOn(!$article, "找不到文章"); => TODO
        $article['title'] = C::H($article['title']);
        $article['content'] = C::H($article['content']);
        C::Show(get_defined_vars(), 'admin/article_update');
    }
    public function do_article_edit()
    {
        AdminBusiness::G()->updateArticle(C::POST('id'), C::POST('title'), C::POST('content'));
        C::ExitRouteTo('admin/articles');
    }
    public function do_article_delete()
    {
        AdminBusiness::G()->deleteArticle(C::POST('id'));
        C::ExitRouteTo('admin/articles');
    }
    public function users()
    {
        list($list, $total) = AdminBusiness::G()->getUserList(C::PageNo());
        $csrf_token = '';
        foreach ($list as  &$v) {
            $v['url_delete'] = C::URL("admin/delete_user?id={$v['id']}&_token={$csrf_token}");
        }
        C::Show(get_defined_vars());
    }
    public function delete_user()
    {
        AdminBusiness::G()->deleteUser(C::REQUEST('id'));
        C::ExitRouteTo('admin/users');
    }
    public function logs()
    {
        list($list, $total) = AdminBusiness::G()->getLogList(C::PageNo());
        
        $list = C::RecordsetUrl($list, [
            'url_edit' => 'admin/article_edit?id={id}',
            'url_delete' => 'admin/article_delete?id={id}',
        ]);
        
        C::Show(get_defined_vars());
    }
    public function comments()
    {
        list($list, $total) = AdminBusiness::G()->getCommentList(C::PageNo());
        C::Show(get_defined_vars());
    }
    public function delete_comments()
    {
        AdminBusiness::G()->deleteComment(C::POST('id'));
        C::ExitRouteTo('admin/comments');
    }
}
