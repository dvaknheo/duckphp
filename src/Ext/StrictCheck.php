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
            'namespace_service' => '',
            'namespace_model' => '',
            'controller_base_class' => null,
            'is_debug' => false,
            'strict_check_context_class' => null,
			
            'postfix_batch_service' => 'BatchService',
            'postfix_lib_service' => 'BatchService',
			'postfix_ex_model' => 'ExModel',

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
        return static::G()->checkStrictComponent('DB', 4, ['DuckPhp\\Core\\App','DuckPhp\\Helper\\ModelHelper']);
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
        
        if (substr($caller_class, 0, strlen($this->options['namespace_controller'])) === $this->options['namespace_controller']) {
            throw new ErrorException("$component_name Can not Call By Controller");
        }
        if ($controller_base_class && (is_subclass_of($caller_class, $this->options['controller_base_class']) || $caller_class === $this->options['controller_base_class'])) {
            throw new ErrorException("$component_name Can not Call By Controller!");
        }
        if (self::EndWith($caller_class, $this->options['namespace_service'])) {
            throw new ErrorException("$component_name Can not Call By Service");
        }
    }
    public function checkStrictModel($trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
        $caller_class = $this->getCallerByLevel($trace_level);
        
        if (self::StartWith($caller_class, $this->options['namespace_service'])) {
            return;
        }
        if (self::StartWith($caller_class, $this->options['namespace_model']) &&
            self::EndWith($caller_class, $this->options['postfix_ex_model'])) {
            return;
        }
        throw new ErrorException("Model Can Only call by Service or ExModel!Caller is {$caller_class}");
    }
    public function checkStrictService($service_class, $trace_level)
    {
        if (!$this->checkEnv()) {
            return;
        }
		if (empty($this->options['namespace_service'])) {
            return;
        }
		
        $caller_class = $this->getCallerByLevel($trace_level);
		
        if (self::EndWith($caller_class, $this->options['postfix_batch_service'])) {
            return;
        }
        if (self::EndWith($service_class, $this->options['postfix_lib_service'])) {
            return;
        }
        if (self::EndWith($caller_class, $this->options['namespace_service'])) {
            throw new ErrorException("Service($service_class) Can not call Service($caller_class)");
        }
        if (self::EndWith($caller_class, $this->options['namespace_model'])) {
            throw new ErrorException("Service Can not call by Model, ($caller_class)");
        }
    }
	protected static function StartWith($str,$prefix)
	{
		return substr($str, 0, strlen($prefix)) === $prefix;
	}
	protected static function EndWith($str,$postfix)
	{
		return substr($str, -strlen($postfix)) === $postfix;
	}
}
