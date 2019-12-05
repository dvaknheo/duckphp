<?php
namespace Project\Service;

use Project\Base\BaseService;
use Project\Base\Helper\ServiceHelper as S;
use Project\Lib\UserRegisterValidator;
use Project\Model\UserModel;
use think\Validate;

class UserService extends BaseService
{
    public function hasLogin()
    {
        return false;
    }
    public function isGuest()
    {
        return true;
    }
    public function register($form)
    {
        $ret=[];
        $errors=ValidatorLib::G()->validateRegister($form);
        if(!empty($errors)){
            return [$ret,$errors];
        }
        $user=UserModel::G()->register($form);
        
        S::ThrowOn(!$user,"注册新用户失败");

        return [$user,[]];
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
        $v=new Validate($rules,$messages);
        $v->batch(true);
        $v->check($data);
        $ret=$v->getError();
        return $ret;
    }
}
