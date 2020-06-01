<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
require_once(__DIR__.'/../../autoload.php');        // @DUCKPHP_HEADFILE

$namespace = 'MY';                              // @DUCKPHP_NAMESPACE
$path = realpath(__DIR__.'/..');

$options = [];
$options['path'] = $path;
$options['namespace'] = $namespace;
// $options['path_namespace'] = 'app';

// $options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true;
$options['ext']['DuckPhp\\Ext\\RouteHookOneFileMode']=true; //@DUCKPHP_DELETE
echo "<div>Don't run the template file directly, Install it! </div>\n"; //@DUCKPHP_DELETE

\DuckPhp\App::RunQuickly($options);
