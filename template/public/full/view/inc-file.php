<fieldset>
<legend>用到的文件</legend>
<pre>
<?php
$files = get_included_files();
//sort($files);
var_dump($files);?>
</pre>
<pre>
array (size=42)
  0 => string '/mnt/d/MyWork/sites/DNMVCS/autoload.php'
  1 => string '@DuckPhp/App.php' (length=38)
  2 => string '@DuckPhp/Core/App.php' (length=43)
  3 => string '@DuckPhp/Core/AppPluginTrait.php' (length=54)
  4 => string '@DuckPhp/Core/AutoLoader.php' (length=50)
  5 => string '@DuckPhp/Core/Configer.php' (length=48)
  6 => string '@DuckPhp/Core/ExceptionManager.php' (length=56)
  7 => string '@DuckPhp/Core/ExtendableStaticCallTrait.php' (length=65)
  8 => string '@DuckPhp/Core/Helper/ControllerHelper.php' (length=63)
  9 => string '@DuckPhp/Core/Helper/HelperTrait.php' (length=58)
  10 => string '@DuckPhp/Core/Helper/ModelHelper.php' (length=58)
  11 => string '@DuckPhp/Core/Helper/ServiceHelper.php' (length=60)
  12 => string '@DuckPhp/Core/Helper/ViewHelper.php' (length=57)
  13 => string '@DuckPhp/Core/Route.php' (length=45)
  14 => string '@DuckPhp/Core/RuntimeState.php' (length=52)
  15 => string '@DuckPhp/Core/SingletonEx.php' (length=51)
  16 => string '@DuckPhp/Core/SuperGlobal.php' (length=51)
  17 => string '@DuckPhp/Core/SystemWrapper.php' (length=53)
  18 => string '@DuckPhp/Core/ThrowOn.php' (length=47)
  19 => string '@DuckPhp/Core/View.php' (length=44)
  20 => string '@DuckPhp/Ext/DBManager.php' (length=48)
  21 => string '@DuckPhp/Ext/Misc.php' (length=43)
  22 => string '@DuckPhp/Ext/RouteHookRewrite.php' (length=55)
  23 => string '@DuckPhp/Ext/RouteHookRouteMap.php' (length=56)
  24 => string '@DuckPhp/Ext/SimpleLogger.php' (length=51)
  25 => string '@DuckPhp/Helper/ControllerHelper.php' (length=58)
  26 => string '@DuckPhp/Helper/ModelHelper.php' (length=53)
  27 => string '@DuckPhp/Helper/ServiceHelper.php' (length=55)
  28 => string '@DuckPhp/Helper/ViewHelper.php' (length=52)
  29 => string '@Project/app/Base/App.php' (length=64)
  30 => string '@Project/app/Base/Helper/ControllerHelper.php' (length=84)
  31 => string '@Project/app/Base/Helper/ModelHelper.php' (length=79)
  32 => string '@Project/app/Base/Helper/ServiceHelper.php' (length=81)
  33 => string '@Project/app/Base/Helper/ViewHelper.php' (length=78)
  34 => string '@Project/app/Controller/Main.php' (length=71)
  35 => string '@Project/auth/Base/App.php' (length=65)
  36 => string '@Project/auth/Base/Helper/ControllerHelper.php' (length=85)
  37 => string '@Project/config/setting.php' (length=66)
  38 => string '@Project/public/index.php' (length=64)
  39 => string '@Project/view/inc-backtrace.php' (length=70)
  40 => string '@Project/view/inc-file.php' (length=65)
  41 => string '@Project/view/main.php' (length=61)
</pre>
</fieldset>