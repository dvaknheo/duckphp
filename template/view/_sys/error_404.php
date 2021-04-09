<?php declare(strict_types=1);
// change me if you can
//var_dump(get_defined_vars());

$is_debug = __is_debug();
?>
<h1>404!</h1>
<?php
    if ($is_debug) {
        ?>
Developing!
<pre>
<?php debug_print_backtrace(); ?>
</pre>
<?php
    }
?>