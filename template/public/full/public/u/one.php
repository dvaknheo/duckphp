<?php
require(__DIR__.'/../../headfile/headfile.php');

$options=[
	'path'=>__DIR__,
	'path_controller'=>'app/Controller',
	'path_view'=>'app/view',
	'path_config'=>'app/config',
	'path_lib'=>'app/lib',
	'ext'=>[
		'key_for_action'=>'act',
		'key_for_module' =>'module',
	]
];
\DNMVCS\DNMVCS::RunQuickly($options);

$url="one.php/aaa/bbb/cc?module=xx&act=y&c=e";

// article/1 => article?id=1;

// dir/aa.php/dd/az.php/z?module=zz&act=z&args=zxvf

?>

<?=$url?> => <?= \DNMVCS\DNMVCS::URL($url);?>
