<?php declare(strict_types=1);
// view/test/done.php?>
<!doctype html><html><body>
<fieldset>
    <legend>到 View 层级的调用堆栈</legend>
    <pre>
<?php debug_print_backtrace(2);?>
    </pre>
</fieldset>
<fieldset>
<legend>到 View 层级的包含文件</legend>
<pre>
<?php $t=get_included_files();sort($t); var_dump($t);?>
</pre>
</fieldset>
<fieldset>
<legend>应用的选项</legend>
<pre>
<?php var_dump(array_diff_assoc(\DuckPhp\Core\App::G()->options,(new \DuckPhp\DuckPhp())->options));?>
</pre>
</fieldset>
</body></html>