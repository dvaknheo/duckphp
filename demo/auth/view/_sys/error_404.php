<?php
use MY\Base\App;

$is_debug=App::IsDebug(); //...
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