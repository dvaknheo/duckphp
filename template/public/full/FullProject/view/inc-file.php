<fieldset>
<legend>用到的文件</legend>
<pre>
<?php
$files = get_included_files();
sort($files);
var_export($files);?>
</pre>
理论
<pre>
array (size=42)
  0 => string '@AutoLoadFile'
  1 => string '@DuckPhp/App.php'
  2 => string '@DuckPhp/Core/App.php'
  3 => string '@DuckPhp/Core/AppPluginTrait.php'
  4 => string '@DuckPhp/Core/AutoLoader.php'
  5 => string '@DuckPhp/Core/Configer.php'
  6 => string '@DuckPhp/Core/ExceptionManager.php'
  7 => string '@DuckPhp/Core/ExtendableStaticCallTrait.php'
  8 => string '@DuckPhp/Core/Helper/ControllerHelper.php'
  9 => string '@DuckPhp/Core/Helper/HelperTrait.php'
  10 => string '@DuckPhp/Core/Helper/ModelHelper.php'
  11 => string '@DuckPhp/Core/Helper/ServiceHelper.php'
  12 => string '@DuckPhp/Core/Helper/ViewHelper.php'
  13 => string '@DuckPhp/Core/Route.php'
  14 => string '@DuckPhp/Core/RuntimeState.php'
  15 => string '@DuckPhp/Core/SingletonEx.php'
  16 => string '@DuckPhp/Core/SuperGlobal.php'
  17 => string '@DuckPhp/Core/SystemWrapper.php'
  18 => string '@DuckPhp/Core/ThrowOn.php'
  19 => string '@DuckPhp/Core/View.php'
  20 => string '@DuckPhp/Ext/DBManager.php'
  21 => string '@DuckPhp/Ext/Misc.php'
  22 => string '@DuckPhp/Ext/RouteHookRewrite.php'
  23 => string '@DuckPhp/Ext/RouteHookRouteMap.php'
  24 => string '@DuckPhp/Ext/SimpleLogger.php'
  25 => string '@DuckPhp/Helper/ControllerHelper.php'
  26 => string '@DuckPhp/Helper/ModelHelper.php'
  27 => string '@DuckPhp/Helper/ServiceHelper.php'
  28 => string '@DuckPhp/Helper/ViewHelper.php'
  29 => string '@Project/app/Base/App.php'
  30 => string '@Project/app/Base/Helper/ControllerHelper.php'
  31 => string '@Project/app/Base/Helper/ModelHelper.php'
  32 => string '@Project/app/Base/Helper/ServiceHelper.php'
  33 => string '@Project/app/Base/Helper/ViewHelper.php'
  34 => string '@Project/app/Controller/Main.php'
  35 => string '@Project/auth/Base/App.php'
  36 => string '@Project/auth/Base/Helper/ControllerHelper.php'
  37 => string '@Project/config/setting.php'
  38 => string '@Project/public/index.php'
  39 => string '@Project/view/inc-backtrace.php'
  40 => string '@Project/view/inc-file.php'
  41 => string '@Project/view/main.php'
</pre>
</fieldset>