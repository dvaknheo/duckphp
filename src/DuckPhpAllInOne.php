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
    protected function embedMe()
    {
        // embed welcome page to this class
        $path = explode('\\', static::class);
        $short_class = array_pop($path);
        $namespace = implode("\\", $path);
        $ext_options = [
            'namespace_controller' => "\\".$namespace,
            'controller_welcome_class' => $short_class ,
            'controller_class_postfix' => '',
            
            'path_info_compact_enable' => true,
        ];
        
        // embed view to this class
        $this->options['ext'][CallableView::class] = true;
        $this->options['callable_view_class'] = static::class;
        $this->options['callable_view_prefix'] = 'view_';
        
        $this->options = array_merge($this->options, $ext_options);
    }
    public function __construct()
    {
        $this->embedMe();
        parent::__construct();
    }
    public function onInited()
    {
    }
    /////////////// controller ///////////////
    public function action_index()
    {
        static::setViewHeadFoot('head', 'foot');
        static::Show(get_defined_vars(), 'index');
    }
    ///////////////
    public function view_head($data)
    {
        //echo "[[";
    }
    public function view_index($data)
    {
        echo  static::class. " main page work at".DATE(DATE_ATOM);
    }
    public function view_foot($data)
    {
        //echo "]]";
    }
}
