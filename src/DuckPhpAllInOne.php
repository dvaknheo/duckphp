<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
//dvaknheo@github.com
//OK，Lazy

namespace DuckPhp;

use DuckPhp\Ext\CallableView;
use DuckPhp\Helper\AppHelperTrait;
use DuckPhp\Helper\BusinessHelperTrait;
use DuckPhp\Helper\ControllerHelperTrait;
use DuckPhp\Helper\ModelHelperTrait;

class DuckPhpAllInOne extends DuckPhp
{
    use ModelHelperTrait;
    use BusinessHelperTrait, ControllerHelperTrait, AppHelperTrait{
        BusinessHelperTrait::Setting insteadof ControllerHelperTrait;
        BusinessHelperTrait::Config insteadof ControllerHelperTrait;
        BusinessHelperTrait::XpCall insteadof ControllerHelperTrait;
        BusinessHelperTrait::FireEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof ControllerHelperTrait;
        BusinessHelperTrait::OnEvent insteadof AppHelperTrait;
        BusinessHelperTrait::FireEvent insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfProject insteadof AppHelperTrait;
        BusinessHelperTrait::PathOfRuntime insteadof AppHelperTrait;
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
            'cli_command_with_app' => true,
            'path_info_compact_enable' => true,
            'duckphp_all_in_one_wrap_header_foot' => true,
        ];
        
        // embed view to this class
        $ext_options['ext'][CallableView::class] = true;
        $ext_options['callable_view_class'] = static::class;
        $ext_options['callable_view_prefix'] = 'view_';
        
        $this->options = array_merge($this->options, $ext_options);
    }
    public function __construct()
    {
        $this->embedMe();
        parent::__construct();
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
