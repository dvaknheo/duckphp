<?php declare(strict_types=1);
// view/files.php?>
<!doctype html><html><body>
<fieldset>
<legend><?=__l('files.elapsed_time')?></legend>
<div>
    <?=__l('files.elapsed')?><strong><?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 6, '.', ''); ?></strong> <?=__l('files.seconds')?>
    &nbsp;|&nbsp;
    <?=__l('files.memory')?><strong><?php echo number_format(memory_get_peak_usage()); ?></strong> <?=__l('files.bytes')?>
</div>
</fieldset>
<fieldset>
<legend><?=__l('files.singletons')?></legend>
<pre>
<?php 
\Duckphp\Core\PhaseContainer::Dump();
?>
</pre>
</fieldset>


<fieldset>
<legend><?=__l('files.app_options')?></legend>
<pre>
<?php var_export(@array_diff_assoc(\DuckPhp\Core\App::_()->options,(new \DuckPhp\DuckPhp())->options));?>
</pre>
</fieldset>
<fieldset>
<legend><?=__l('files.all_options')?></legend>
<pre>
<?php var_export(\DuckPhp\Core\App::_()->options);?>
</pre>
<?=__l('files.total')?> <?=count(\DuckPhp\Core\App::_()->options);?><?=__l('files.total_unit')?>
</fieldset>
<fieldset>
    <legend><?=__l('files.stack')?></legend>
    <pre>
<?php 
ob_start();
debug_print_backtrace(2);
$data = ob_get_clean();
$path = dirname(dirname(__DIR__));
$data = str_replace($path,'', $data);

echo $data;
?>
    </pre>
</fieldset>
<fieldset>
<legend><?=__l('files.included')?></legend>
<pre>
<?php
$t=get_included_files();sort($t); 
$path = dirname(dirname(__DIR__));

$data = var_export($t, true);
$data = str_replace($path,'', $data);
$data = preg_replace('#\/vendor.*?\.php#','vendor', $data);
$data = preg_replace('/^  \d\d => \'vendor.*\r?\n/m','', $data);
echo $data;
?>
* <?=__l('files.vendor_ignored')?>
</pre>
</fieldset>
<fieldset>
<legend><?=__l('files.public_methods')?></legend>
<pre>
<?php 
$ref = new ReflectionClass(\DuckPhp\DuckPhp::class);
//$t =get_class_methods(\DuckPhp\DuckPhp::class);
$m = $ref->getMethods();
$t=[];foreach($m as $v){
    if(!$v->isPublic()){continue;}
    if(substr($v->name,0,1) === '_'){ continue; }
    $t[]=$v->name;
}
sort($t);

var_export($t);?>
</pre>
</fieldset>
<fieldset>
<legend><?=__l('files.all_methods')?></legend>
<pre>
<?php 
$ref = new ReflectionClass(\DuckPhp\DuckPhp::class);
$m = $ref->getMethods();
$t=[];
foreach($m as $v){
    if(!$v->isPublic()){continue;}
    $t[]=$v->name;
}
var_export($t);
?>
</pre>
</fieldset>

</body></html>
