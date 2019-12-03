<?php
require_once(__DIR__.'/../headfile/headfile.php');
$options=[];
//* DuckPhp TO DELETE
if (defined('DuckPhp_WARNING_IN_TEMPLATE')) {
    $options['is_debug']=true;
    $options['skip_setting_file']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
//*/

$options['path']=realpath(__DIR__.'/..');
$options['namespace']=rtrim('MY\\', '\\');

$options['error_404']='_sys/error-404';
$options['error_500']='_sys/error-500';
$options['error_exception']='_sys/error-exception';
$options['error_debug']='_sys/error-debug';

\DuckPhp\DuckPhp::RunQuickly($options, function () {
});
