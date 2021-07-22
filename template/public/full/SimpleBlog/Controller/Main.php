<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Controller;

use SimpleBlog\Business\ArticleBusiness;
use SimpleBlog\Business\UserBusiness;
use SimpleBlog\ControllerEx\SessionManager;
use SimpleBlog\System\ProjectController  as C;

class Main 
{
    public function __construct()
    {
    }
    public function index()
    {
        $url_reg = __url('register');
        $url_login = __url('login');
        $url_logout = __url('logout');
        $url_admin = __url('admin/index');

        $user = SessionManager::G()->getCurrentUser();
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
        $url_add_comment = __url('addcomment');
        C::Show(get_defined_vars(), 'article');
    }
    public function _old_reg()
    {
        C::setViewHeadFoot('user/inc_head.php', 'user/inc_foot.php');
        C::Show(get_defined_vars(), 'user/reg');
    }
    public function _old_do_changepass()
    {
        $uid = SessionManager::G()->getCurrentUid();
        //TODO 
    }
    public function do_addcomment()
    {
        $uid = SessionManager::G()->getCurrentUid();
        UserBusiness::G()->addComment($uid, C::POST('article_id'), C::POST('content'));
        C::ExitRouteTo('article/'.C::POST('article_id'));
    }
    public function do_delcomment()
    {
        $uid = SessionManager::G()->getCurrentUid();
        UserBusiness::G()->deleteCommentByUser($uid, C::POST('id'));
        C::ExitRouteTo('');
    }

}
