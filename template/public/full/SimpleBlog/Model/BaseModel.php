<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;

use SimpleBlog\System\ProjectModel;

class BaseModel extends ProjectModel
{
    //public $table_name = null;
    public function getList(int $page = 1, int $page_size = 10)
    {
        $start = $page - 1;
        $sql = "SELECT SQL_CALC_FOUND_ROWS  * from 'TABLE' where deleted_at is null order by id desc limit $start,$page_size";
        $sql = $this->prepare($sql);
        $data = BaseModel::Db()->fetchAll($sql);
        $sql = "SELECT FOUND_ROWS()";
        $total = BaseModel::Db()->fetchColumn($sql);
        return array($data,$total);
    }
    public function get($id)
    {
        $sql = "select * from 'TABLE' where id =? and deleted_at is null";
        $sql = $this->prepare($sql);
        $ret = BaseModel::Db()->fetch($sql, $id);
        return $ret;
    }
    public function add($data)
    {
        $date = date('Y-m-d H:i:s');
        $data['created_at'] = $date;
        $data['updated_at'] = $date;
        $ret = BaseModel::Db()->insertData($this->table(), $data);
        
        return $ret;
    }
    public function update($id, $data)
    {
        $date = date('Y-m-d H:i:s');
        $data['updated_at'] = $date;
        $ret = BaseModel::Db()->updateData($this->table(), $id, $data);
        
        return $ret;
    }
    public function delete($id)
    {
        $date = date('Y-m-d H:i:s');
        $sql = "update 'TABLE' set deleted_at=? where id=? ";
        $sql = $this->prepare($sql);
        $ret = BaseModel::Db()->execute($sql, $date, $id);
        return $ret;
    }
}
