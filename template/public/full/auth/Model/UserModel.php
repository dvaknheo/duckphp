<?php
namespace Project\Model;

use Project\Base\BaseModel;
use Project\Base\Helper\ModelHelper as M;

class UserModel extends BaseModel
{
    public function register($form)
    {
        $data=[];
        $data['name']=$form['name'];
        $data['email']=$form['email'];
        $data['password']=$this->hash($form['password']);
        
        if($this->exists($data['email'])){
            return [];
        }
        $id=M::DB()->insertData('users', $data);
        if(!$id){
            return [];
        }
        $sql="select * from users where id=?";
        $user=M::DB()->fetch($sql,$id);
        unset($user['password']);
        return $user;
    }
    public function login($form)
    {
        $email=$form['email'];
        $user=$this->getUserByEmail($email);
        M::ThrowOn(!$user,'没有这个用户');
        $flag=$this->verify($form['password'],$user['password']);
        M::ThrowOn(!$flag,'验证失败');
        unset($user['password']);
        return $user;
    }
    public function exists($email):bool
    {
        $sql="select count(*) as c from users where email=?";
        $ret=M::DB()->fetchColumn($sql,$email);
        return $ret;
    }
    protected function hash($password)
    {
        return password_hash($password,PASSWORD_DEFAULT);
    }
    protected function verify($password,$hash)
    {
        return password_verify($password,$hash);
    }
    ////
    public function getUserByEmail($email)
    {
        $sql="select * from users where email=? ";
        $ret=M::DB()->fetch($sql,$email);
        if(!$ret){
            return [];
        }
        return $ret;
    }
}
