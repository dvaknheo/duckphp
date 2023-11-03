<?php declare(strict_types=1);
// view/test/done.php?>
<!doctype html><html><body>
<fieldset>
<legend>全部单例</legend>
<pre>
<?php 
\Duckphp\Core\PhaseContainer::GetContainerInstanceEx()->dumpAllObject();
?>
</pre>
</fieldset>


<fieldset>
<legend>应用的选项</legend>
<pre>
<?php var_export(array_diff_assoc(\DuckPhp\Core\App::_()->options,(new \DuckPhp\DuckPhp())->options));?>
</pre>
</fieldset>
<fieldset>
<legend>全部选项</legend>
<pre>
<?php var_export(\DuckPhp\Core\App::_()->options);?>
</pre>
</fieldset>
<fieldset>
    <legend>到 View 层级的调用堆栈</legend>
    <pre>
<?php debug_print_backtrace(2);?>
    </pre>
</fieldset>
<fieldset>
<legend>到 View 层级的包含文件</legend>
<pre>
<?php $t=get_included_files();sort($t); var_export($t);?>
</pre>
</fieldset>
<fieldset>
<legend>DuckPhp 类的公开方法列表</legend>
<pre>
<?php 
$ref = new ReflectionClass(\DuckPhp\DuckPhp::class);
//$t =get_class_methods(\DuckPhp\DuckPhp::class);
$m = $ref->getMethods();
$t=[];foreach($m as $v){
    if(!$v->isPublic()){continue;}
    if(substr($name,0,1) === '_'){ continue; }
    $t[]=$v->name;
}
var_export($t);?>
</pre>
</fieldset>
<fieldset>
<legend>DuckPhp 类全部方法列表</legend>
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