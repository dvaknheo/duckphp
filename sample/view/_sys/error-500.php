500
<?php if(DN::G()->isDev()){ ?>

<pre>
<?php debug_print_backtrace(); ?>
</pre>
<hr />
<pre>
<?php var_dump($ex);?>
</pre>
<?php }?>