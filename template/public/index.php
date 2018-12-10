<?php
require(__DIR__.'/../boot/headfile.php');

$path=realpath(__DIR__.'/..');
$options=[
	'path'=>$path,
];
\DNMVCS\DNMVCS::RunQuickly($options);
// \DNMVCS\DNMVCS::G()->init($options)->run();