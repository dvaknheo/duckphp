<?php
$options=[];

require(__DIR__.'/../headfile/headfile.php');

//* DNMVCS TO DELETE 
if (defined('DNMVCS_WARNING_IN_TEMPLATE')) {
    $options['is_debug']=true;
    $options['skip_setting_file']=true;
    echo "<div>Don't run the template file directly </div>\n";
}
//*/

$path=realpath(__DIR__.'/..');
$options['path']=$path;
$options['namespace']='MY\\';
\DNMVCS\DNMVCS::RunQuickly($options, function () {
});
