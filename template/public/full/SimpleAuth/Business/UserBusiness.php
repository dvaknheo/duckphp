<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Business;

use SimpleAuth\Model\UserModel;
use SimpleAuth\System\BaseBusiness;

class UserBusiness extends BaseBusiness
{
    public function register($form)
    {
        UserBusinessException::ThrowOn($form['password'] != $form['password_confirm'], '重复密码不一致');

        $username = $form['name'];
        $password = $form['password'] ?? '';
        UserBusinessException::ThrowOn($password === '', "密码为空");
        
        $flag = UserModel::G()->exsits($username);
        UserBusinessException::ThrowOn($flag, "用户已经存在");
        
        $uid = UserModel::G()->addUser($username, $password);
        UserBusinessException::ThrowOn(!$uid, "注册新用户失败");
        
        $user = UserModel::G()->getUserById($uid);
        $user = UserModel::G()->unloadPassword($user);
        
        return $user;
    }
    public function login($form)
    {
        $username = $form['name'];
        $password = $form['password'];
        $user = UserModel::G()->getUserByUsername($username);
        UserBusinessException::ThrowOn(empty($user), "用户不存在");
        UserBusinessException::ThrowOn(!empty($user['delete_at']), "用户已被禁用");
        
        $flag = UserModel::G()->verifyPassword($user, $password);
        UserBusinessException::ThrowOn(!$flag, "密码错误");
        
        $user = UserModel::G()->unloadPassword($user);
        
        return $user;
    }
    public function changePassword($uid, $password, $new_password)
    {
        UserBusinessException::ThrowOn($new_password === '', "空密码");
        $user = UserModel::G()->getUserById($uid);
        
        UserBusinessException::ThrowOn(!empty($user['delete_at']), "用户已被禁用");
        
        $flag = UserModel::G()->verifyPassword($user, $password);
        
        UserBusinessException::ThrowOn(!$flag, "旧密码错误");
        
        UserModel::G()->updatePassword($uid, $new_password);
    }
}
