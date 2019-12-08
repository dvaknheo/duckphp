500
<?php if(!empty($__is_debug)){ ?>
<fieldset>
	<legend>Error(<?=get_class($ex);?>:<?php echo($ex->getCode());?>)</legend>
	<?php echo($ex->getMessage());?>
<pre>
<?=($ex->getFile());?>:<?=($ex->getLine());?>
--
<?php echo($trace);?>
</pre>
</fieldset>
<?php }?>
