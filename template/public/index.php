<?php
require(__DIR__.'/../headfile/headfile.php');

$path=realpath(__DIR__.'/..');
$options=[
	'path'=>$path,
];
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){
    $options['setting_file_basename']='';
    $options['is_dev']=true;
    echo "<div>Don't run the template file directly </div>\n"; }
}
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();