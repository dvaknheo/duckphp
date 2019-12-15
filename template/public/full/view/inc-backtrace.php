<fieldset>
<legend>调用堆栈</legend>
<h3></h3>
<pre>
<?php debug_print_backtrace(2);?>
</pre>
<h3></h3>
<pre>
#0  include(@DOCUMENT_ROOT/full/view/main.php)   [@DuckPhp/Core/View.php:52]                        // 包含  View 文件
#1  DuckPhp\Core\View->_Show()                   [@DuckPhp/Core/App.php:751]                        // View 类实际处理 视图
#2  DuckPhp\Core\App->_Show()                    [@DuckPhp/Core/App.php:707]                        // App::Show 的内部实现，处理一些东西，转由 View 类出来
#3  DuckPhp\Core\App::Show()                     [@DuckPhp/Core/Helper/ControllerHelper.php:64]     // 未接管情况下，ControllerHelper 传递到实际的 App::Show
#4  DuckPhp\Core\Helper\ControllerHelper::Show() [@Project_namespace_path/Controller/Main.php:21]   // 调用 ControllerHelper::Show 显示页面
----
#5  MY\Controller\Main->index()                  [@DuckPhp/Core/Route.php:280]                      // index 方法
#6  DuckPhp\Core\Route->defaultRunRouteCallback()[@DuckPhp/Core/Route.php:211]                      // 默认路由方法
#7  DuckPhp\Core\Route->run()                    [@DuckPhp/Core/App.php:277]                        // 路由，处理钩子等。
#8  DuckPhp\Core\App->run()                      [@DuckPhp/Core/App.php:138]                        // App run 方法开始运行
#9  DuckPhp\Core\App::RunQuickly()               [@DOCUMENT_ROOT/full/public/index.php:15]          // 快速运行
</pre>
</fieldset>