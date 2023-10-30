<?php
$info = \DuckPhp\Core\Route::_()->getRouteError();
?>
404 info:<?php echo $info; ?>;
<?php if (true) {
    ?>
Developing!
<pre>
<?php debug_print_backtrace(2); ?>
</pre>
<?php
}?>