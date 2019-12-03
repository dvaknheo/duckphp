[[[[[[[[[[[[[[[[[[[
<?php use \DNMVCS\DNMVCS as DN ?>
<h1>DNMVCS_FullTest - 404</h1>
<?php var_dump(DN::SG());?>
<?php if(DN::IsDebug()){ ?>
Developing!
<pre>
<?php debug_print_backtrace(); ?>
</pre>
<?php }?>
]]]]]]]]]]]]]]]]]]]