<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;
use DuckPhp\Foundation\Helper;

class DuckPhpAllInOne extends DuckPhp
{
    public static function __callStatic($method, $args)
    {
        $classes = [
            \DuckPhp\Foundation\System\Helper::class,
            \DuckPhp\Foundation\Controller\Helper::class,
            \DuckPhp\Foundation\Business\Helper::class,
            \DuckPhp\Foundation\Model\Helper::class,
        ];
        foreach($classes as $class){
            if (method_exists($class, $method)) {
                return $class::$method(...$args);
            }
        }
        trigger_error("Call to undefined method " . static::class . "::$method()", E_USER_ERROR);
    }
    protected $head_view = 'head';
    protected $foot_view = 'foot';
    protected function embedMe(): void
    {
        // embed welcome page to this class
        $path = explode('\\', static::class);
        $short_class = array_pop($path);
        $namespace = implode("\\", $path);
        $ext_options = [
            'namespace_controller' => "\\".$namespace,
            'name' => '@',
            'controller_welcome_class' => $short_class ,
            'controller_class_postfix' => '',
            'controller_method_prefix' => 'action_',
            'cli_enable' => true,
            'path_info_compact_enable' => true,
            'duckphp_all_in_one_wrap_header_foot' => true,
        ];

        $this->options = array_merge($this->options, $ext_options);
    }
    public function __construct()
    {
        $this->embedMe();
        parent::__construct();
    }
    protected function onPrepare(): void
    {
        parent::onPrepare();
        // implements cli_command_with_app=true effect (without depending on the option)
        $this->options['cmd'] = array_merge([static::class => true], $this->options['cmd']);
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'][Command::class] = true;
        }
    }
    public function onInited(): void
    {
        if ($this->options['duckphp_all_in_one_wrap_header_foot']) {
            $this->head_view = 'head';
            $this->foot_view = 'foot';
        }
    }
    /////////////// controller ///////////////
    public function action_index()
    {
        $this->_Show(get_defined_vars(), 'index');
    }
    /////////////// callable view (was DuckPhp\Ext\CallableView) ///////////////
    protected function viewToCallback(?string $func): ?\Closure
    {
        $func = str_replace('/', '_', 'view_' . $func);
        $ret = [$this, $func];
        if (!is_callable($ret)) {
            return null;
        }
        return \Closure::fromCallable($ret);
    }
    public function _Show(array $data, string $view = '')
    {
        $callback = $this->viewToCallback($view);
        if (null === $callback) {
            return parent::_Show($data, $view);
        }
        $head = $this->viewToCallback($this->head_view ?: 'head');
        $foot = $this->viewToCallback($this->foot_view ?: 'foot');
        if (null !== $head) {
            ($head)($data);
        }
        ($callback)($data);
        if (null !== $foot) {
            ($foot)($data);
        }
    }
    ///////////////
    public function view_head($data)
    {
        echo <<<EOT
<html><head><meta charset="UTF-8"><title>demo</title></head><body>
EOT;
    }
    public function view_index($data)
    {
        echo  static::class. " main page work at".DATE(DATE_ATOM);
    }
    public function view_foot($data)
    {
        echo <<<EOT
</body></html>
EOT;
    }
}
