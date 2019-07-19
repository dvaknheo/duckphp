<?php use \DNMVCS\DNMVCS as DN;

?>
404
<?php if (DN::IsDebug()) {
    ?>
Developing!
<pre>
<?php debug_print_backtrace(); ?>
</pre>
<?php
}?>