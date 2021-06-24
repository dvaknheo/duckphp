<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;

use SimpleBlog\Helper\ModelHelper as M;

//开发人员备注，这个模型不是和数据库表的对应。
class CommentExModel extends BaseModel
{
    public $table_name = "Comments";
    public $table_name_user = "Users";
    public function getListByArticle($article_id, int $page = 1, int $page_size = 10)
    {
        $start = $page - 1;
        $sql = "SELECT SQL_CALC_FOUND_ROWS  a.*,b.id as user_id,b.username from {$this->table_name} as a left join {$this->table_name_user} as b on  a.user_id=b.id where a.article_id=? and a.deleted_at is null order by a.id limit $start,$page_size";
        $data = M::DB()->fetchAll($sql, $article_id);
        $sql = "SELECT FOUND_ROWS()";
        $total = M::DB()->fetchColumn($sql);
        return array($data,$total);
    }
    //获取一个评论 ，连同人名。
}
