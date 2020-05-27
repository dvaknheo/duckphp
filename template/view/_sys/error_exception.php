<?php declare(strict_types=1);
use DuckPhp\Helper\ViewHelper as V;

// change this file if you can.
//var_dump(get_defined_vars());

$is_debug = V::IsDebug();
$class = get_class($ex);
$code = $ex->getCode();
$message = $ex->getMessage();
$trace = $ex->getTraceString();
$file = $ex->getFile();
$line = $ex->getLine();

if ($is_debug) {
    ?>
<fieldset>
    <legend>Exception(<?=$class ?>:<?=$code?>)</legend>
    <?=$message ?>
    <div><?=$file?> : <?=$line?></div>";

<pre>
--
<?=$trace?>
</pre>
</fieldset>
<?php
} else {
        ?>
    500
<?php
}
