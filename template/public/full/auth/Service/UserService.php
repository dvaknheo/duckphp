<?php
namespace Project\Service;

use Project\Base\BaseService;
use Project\Base\Helper\ServiceHelper as S;
use Project\Lib\UserRegisterValidator;
use Project\Model\UserModel;

class UserService extends BaseService
{
    public function register($form)
    {
        $user=UserModel::G()->register($form);
        S::ThrowOn(empty($user),"注册新用户失败");
        return $user;
    }
    public function validateRegister(array $data)
    {
        $rules =   [
            'name'          => 'require|max:255',
            'email'         => 'require|email|min:8',
            'password'      => 'require|min:8|confirm:password_confirmation',
        ];
        $messages=[
            'name.require'      => 'The :attribute field is required.',
            'name.max'          => 'The :attribute may not be greater than :max characters.',
            'email.require'     => 'The :attribute field is required.',
            'email.email'       => 'The :attribute must be a valid email address.',
            'email.min'         => 'The :attribute must be at least  characters.',
            'password.require'  => 'The :attribute field is required.',
            'password.min'      => 'The :attribute must be at least 8 characters.',
            'password.confirm'      => 'The :attribute confirmation does not match.',
        ];
        $ret = ValidateLib::G()->validate($data,$rules,$messages);
        if(empty($ret)){
            //UserModel::G()->login($data);
        }
        return $ret;
    }
    public function login($form)
    {
        $user=UserModel::G()->login($form);
        return $user;
    }
}
