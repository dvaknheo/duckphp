<?php declare(strict_types=1);
use DuckPhp\Helper\ViewHelper as V;

// change me if you can
////var_dump(get_defined_vars());

$is_debug = V::IsDebug();
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