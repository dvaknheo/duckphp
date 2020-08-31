<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Core\ComponentBase;
use ErrorException;

class StrictCheck extends ComponentBase
{
    const MAX_TRACE_LEVEL = 20;
    
    public $options = [
            'namespace' => 'MY',
            'namespace_controller' => 'Controller',
            'namespace_business' => '',
            'namespace_model' => '',
            'controller_base_class' => null,
            'is_debug' => false,
            'strict_check_context_class' => null,
            
            'postfix_batch_business' => 'BatchBusiness',
            'postfix_business_lib' => 'Lib',
            'postfix_ex_model' => 'ExModel',
            'postfix_model' => 'Model',

        ];
    
    protected $context_class = null;
    
    //@override
    protected function initOptions(array $options)
    {
        $this->context_class = $this->options['strict_check_context_class'];
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        $this->options['is_debug'] = isset($context->options) ? ($context->options['is_debug'] ?? $this->options['is_debug']) : $this->options['is_debug'];
        
        try {
            get_class($context)::setBeforeGetDBHandler([static::class, 'CheckStrictDB']);
        } catch (\BadMethodCallException $ex) { // @codeCoverageIgnore
            //do nothing;
        }
    }
    public static function CheckStrictDB()
    {
        return static::G()->checkStrictComponent('DB', 5, ['DuckPhp\\Core\\App','DuckPhp\\Helper\\ModelHelper']);
    }
    ///////////////////////////////////////////////////////////
    public function getCallerByLevel($level, $parent_classes_to_skip = [])
    {
        $level += 1;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, static::MAX_TRACE_LEVEL);
        $caller_class = $backtrace[$level]['class'] ?? '';
        foreach ($parent_classes_to_skip as $parent_class_to_skip) {
            if (is_subclass_of($caller_class, $parent_class_to_skip) || $parent_class_to_skip === $caller_class) {
                $caller_class = $backtrace[$level + 1]['class'] ?? '';
                return $caller_class;
            }
        }
        return $caller_class;
    }
    public function checkEnv(): bool
    {
        if (!$this->options['is_debug']) {
            return false;
        }
        if (!$this->context_class) {
            return $this->options['is_debug'];
        }
        $flag = ($this->context_class)::G()->options['is_debug'] ?? false;
        return $flag?true:false;
    }
    public function checkStrictComponent($component_name, $trace_level, $parent_classes_to_skip = [])
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class = $this->getCallerByLevel($trace_level, $parent_classes_to_skip);

        $controller_base_class = $this->options['controller_base_class'];
        
        if (self::StartWith($caller_class, $this->options['namespace_controller'])) {
            throw new ErrorException("$component_name Can not Call By Controller");
        }
        if (self::StartWith($caller_class, $this->options['namespace_business'])) {
            throw new ErrorException("$component_name Can not Call By Service");
        }
        
        if ($controller_base_class && (is_subclass_of($caller_class, $controller_base_class) || $caller_class === $controller_base_class)) {
            throw new ErrorException("$component_name Can not Call By Controller");
        }
    }
    public function checkStrictClass($class, $trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class = $this->getCallerByLevel($trace_level);
        if (self::EndWith($class, $this->options['postfix_model'])) {
            if (self::StartWith($caller_class, $this->options['namespace_business'])) {
                return;
            }
            if (self::StartWith($caller_class, $this->options['namespace_model']) &&
                self::EndWith($caller_class, $this->options['postfix_ex_model'])) {
                return;
            }
            throw new ErrorException("Model Can Only call by Service or ExModel!Caller is {$caller_class}");
        }

        if (empty($this->options['namespace_business'])) {
            return;
        }
        if (self::EndWith($class, $this->options['postfix_business_lib'])) {
            return;
        }
        if (self::EndWith($caller_class, $this->options['postfix_batch_business'])) {
            return;
        }
        if (self::StartWith($caller_class, $this->options['namespace_business'])) {
            throw new ErrorException("Service($class) Can not call by Business($caller_class)");
        }
        if (self::StartWith($caller_class, $this->options['namespace_model'])) {
            throw new ErrorException("Service($class) Can not call by Model, ($caller_class)");
        }
    }
    protected static function StartWith($str, $prefix)
    {
        return substr($str, 0, strlen($prefix)) === $prefix;
    }
    protected static function EndWith($str, $postfix)
    {
        return substr($str, -strlen($postfix)) === $postfix;
    }
}
