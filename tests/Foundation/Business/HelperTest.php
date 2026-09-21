<?php
namespace tests\DuckPhp\Foundation\Business;

use DuckPhp\Foundation\Business\Helper;
use DuckPhp\DuckPhp;

class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(Helper::class);
        

        $key='key';
        $file_basename='config';
        
        Helper::Setting($key);
        try{
        Helper::Config($file_basename, $key, null);
        }catch(\Exception $ex){}
        try{
        Helper::Cache(new \stdClass);
        }catch(\Exception $ex){}
        
        Helper::XpCall(function(){return "abc";});
        Helper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
        
            Helper::OnGlobalEvent("test",function(){});
            Helper::FireGlobalEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        try{
            Helper::BusinessThrowOn(false, "haha",1);
            Helper::ThrowOn(false, "haha",1);
        }catch(\Throwable $ex){}
        
        try{
            Helper::AdminService();
        }catch(\Throwable $ex){}
        try{
            Helper::UserService();
        }catch(\Throwable $ex){}
        // Validator 相关方法
        $rules = ['name' => 'required|minLen:1|maxLen:12'];
        Helper::ValidatorValid(['name' => 'ok'], $rules);
        try {
            Helper::ValidatorCheck(['name' => ''], $rules);
        } catch (\Throwable $ex) {
        }
        Helper::ValidatorFilter(['name' => 'ok', 'x' => 1], $rules);
        $v = Helper::Validator();
        $v->setRules(['a' => 'required']);
        Helper::Validator($v);
        try {
            Helper::ValidatorCheck(['name' => 'ss'], $rules);
        } catch (\Throwable $ex) {
        }

        DuckPhp::_()->init(['my_test_key' => 'my_value']);
        Helper::AppOptions('my_test_key');
        Helper::AppOptions('my_test_key', 'default');
        Helper::AppOptions('no_such_key', 'default');
        Helper::PathOfRuntime();
        Helper::PathOfProject();

        
        \LibCoverage\LibCoverage::End();
    }
}
