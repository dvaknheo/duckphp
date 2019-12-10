<?php
namespace UserSystemDemo\Model;

use UserSystemDemo\Base\BaseModel;
use UserSystemDemo\Base\Helper\ModelHelper as M;

class UserModel extends BaseModel
{
    public function exsits($name)
    {
        $sql="select count(*) as c from Users where username=?";
        $count=M::DB()->fetchColumn($sql,$form['name']);
        return !empty($count)?true:false;
    }
    public function addUser($username,$password)
    {
        $data=[];
        $data['username']=$username;
        $data['password']=$this->hash($password);
        
        $id=M::DB()->insertData('Users', $data);
        return $id;
    }
    public function getUserById($id)
    {
        $sql="select * from Users where id=?";
        $user=M::DB()->fetch($sql,$id);
        
        return $user;
    }
    public function getUserByUsername($username)
    {
        $sql="select * from Users where username=?";
        $user=M::DB()->fetch($sql,$username);
        
        return $user;
    }
    public function verifyPassword($user,$password)
    {
        return $this->verify($password,$user['password']);
    }
    public function unloadPassword($user)
    {
        unset($user['password']);
        return $user;
    }
    ////
    protected function hash($password)
    {
        return password_hash($password,PASSWORD_DEFAULT);
    }
    protected function verify($password,$hash)
    {
        return password_verify($password,$hash);
    }
}
