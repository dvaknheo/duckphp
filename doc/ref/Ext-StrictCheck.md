# Ext\StrictCheck

## 简介
用于 严格使用 DB 等情况。使得在调试状态下，不能在 Controller 里 使用 M::DB();等
## 选项
    'namespace' => '',
    'namespace_controller' => '',
    'namespace_service' => '',
    'namespace_model' => '',
    'controller_base_class' => '',
    'is_debug' => true,
    'app_class' => null,
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