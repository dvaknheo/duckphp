<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Model;

use SimpleAuth\System\BaseModel;
use SimpleAuth\Helper\ModelHelper as M;

class UserModel extends BaseModel
{
    public function exsits($name)
    {
        $sql = "select count(*) as c from Users where username=?";
        $count = M::DB()->fetchColumn($sql, $name);
        return !empty($count)?true:false;
    }
    public function addUser($username, $password)
    {
        $data = [];
        $data['username'] = $username;
        $data['password'] = $this->hash($password);
        
        $id = M::DB()->insertData('Users', $data);
        return $id;
    }
    public function getUserById($id)
    {
        $sql = "select * from Users where id=?";
        $user = M::DB()->fetch($sql, $id);
        
        return $user;
    }
    public function getUserByUsername($username)
    {
        $sql = "select * from Users where username=?";
        $user = M::DB()->fetch($sql, $username);
        
        return $user;
    }
    public function verifyPassword($user, $password)
    {
        return $this->verify($password, $user['password']);
    }
    public function unloadPassword($user)
    {
        unset($user['password']);
        return $user;
    }
    public function updatePassword($uid, $password)
    {
        $password = $this->hash($password);
        $sql = "update Users set password=? where id=? limit 1";
        $ret = M::DB()->execute($sql, $password, $uid);
        return $ret;
    }
    ////
    protected function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    protected function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
