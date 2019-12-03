<?php
namespace Project\Model;

use Project\Base\BaseModel;
use Project\Base\ModelHelper as M;

class UserModel extends BaseModel
{
    public function register($form):int
    {
        $form['password']=$this->hash($form['password']);
        $ret=M::DB()->insertData('users', $form);
        return $ret?$ret:0;
    }
    public function exists($email):bool
    {
        $sql="select count(*) as c from users where email=?";
        $ret=M::DB()->fetchColumn($sql,$email);
        return $ret;
    }
    protected function hash($password)
    {
        return password_hash($password);
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
    public function verifyPassword($user,$hash)
    {
        $ret=$this->verify($user['password'],$hash);
        return $ret;
    }
}
