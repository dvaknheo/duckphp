<?php
namespace Project\Service;

use Project\Base\BaseService;

//use think\Validate;

class ValidateLib extends BaseService
{
    public function validate($data, $rules, $message)
    {
        if(class_exists(\think\Validate::class)){
            $v=new Validate($data, $rules, $messages);
            $v->batch(true);
            $v->check($data);
            return $v;
        }
        return $this->doValidate($data, $rules,$message);
    }
    protected function doValidate($data, $rules,$message)
    {
        $this->data=$data;
        
        $ret=[];
        foreach ($rules as $name => $rule) {
            $info=$this->validateByRule($name, $rule,$message,$data);
            foreach($info as $v){
                $ret[$name]=str_replace(':attribute',$name,$message[$v]);
                break;
            }
        }
        return $ret;
    }
    protected function validateByRule($name, $rule,$message,$data)
    {
        $ret = [];
        $value=$data[$name]??null;
        $subRule=explode('|',$rule);
        foreach($subRule as $v){
            $args=explode(':',$v);
            $validator=array_shift($args);
            $a=$data[$name]??null;
            array_unshift($args,$a);

            $method='validate_'.$validator;
            $flag=true;
            if(method_exists($this,$method)){
                $flag=call_user_func_array([$this,$method],$args);
            }
            if($flag){
                continue;
            }
            $ret[]="$name.$validator";
        }
        return $ret;
    }
    protected function validate_require($value)
    {
        return !empty($value)?true:false;
    }
    protected function validate_email($value)
    {
        return filter_var($value,FILTER_VALIDATE_EMAIL)?true:false;
    }
    protected function validate_max($value,$number)
    {
        return strlen($value)<=$number?true:false;
    }

    protected function validate_min($value, $number)
    {
        return strlen($value)>=$number?true:false;
    }
    protected function validate_confirm($value,$key)
    {
        if(!isset($this->data[$key])){
            return false;
        }
        return $value===$this->data[$key]?true:false;
    }
/*
$rules =   [
            'name'          => 'require|max:255',
            'email'         => 'require|email|min:8',
            'password'      => 'require|min:8|confirm:password_confirmation',
        ];
*/
}