<?php
namespace UserSystemDemo\Model;

use UserSystemDemo\Base\BaseModel;
use UserSystemDemo\Base\Helper\ModelHelper as M;

class UserModel extends BaseModel
{
    public function register($form)
    {
        $data=[];
        $data['name']=$form['name'];
        $data['password']=$this->hash($form['password']);
        
        $sql="select count(*) as c from Users where username=?";
        $count=M::DB()->fetchColumn($sql,$form['name']);
        M::ThrowOn($count,'用户已经存在');
        
        $id=M::DB()->insertData('users', $data);
        M::ThrowOn(!$id,'添加用户失败');
        
        $sql="select * from Users where id=?";
        $user=M::DB()->fetch($sql,$id);
        unset($user['password']);
        
        return $user;
    }
    public function login($form)
    {
        $sql="select * from Users where username=?";
        $user=M::DB()->fetch($sql,$form['name']);
        
        M::ThrowOn(!$user,'没有这个用户');
        $flag=$this->verify($form['password'],$user['password']);
        M::ThrowOn(!$flag,'验证失败');
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
