exceptiont! 发生了异常.
<fieldset>
	<legend>Exception(<?=get_class($ex);?>:<?php echo($ex->getCode());?>)</legend>
	<?php echo($ex->getMessage());?>
<pre>
--
<?php echo($trace);?>
</pre>
</fieldset>