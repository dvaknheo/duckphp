<?php
//var_dump(get_defined_vars());
?>
<?php if ($is_debug) {
    ?>
<fieldset>
	<legend>Exception(<?=get_class($ex); ?>:<?php echo($ex->getCode()); ?>)</legend>
	<?php echo($ex->getMessage()); ?>
<pre>
--
<?php echo($trace); ?>
</pre>
</fieldset>
<?php
}?>
