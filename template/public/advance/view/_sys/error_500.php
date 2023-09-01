<?php declare(strict_types=1);

// change this file if you can.
//var_dump(get_defined_vars());
$is_debug = __is_debug();
if ($is_debug) {

    $class = get_class($ex);

    $code = $ex->getCode();
    $message = $ex->getMessage();
    $file = $ex->getFile();
    $line = $ex->getLine();
    
    $trace = '';
    try{
        $trace = ''.$ex;
    }catch(\Throwable $e){}
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
