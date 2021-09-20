<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Core;

use DuckPhp\Core\Route;

class RouteEx extends Route
{
    public $options = [
        /*
        'controller_welcome_class' => 'Main',
        'controller_class_postfix' => '',
        'controller_enable_slash' => false,
        'controller_path_ext' => '',
        'controller_stop_static_method' => true,
        'controller_strict_mode' => true,
        */
    ];
    public function __construct()
    {
        $this->options = array_replace_recursive($this->options, (new parent())->options); //merge parent's options;
        parent::__construct();
    }

}