<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Ext;

use DuckPhp\Component\DbManager;
use DuckPhp\Core\ComponentBase;
use ErrorException;

class StrictCheck extends ComponentBase
{
    const MAX_TRACE_LEVEL = 20;
    
    public $options = [
        'namespace' => '',
        'namespace_controller' => 'Controller',
        'namespace_business' => '',
        'namespace_model' => '',
        'controller_base_class' => null,
        'is_debug' => false,
        'strict_check_context_class' => null,
        
        'strict_check_enable' => true,
        
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
        if (!defined('__SINGLETONEX_REPALACER')) {
            define('__SINGLETONEX_REPALACER', static::class . '::SingletonExReplacer');//$callback = __SINGLETONEX_REPALACER;
        }
    }
    //@override
    protected function initContext(object $context)
    {
        $this->context_class = get_class($context);
        try {
            DbManager::G()->setBeforeGetDbHandler([static::class, 'CheckStrictDb']);
        } catch (\BadMethodCallException $ex) { // @codeCoverageIgnore
            //do nothing;
        }
    }
    public static function CheckStrictDb()
    {
        $magic_number = 5;
        return static::G()->checkStrictComponent('Db', $magic_number, ['DuckPhp\\Core\\App',"DuckPhp\\Helper\\ModelHelper"]);
    }
    //////
    //*
    protected static $classes;
    public static function SingletonExReplacer($class, $object)
    {
        if ($class !== static::class) {
            $c = (static::$classes[self::class]) ?? new static();
            $c->check_strict_class($class);
        }
        if (isset($object)) {
            static::$classes[$class] = $object;
            return static::$classes[$class];
        }
        if (isset(static::$classes[$class])) {
            return static::$classes[$class];
        }
        
        $ref = new \ReflectionClass($class);
        $prop = $ref->getProperty('_instances'); //OK Get It
        $prop->setAccessible(true);
        $array = $prop->getValue();
        if (!empty($array[$class])) {
            static::$classes[$class] = $array[$class];
        } else {
            static::$classes[$class] = new $class;
        }
        return static::$classes[$class];
    }
    //*/
    
    ///////////////////////////////////////////////////////////

    protected function hit_class($caller_class, $parent_classes_to_skip)
    {
        foreach ($parent_classes_to_skip as $parent_class_to_skip) {
            if (is_subclass_of($caller_class, $parent_class_to_skip) || $parent_class_to_skip === $caller_class) {
                return true;
            }
        }
        return false;
    }
    public function getCallerByLevel($level, $parent_classes_to_skip = [])
    {
        $level += 1;
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, static::MAX_TRACE_LEVEL);
        for ($i = $level;$i < static::MAX_TRACE_LEVEL;$i++) {
            $caller_class = $backtrace[$i]['class'] ?? '';
            if (!$this->hit_class($caller_class, $parent_classes_to_skip)) {
                return $caller_class;
            }
        }
        return ''; // @codeCoverageIgnore
    }
    public function checkEnv(): bool
    {
        if (!$this->options['is_debug']) {
            return false;
        }
        return true;
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
            throw new ErrorException("$component_name Can not Call By Business");
        }
        
        if ($controller_base_class && (is_subclass_of($caller_class, $controller_base_class) || $caller_class === $controller_base_class)) {
            throw new ErrorException("$component_name Can not Call By Controller");
        }
    }
    public function check_strict_class($class)
    {
        if (!$this->checkEnv()) {
            return;
        }

        if (!empty($this->options['namespace_model']) && self::StartWith($class, $this->options['namespace_model'])) {
            $caller_class = $this->getCallerByLevel(3);
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
        }
        if (!empty($this->options['namespace_business']) && self::StartWith($class, $this->options['namespace_business'])) {
            $caller_class = $this->getCallerByLevel(3);
            if (self::EndWith($class, $this->options['postfix_business_lib'])) {
                return;
            }
            if (self::EndWith($caller_class, $this->options['postfix_batch_business'])) {
                return;
            }
            if (self::StartWith($caller_class, $this->options['namespace_business'])) {
                throw new ErrorException("Business($class) Can not call by Business($caller_class)");
            }
            if (self::StartWith($caller_class, $this->options['namespace_model'])) {
                throw new ErrorException("Business($class) Can not call by Model, ($caller_class)");
            }
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
