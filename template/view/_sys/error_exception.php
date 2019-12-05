<?php
use DuckPhp\Helper\ViewHelper as V;
//var_dump(get_defined_vars());

$is_debug=V::IsDebug();
$class=get_class($ex);
$code=$ex->getCode();
$message=$ex->getMessage();
$trace=$ex->getTraceString();

if ($is_debug) {
    ?>
<fieldset>
	<legend>Exception(<?=$class ?>:<?=$code?>)</legend>
	<?=$message ?>
<pre>
--
<?=$trace?>
</pre>
</fieldset>
<?php
}else{
?>
    500
<?php
}