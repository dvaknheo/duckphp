<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Base\ControllerHelper  as C;
use SimpleBlog\Service as S;
use SimpleBlog\Service\AdminService;
use SimpleBlog\Service\SessionService;
use SimpleBlog\Service\ArticleService;

class admin
{
    public function __construct()
    {
        $method = C::getRouteCallingMethod();
        if (in_array($method, ['login'])) {
            return;
        }
        $flag = SessionService::G()->checkAdminLogin();
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
        C::setViewWrapper('admin/inc_head', 'admin/inc_foot');
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
        $pass = $_POST['password'] ?? '';
        $r = $_REQUEST['r'] ?? '';
        $flag = AdminService::G()->login($pass);
        if (!$flag) {
            $method = C::getRouteCallingMethod();
            C::ExitRouteTo('admin/login?r=admin/'.$method);
        }
        $r = ($r !== 'admin/login')?$r:'admin/index';

        SessionService::G()->adminLogin();
        C::ExitRouteTo($r);
    }
    public function logout()
    {
        SessionService::G()->adminLogout();
        C::ExitRouteTo('');
    }
    public function reset_password()
    {
        $data = [];
        C::Show($data);
    }
    public function do_reset_password()
    {
        AdminService::G()->changePassword($_POST['password']);
        C::ExitRouteTo('admin');
    }
    public function articles()
    {
        $url_add = C::URL('admin/article_add');
        $page = intval($_GET['page'] ?? 1);
        $page = ($page > 1)?:1;
        list($list, $total) = ArticleService::G()->getArticleList($page);
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
        $title = $_POST['title'];
        $content = $_POST['content'];
        AdminService::G()->addArticle($title, $content);
        C::ExitRouteTo('admin/articles');
    }
    public function article_edit()
    {
        $id = $_GET['id'] ?? 0;
        $article = AdminService::G()->getArticle($id);
        C::ThrowOn(!$article, "找不到文章");
        $article['title'] = C::H($article['title']);
        $article['content'] = C::H($article['content']);
        C::Show(get_defined_vars(), 'admin/article_update');
    }
    public function do_article_edit()
    {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        AdminService::G()->updateArticle($id, $title, $content);
        C::ExitRouteTo('admin/articles');
    }
    public function do_article_delete()
    {
        $id = $_POST['id'];
        AdminService::G()->deleteArticle($id);
        C::ExitRouteTo('admin/articles');
    }
    public function users()
    {
        $page = intval($_GET['page'] ?? 1);
        $page = ($page > 1)?:1;
        list($list, $total) = AdminService::G()->getUserList($page);
        $csrf_token = '';
        foreach ($list as  &$v) {
            $v['url_delete'] = C::URL("admin/delete_user?id={$v['id']}&_token={$csrf_token}");
        }
        C::Show(get_defined_vars());
    }
    public function delete_user()
    {
        $id = $_REQUEST['id'] ?? 0;
        AdminService::G()->deleteUser($id);
        C::ExitRouteTo('admin/users');
    }
    public function logs()
    {
        $page = intval($_GET['page'] ?? 1);
        $page = ($page > 1)?:1;
        list($list, $total) = AdminService::G()->getLogList($page);
        
        $list = C::RecordsetUrl($list, [
            'url_edit' => 'admin/article_edit?id={id}',
            'url_delete' => 'admin/article_delete?id={id}',
        ]);
        
        C::Show(get_defined_vars());
    }
    public function comments()
    {
        $page = intval($_GET['page'] ?? 1);
        $page = ($page > 1)?:1;
        
        list($list, $total) = AdminService::G()->getCommentList($page);
        C::Show(get_defined_vars());
    }
    public function delete_comments()
    {
        $id = $_POST['id'];
        AdminService::G()->deleteComment($id);
        
        C::ExitRouteTo('admin/comments');
    }
}
