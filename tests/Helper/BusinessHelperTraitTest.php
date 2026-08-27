<?php
namespace tests\DuckPhp\Helper;

use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\DuckPhp;

class BusinessHelperTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testAll()
    {
        \LibCoverage\LibCoverage::Begin(BusinessHelperTrait::class);
        

        $key='key';
        $file_basename='config';
        
        BusinessHelper::Setting($key);
        try{
        BusinessHelper::Config($file_basename, $key, null);
        }catch(\Exception $ex){}
        try{
        BusinessHelper::Cache(new \stdClass);
        }catch(\Exception $ex){}
        
        BusinessHelper::XpCall(function(){return "abc";});
        BusinessHelper::XpCall(function(){ throw new \Exception('ex'); });
        
        try{
        
            BusinessHelper::OnGlobalEvent("test",function(){});
            BusinessHelper::FireGlobalEvent("test",1,2,3);
        }catch(\Exception $ex){
        }
        try{
            BusinessHelper::BusinessThrowOn(false, "haha",1);
        }catch(\Throwable $ex){}
        
        try{
            BusinessHelper::AdminService();
        }catch(\Throwable $ex){}
        try{
            BusinessHelper::UserService();
        }catch(\Throwable $ex){}
        // Validator 相关方法
        $rules = ['name' => 'required|minLen:1|maxLen:12'];
        BusinessHelper::ValidatorValid(['name' => 'ok'], $rules);
        try {
            BusinessHelper::ValidatorCheck(['name' => ''], $rules);
        } catch (\Throwable $ex) {
        }
        BusinessHelper::ValidatorFilter(['name' => 'ok', 'x' => 1], $rules);
        $v = BusinessHelper::Validator();
        $v->setRules(['a' => 'required']);
        BusinessHelper::Validator($v);
        try {
            BusinessHelper::ValidatorCheck(['name' => 'ss'], $rules);
        } catch (\Throwable $ex) {
        }

        DuckPhp::_()->init(['my_test_key' => 'my_value']);
        BusinessHelper::Options('my_test_key');
        BusinessHelper::Options('my_test_key', 'default');
        BusinessHelper::Options('no_such_key', 'default');
        BusinessHelper::PathOfRuntime();
        BusinessHelper::PathOfProject();

        
        \LibCoverage\LibCoverage::End();
    }
}
class BusinessHelper
{
    use BusinessHelperTrait;
}