<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK, Lazy

namespace DuckPhp;

use DuckPhp\Component\Command;
use DuckPhp\Helper\AppHelperTrait;
use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Helper\ModelHelperTrait;

class DuckPhpAllInOne extends DuckPhp
{
    use ModelHelperTrait;
    use BusinessHelperTrait, ControllerHelperTrait, AppHelperTrait{
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::Options insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnGlobalEvent insteadof AppHelperTrait;
        BusinessHelperTrait::FireGlobalEvent insteadof AppHelperTrait;
        ControllerHelperTrait::header insteadof AppHelperTrait;
        ControllerHelperTrait::setcookie  insteadof AppHelperTrait;
        ControllerHelperTrait::exit  insteadof AppHelperTrait;
        ControllerHelperTrait::AdminService  insteadof BusinessHelperTrait;
        ControllerHelperTrait::UserService  insteadof BusinessHelperTrait;
    }
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
        // implements cli_command_with_app=true effect (without depending on the option)
        $this->options['cmd'] = array_merge([static::class => true], $this->options['cmd']);
        if ($this->options['cli_command_with_common']) {
            $this->options['cmd'][Command::class] = true;
        }
    }
    public function onInited(): void
    {
        if ($this->options['duckphp_all_in_one_wrap_header_foot']) {
            static::setViewHeadFoot('head', 'foot');
        }
    }
    /////////////// controller ///////////////
    public function action_index()
    {
        static::Show(get_defined_vars(), 'index');
    }
    /////////////// callable view (was DuckPhp\Ext\CallableView) ///////////////
    protected static function viewToCallback(?string $func): ?array
    {
        $func = str_replace('/', '_', 'view_' . $func);
        $ret = [static::_(), $func];
        if (!is_callable($ret)) {
            return null;
        }
        return $ret;
    }
    public static function Show($data = [], $view = '')
    {
        $callback = static::viewToCallback($view);
        if (null === $callback) {
            return parent::Show($data, $view);
        }
        $head = static::viewToCallback(static::_()->head_file ?: 'head');
        $foot = static::viewToCallback(static::_()->foot_file ?: 'foot');
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
