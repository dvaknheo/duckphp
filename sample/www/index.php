<?php
require('../../DNMVCS/DNMVCS.php');
//$path=realpath('../');
//\DNMVCS\DNMVCS::G()->autoload(['path']=>$path);
//\DNMVCS\DNMVCS::G()->init([])->run();
$options=[
];
\DNMVCS\DNMVCS::G($options)->RunQuickly();