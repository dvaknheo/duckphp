<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Model;

class UserModel extends Base
{
    public function exsits($name)
    {
        $sql = "select count(*) as c from 'TABLE' where username=?";
        $sql = $this->prepare($sql);
        
        $count = Base::Db()->fetchColumn($sql, $name);
        return !empty($count)?true:false;
    }
    public function addUser($username, $password)
    {
        $data = [];
        $data['username'] = $username;
        $data['password'] = $this->hash($password);
        
        $id = Base::DB()->insertData($this->table(), $data);
        return $id;
    }
    public function getUserById($id)
    {
        $sql = "select * from Users where id=?";
        $user = Base::DB()->fetch($sql, $id);
        
        return $user;
    }
    public function getUserByUsername($username)
    {
        $sql = "select * from Users where username=?";
        $user = Base::DB()->fetch($sql, $username);
        
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
        $ret = Base::DB()->execute($sql, $password, $uid);
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
