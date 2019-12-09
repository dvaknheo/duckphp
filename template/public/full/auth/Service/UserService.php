<?php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseService;
use UserSystemDemo\Base\Helper\ServiceHelper as S;

use UserSystemDemo\Model\UserModel;

class UserService extends BaseService
{
    public function register($form)
    {
        $user=UserModel::G()->register($form);
        S::ThrowOn(empty($user),"注册新用户失败");
        return $user;
    }
    public function login($form)
    {
        $user=UserModel::G()->login($form);
        return $user;
    }
}
