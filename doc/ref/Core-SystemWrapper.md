# Core\SystemWrapper

## 简介
替换同名系统函数保持兼容性的 Trait
## 选项

## 公开方法
    public static function system_wrapper_replace(array $funcs)
    public static function system_wrapper_get_providers():array

## 详解

    public static function system_wrapper_replace(array $funcs)
    public static function system_wrapper_get_providers():array
    public function _system_wrapper_replace(array $funcs)
    public function _system_wrapper_get_providers()
    protected function system_wrapper_call_check($func)