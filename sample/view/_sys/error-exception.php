<?php use \DNMVCS\DNMVCS as DN ;?>
<?php if(DN::G()->isDev()){ ?>
<fieldset>
	<legend>Exception(<?=get_class($ex);?>:<?php echo($ex->getCode());?>)</legend>
	<?php echo($ex->getMessage());?>
<pre>
--
<?php echo($trace);?>
</pre>
</fieldset>
<?php }?>
