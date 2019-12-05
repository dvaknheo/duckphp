<?php
use DuckPhp\Helper\ViewHelper as V;
////var_dump(get_defined_vars());

$is_debug=V::IsDebug();
?>
404
<?php if ($is_debug) {
    ?>
Developing!
<pre>
<?php debug_print_backtrace(); ?>
</pre>
<?php
}?>