<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UUU\Controller;

use UUU\Base\ControllerHelper as C;

use UUU\Service\SessionService;
use UUU\Service\ArticleService;
use UUU\Service\UserService;

class Main
{
    public function __construct()
    {
        //C::assignExceptionHandel( )
    }
    public function index()
    {
        $page = intval(C::SG()->_GET['page'] ?? 1);
        $page = ($page > 1)?:1;

        $user = SessionService::G()->getCurrentUser();
        
        list($articles, $total) = ArticleService::G()->getRecentArticle($page);
        $articles = C::RecordsetH($articles, ['title']);
        $articles = C::RecordsetUrl($articles, ['url' => 'article/{id}']);


        $url_reg = C::URL('register');
        $url_login = C::URL('login');
        $url_logout = C::URL('logout');
        $url_admin = C::URL('admin/index');
        
        C::Show(get_defined_vars(), 'main');
    }
    public function article()
    {
        $user = SessionService::G()->getCurrentUser();
        
        $id = intval(C::SG()->_GET['id'] ?? 1);
        $page = intval(C::SG()->_GET['page'] ?? 1);
        $page = ($page > 1)?:1;
        $page_size = 10;
        
        $article = ArticleService::G()->getArticleFullInfo($id, $page, $page_size);
        if (!$article) {
            C::Exit404();
        }
        $article['comments'] = C::RecordsetH($article['comments'], ['content','username']);
        $html_pager = C::Pager()::Render($article['comments_total']);
        $url_add_comment = C::URL('addcomment');
        C::Show(get_defined_vars(), 'article');
    }
    public function _old_reg()
    {
        C::setViewWrapper('user/inc_head.php', 'user/inc_foot.php');
        C::Show(get_defined_vars(), 'user/reg');
    }
    public function do_changepass()
    {
        $uid = SessionService::G()->getCurrentUid();
    }
    public function do_addcomment()
    {
        $uid = SessionService::G()->getCurrentUid();
        UserService::G()->addComment($uid, C::SG()->_POST['article_id'], C::SG()->_POST['content']);
        C::ExitRouteTo('article/'.C::SG()->_POST['article_id']);
    }
    public function do_delcomment()
    {
        $user = SessionService::G()->getCurrentUser();
        UserService::G()->deleteCommentByUser($user['id'], DN::SG()->_POST['id']);
        C::ExitRouteTo('');
    }
    public function dump()
    {
        $ret = [];
        $tables = ['Articles'];
        foreach ($tables as $table) {
            try {
                $sql = "SHOW CREATE TABLE $table";
                $data = DN::DB()->fetch($sql);
                $str = $data['Create Table'];
                $str = preg_replace('/AUTO_INCREMENT=\d+/', 'AUTO_INCREMENT=1', $str);
                $ret[$table] = $str;
            } catch (\PDOException $ex) {
            }
        }
        var_dump($ret);
        return $ret;
    }
}
