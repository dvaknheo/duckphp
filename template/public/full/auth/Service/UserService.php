<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseService;
use UserSystemDemo\Base\Helper\ServiceHelper as S;

use UserSystemDemo\Model\UserModel;

class UserService extends BaseService
{
    public function register($form)
    {
        $username = $form['name'];
        $password = $form['password'] ?? '';
        UserServiceException::ThrowOn($password === '', "密码为空");
        $flag = UserModel::G()->exsits($username);
        UserServiceException::ThrowOn($flag, "用户已经存在");
        
        $uid = UserModel::G()->addUser($username, $password);
        UserServiceException::ThrowOn(!$uid, "注册新用户失败");
        
        $user = UserModel::G()->getUserById($uid);
        $user = UserModel::G()->unloadPassword($user);
        
        return $user;
    }
    public function login($form)
    {
        $username = $form['name'];
        $password = $form['password'];
        $user = UserModel::G()->getUserByUsername($username);
        UserServiceException::ThrowOn(empty($user), "用户不存在");
        UserServiceException::ThrowOn(!empty($user['deletee_at']), "用户已被禁用");
        $flag = UserModel::G()->verifyPassword($user, $password);
        UserServiceException::ThrowOn(!$flag, "密码错误");
        
        $user = UserModel::G()->unloadPassword($user);
        
        return $user;
    }
    public function changePassword($uid, $password, $new_password)
    {
        UserServiceException::ThrowOn($new_password === '', "空密码");
        $user = UserModel::G()->getUserById($uid);
        UserServiceException::ThrowOn(!empty($user['deletee_at']), "用户已被禁用");
        $flag = UserModel::G()->verifyPassword($user, $password);
        UserServiceException::ThrowOn(!$flag, "旧密码错误");
        
        UserModel::G()->updatePassword($uid, $new_password);
    }
}
