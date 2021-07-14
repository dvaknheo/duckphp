<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\Ext\SimpleModelTrait;
use DuckPhp\Helper\ModelHelperTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

use SimpleBlog\System\App;

class ProjectModel
{
    use SingletonExTrait;
    use SimpleModelTrait;
    use ModelHelperTrait;
    
    public function __construct()
    {
        $this->table_prefix = App::G()->getTablePrefix();
    }
}
