<?php declare(strict_types=1);
use MY\Base\Helper\ViewHelper as V;
use MY\Base\Helper\ControllerHelper as DN;

?>
<fieldset>
<legend>超全局变量</legend>
<h3>$_GET</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_GET);?>
</pre>
<h3>$_POST</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_POST);?>
</pre>
<h3>$_REQUEST</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_REQUEST);?>
</pre>
<h3>$_SERVER</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_SERVER);?>
</pre>
<h3>$_ENV</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_ENV);?>
</pre>
<h3>$_COOKIE</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_COOKIE);?>
</pre>
<h3>$_SESSION</h3>
<pre>
<?php var_dump(DN::SuperGlobal()->_SESSION);?>
</pre>
</fieldset>