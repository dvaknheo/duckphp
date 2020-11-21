<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Model\ArticleModel;
use SimpleBlog\Model\CommentExModel;

class ArticleBusiness extends BaseBusiness
{
    public function getRecentArticle()
    {
        $ret = ArticleModel::G()->getList(1, 10);
        return $ret;
    }
    public function getArticleList($page = 1, $page_size = 10)
    {
        $ret = ArticleModel::G()->getList($page, $page_size);
        return $ret;
    }
    public function getArticle($id, $comment_pge = 1, $page_size = 10)
    {
        $ret = ArticleModel::G()->get($id);
        if (!$ret) {
            return array();
        }
        $ret['comments'] = CommentExModel::G()->getListByArticle($id, $comment_pge);
        
        return $ret;
    }
    public function getArticleFullInfo($id, $page = 1, $page_size = 10)
    {
        $art = ArticleModel::G()->get($id);
        list($comments, $total) = CommentExModel::G()->getListByArticle($id, $page, $page_size);
        $art['comments'] = $comments;
        $art['comments_total'] = $total;
        return $art;
    }
    public function addArctile()
    {
        //TODO
    }
}
