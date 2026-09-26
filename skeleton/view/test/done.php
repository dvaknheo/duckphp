<?php declare(strict_types=1);
/**
 * DuckPhp
 *
 * 路由 /test/done 的视图（控制器 src/Controller/testController.php 里 Helper::Show() 不给视图名时，
 * 框架按当前路由找这个文件）。
 */
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>test/done</title>
</head>
<body>
<h1>test</h1>
<div><?= __h($var) ?></div>
</body>
</html>
