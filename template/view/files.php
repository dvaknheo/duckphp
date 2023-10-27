<?php declare(strict_types=1);
// view/test/done.php?>
<!doctype html><html><body>
<fieldset>
<legend>应用的选项</legend>
<pre>
<?php var_export(array_diff_assoc(\DuckPhp\Core\App::G()->options,(new \DuckPhp\DuckPhp())->options));?>
</pre>
</fieldset>
<fieldset>
<legend>全部选项</legend>
<pre>
<?php var_export(\DuckPhp\Core\App::G()->options);?>
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

</body></html>