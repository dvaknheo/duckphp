<?php
use \DNMVCS\DNMVCS as DN;
?>
<fieldset>
<legend>超全局变量</legend>
<h3>$_GET</h3>
<pre>
<?php var_dump(DN::SG()->_GET);?>
</pre>
<h3>$_POST</h3>
<pre>
<?php var_dump(DN::SG()->_POST);?>
</pre>
<h3>$_REQUEST</h3>
<pre>
<?php var_dump(DN::SG()->_REQUEST);?>
</pre>
<h3>$_SERVER</h3>
<pre>
<?php var_dump(DN::SG()->_SERVER);?>
</pre>
<h3>$_ENV</h3>
<pre>
<?php var_dump(DN::SG()->_ENV);?>
</pre>
<h3>$_COOKIE</h3>
<pre>
<?php var_dump(DN::SG()->_COOKIE);?>
</pre>
<h3>$_SESSION</h3>
<pre>
<?php var_dump(DN::SG()->_SESSION);?>
</pre>
</fieldset>