<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;


class CommentModel extends BaseModel
{
    public $table_name = "Comments";
    public function getListByArticle($article_id, int $page = 1, int $page_size = 10)
    {
        $start = $page - 1;
        $sql = "SELECT SQL_CALC_FOUND_ROWS  * from 'TABLE' where article_id=? and deleted_at is null order by id desc limit $start,$page_size";
        $sql = $this->prepare($sql);
        $data = BaseModel::Db()->fetchAll($sql, $article_id);
        $sql = "SELECT FOUND_ROWS()";
        $total = BaseModel::Db()->fetchColumn($sql);
        return array($data,$total);
    }
    public function getList(int $page = 1, int $page_size = 10)
    {
        return parent::getList($page, $page_size);
    }
    public function addData($user_id, $article_id, $content)
    {
        $data = array('user_id' => $user_id,'article_id' => $article_id,'content' => $content);
        return parent::add($data);
    }
    public function delete($id)
    {
        return parent::delete($id);
    }
}
