<?php
use MY\Base\Helper\ViewHelper as V;
?>
500
<?php if(V::IsDebug()){ ?>
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
