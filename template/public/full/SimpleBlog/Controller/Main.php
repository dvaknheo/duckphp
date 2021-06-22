<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Business\ArticleBusiness;
use SimpleBlog\Business\UserBusiness;
use SimpleBlog\Helper\ControllerHelper as C;

class Main
{
    public function __construct()
    {
        C::CheckInstall();
    }
    public function index()
    {
        $url_reg = C::URL('register');
        $url_login = C::URL('login');
        $url_logout = C::URL('logout');
        $url_admin = C::URL('admin/index');

        $user = C::SessionManager()->getCurrentUser();
        list($articles, $total) = ArticleBusiness::G()->getRecentArticle(C::PageNo());
        
        $articles = C::RecordsetH($articles, ['title']);
        $articles = C::RecordsetUrl($articles, ['url' => 'article/{id}']);
        
        C::Show(get_defined_vars(), 'main');
    }
    public function article()
    {
        $id = C::GET('id',1);
        
        $article = ArticleBusiness::G()->getArticleFullInfo($id, C::PageNo(), C::PageSize());
        if (!$article) {
            C::Exit404();
            return;
        }
        $article['comments'] = C::RecordsetH($article['comments'], ['content','username']);
        $html_pager = C::PageHtml($article['comments_total']);
        $url_add_comment = C::URL('addcomment');
        C::Show(get_defined_vars(), 'article');
    }
    public function _old_reg()
    {
        C::setViewHeadFoot('user/inc_head.php', 'user/inc_foot.php');
        C::Show(get_defined_vars(), 'user/reg');
    }
    public function do_changepass()
    {
        $uid = C::SessionManager()->getCurrentUid();
        //TODO 
    }
    public function do_addcomment()
    {
        $uid = C::SessionManager()->getCurrentUid();
        UserBusiness::G()->addComment($uid, C::POST('article_id'), C::POST('content'));
        C::ExitRouteTo('article/'.C::POST('article_id'));
    }
    public function do_delcomment()
    {
        $uid = C::SessionManager()->getCurrentUid();
        UserBusiness::G()->deleteCommentByUser($uid, C::POST('id'));
        C::ExitRouteTo('');
    }

}
