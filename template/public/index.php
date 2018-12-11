<?php
require(__DIR__.'/../headfile/headfile.php');

$path=realpath(__DIR__.'/..');
$options=[
	'path'=>$path,
];
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ echo "<div>Don't run the template file directly </div>\n"; }
if(defined('DNMVCS_WARNING_IN_TEMPLATE')){ $options['setting_basename']=''; }
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();