# DuckPhp\Ext\StrictCheck

## 简介
用于 严格使用 Db 等情况。使得在调试状态下，不能在 Controller 里 使用 M::Db();等
不能在 service 里调用 service

## 选项
全部选项
        'namespace' => '',
命名空间

        'namespace_controller' => 'Controller',
控制器命名空间

        'namespace_model' => '',
模型命名空间

        'controller_base_class' => null,
控制器基类

        'is_debug' => false,
是否调试模式

        'namespace_business' => '',
业务类命名空间

        'strict_check_context_class' => null,
上下文类

        'strict_check_enable' => true,
严格检查模式开启（本类是否生效

        'postfix_batch_business' => 'BatchBusiness',
跳过批量业务的类

        'postfix_business_lib' => 'Lib',
跳过非业务类

        'postfix_ex_model' => 'ExModel',
混合模型后缀

        'postfix_model' => 'Model',
模型后缀
## 扩充方法

### 方法

public function init($options=[], $context=null)
public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip=[])
public function checkStrictModel($trace_level)
public function checkStrictService($service_class, $trace_level)
protected function getCallerByLevel($level, $parent_classes_to_skip=[])
protected function checkEnv(): bool

## 详解

没文档，先看单元覆盖测试吧。


    public function __construct()
    public function init(array $options, object $context = null)
    protected function initContext($options = [], $context = null)
    public static function CheckStrictDB()
    public function getCallerByLevel($level, $parent_classes_to_skip = [])
    public function checkEnv(): bool
    public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip = [])
    public function checkStrictModel($trace_level)
    public function checkStrictService($service_class, $trace_level)
    

这个例子禁止了Controller 里调用 DB ，禁止调用 Model


```
class StrictCheckTestMain extends BaseController
{
    public function index()
    {
    }
    public function foo()
    {
        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "111111111111".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "2222222222222222222 Catch S::DB ".$ex->getMessage().PHP_EOL;
        }
        try{
            (new t)->foo();
        }catch(\Throwable $ex){
            echo "33333333333333333333333".$ex->getMessage().PHP_EOL;
        }
        
        try{
            FakeModel::G()->foo();
        }catch(\Throwable $ex){
            echo "4444444444444444444444444".$ex->getMessage().PHP_EOL;
        }

        try{
            FakeService::G()->callService();
        }catch(\Throwable $ex){
            echo "55555555555555555555555555555FakeService::G()->callService()".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->modelCallService();
        }catch(\Throwable $ex){
            echo "sssssssss modelCallService sssssssssssssssssss".$ex->getMessage().PHP_EOL;
        }
        try{
            FakeService::G()->callDB();
        }catch(\Throwable $ex){
            echo "sssssssss modelCallService sssssssssssssssssss".$ex->getMessage().PHP_EOL;
        }
        
        
        try{
            DuckPhp::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz".$ex->getMessage().PHP_EOL;
        }
        try{
            M::DB()->fetch("select 1+1 as t");
        }catch(\Throwable $ex){
            echo "zzzzzzzzzzzzz Catch S::DB ".$ex->getMessage().PHP_EOL;
        }
        try{
            (new BaseController2)->foo();
        }catch(\Throwable $ex){
            echo "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa".$ex->getMessage().PHP_EOL;
        }
        FakeService::G()->normal();
        echo "============================\n";
        FakeBatchService::G()->foo();

    }
}
```