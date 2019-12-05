404 (u/app/view/_sys)
<?php if(true ){ ?>
<pre>
<?php debug_print_backtrace(); ?>
</pre>
<?php }?>
<pre>
--
<?php
DNMVCS\Core\AutoLoader::G()->assignPathNamespace(__DIR__."/src","Opis\Closure");
$factorial = function ($n) use (&$factorial) {
  return $n <= 1 ? 1 : $factorial($n - 1) * $n;
};
$a=spl_autoload_functions();
var_dump($a);
$wrapper = new Opis\Closure\SerializableClosure($a[1]);
// Now it can be serialized
$serialized = serialize($wrapper);
echo $serialized;
    //\xSwooleHttpd\SwooleCoroutineSingleton::DumpString();
?>
--
</pre>