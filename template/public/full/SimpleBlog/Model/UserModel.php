<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Model;

use SimpleBlog\Helper\ModelHelper as M;

class UserModel extends BaseModel
{
    public $table_name = "Users";
    public function getUserByName($username)
    {
        $sql = "select * from Users where username=?";
        $ret = M::DB()->fetch($sql, $username);
        return $ret;
    }
    public function getUserDirect($id)
    {
        $sql = "select * from Users where id=?";
        $ret = M::DB()->fetch($sql, $id);
        return $ret;
    }
    public function getList(int $page = 1, int $page_size = 10)
    {
        return parent::getList($page, $page_size);
    }
    public function addUser($username, $pass)
    {
        $password = password_hash($pass, PASSWORD_BCRYPT);
        $date = date('Y-m-d H:i:s');
        $data = array('username' => $username,'password' => $password,'created_at' => $date);
        $ret = M::DB()->insert('Users', $data);
        return $ret;
    }
    public function changePass($user_id, $password)
    {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $data = array('password' => $password);
        //$ret=M::DB()->update('Users',$id,$data,'id');
        return $ret;
    }
    public function checkPass($password, $old)
    {
        return password_verify($password, $old);
    }
}
