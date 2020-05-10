<?php declare(strict_types=1);
use DuckPhp\Helper\ViewHelper as V;

// change me if you can
//var_dump(get_defined_vars());

$is_debug = V::IsDebug();
$class = get_class($ex);
$code = $ex->getCode();
$message = $ex->getMessage();
$trace = $ex->getTraceAsString();
$file = $ex->getFile();
$line = $ex->getLine();
if ($is_debug) {
                echo "<div>{$file} : {$line}</div>";

    ?>

<fieldset>
    <legend>Error(<?=$class ?>:<?=$code?>)</legend>
    <?=$message ?>
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
