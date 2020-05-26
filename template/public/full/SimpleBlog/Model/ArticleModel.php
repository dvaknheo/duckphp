<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;

class ArticleModel extends BaseModel
{
    public $table_name = "Articles";
    public function addData($title, $content)
    {
        $data = array('title' => $title,'content' => $content);
        return parent::add($data);
    }
    public function getList(int $page = 1, int $page_size = 10)
    {
        return parent::getList($page, $page_size);
    }
    public function updateData($id, $title, $content)
    {
        $data = array('title' => $title,'content' => $content);
        return parent::update($id, $data);
    }
    public function delete($id)
    {
        return parent::delete($id);
    }
}
