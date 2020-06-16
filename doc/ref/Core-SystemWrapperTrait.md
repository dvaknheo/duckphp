# DuckPhp\Core\SystemWrapperTrait

## 简介

替换同名系统函数保持兼容性的 Trait

## 使用于

## 方法

system_wrapper_replace(array $funcs)

    第三方替换本类提供的系统函数
system_wrapper_get_providers():array

    获得本类能替换的系统函数
_system_wrapper_replace(array $funcs)

    动态实现，第三方替换本类提供的系统函数
_system_wrapper_get_providers()

    动态实现，获得本类能替换的系统函数
system_wrapper_call_check($func)

    检查是否有相应的系统函数实现。
    
相关例子在 `DuckPhp\Core\App` 里。