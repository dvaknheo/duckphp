<?php use \DNMVCS\DNMVCS as DN ;

?>
500
<?php if ($is_debug) {
    ?>
<fieldset>
	<legend>Developing! Error(<?=get_class($ex); ?>:<?php echo($ex->getCode()); ?>)</legend>
	<?php echo($ex->getMessage()); ?>
<pre>
<?=($ex->getFile()); ?>:<?=($ex->getLine()); ?>


--
<?php echo($trace); ?>
</pre>
</fieldset>
<?php
}?>
