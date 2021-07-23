<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Model;

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'Users';
    }
    public function exsits($name)
    {
        $sql = "select count(*) as c from 'TABLE' where username=?";
        
        $count = BaseModel::Db()->fetchColumn($this->prepare($sql), $name);
        return !empty($count)?true:false;
    }
    public function addUser($username, $password)
    {
        $data = [];
        $data['username'] = $username;
        $data['password'] = $this->hash($password);
        $id = BaseModel::Db()->insertData($this->table(), $data);
        return $id;
    }
    public function getUserById($id)
    {
        $sql = "select * from 'TABLE' where id=?";
        $user = BaseModel::Db()->fetch($this->prepare($sql), $id);
        
        return $user;
    }
    public function getUserByUsername($username)
    {
        $sql = "select * from 'TABLE' where username=?";
        $user = BaseModel::Db()->fetch($this->prepare($sql), $username);
        
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
        $sql = "update 'TABLE' set password=? where id=? limit 1";
        $ret = BaseModel::Db()->execute($this->prepare($sql), $password, $uid);
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
