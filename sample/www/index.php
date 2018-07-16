<?php
require('../../DNMVCS/DNMVCS.php');
$options=[
];
\DNMVCS\DNMVCS::G($options)->RunQuickly();
//$path=realpath('../');
//\DNMVCS\DNMVCS::G()->autoload(['path']=>$path);
//\DNMVCS\DNMVCS::G()->init([])->run();