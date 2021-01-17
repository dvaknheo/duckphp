# DuckPhp\Core\SystemWrapperTrait
[toc]

## 简介

替换同名系统函数保持兼容性的 Trait

## 公开方法
public static function system_wrapper_replace(array $funcs)
    
    第三方替换本类提供的系统函数
    
public static function system_wrapper_get_providers():array

    获得本类能替换的系统函数，返回数组    
## 内部方法

system_wrapper_replace(array $funcs)

system_wrapper_get_providers():array

_system_wrapper_replace(array $funcs)

    动态实现，第三方替换本类提供的系统函数
_system_wrapper_get_providers()

    动态实现，获得本类能替换的系统函数
protected function system_wrapper_call_check($func)

    检查是否有相应的系统函数实现。
protected function system_wrapper_call($func, $input_args)
    
    相关例子在 `DuckPhp\Core\App` 里。
## 说明

配合属性 protected $system_handlers=[]; 和 G() 方法用。

