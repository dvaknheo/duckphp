<?php declare(strict_types=1);
use MY\Base\Helper\ViewHelper as V;

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Hello DuckPHP!</title>
</head>
<body>

本页面展示 ContrlloerHelper 方法。

ContrlloerHelper 的方法很多很杂，但掌握了 ContrlloerHelper  方法，基本就掌握了用法
大致分为 【通用杂项】【路由处理】【异常管理】【跳转】【swoole 兼容】 【内容处理】 几块
内容处理和 ViewHelper 基本通用。
<fieldset>
    <legend>ControllerHelper 方法</legend>

    <dt><a href="#">H</a></dt>
    <dd>【显示相关】<a href="<?=V::URL('ViewHelper/index#H')?>">见 ViewHelper 的 H 介绍</a></dd>
    <dt><a href="#">L</a></dt>
    <dd>【显示相关】<a href="<?=V::URL('ViewHelper/index#L')?>">见 ViewHelper 的 L 介绍</a></dd>
    <dt><a href="#">HL</a></dt>
    <dd>【显示相关】<a href="<?=V::URL('ViewHelper/index#HL')?>">见 ViewHelper 的 HL 介绍</a></dd>
    <dt><a href="#">URL</a></dt>
    <dd>【显示相关】<a href="<?=V::URL('ViewHelper/index#URL')?>">见 ViewHelper 的 URL 介绍</a></dd>
    <dt><a href="#">Display</a></dt>
    <dd>【显示相关】<a href="<?=V::URL('ViewHelper/index#Display')?>">见 ViewHelper 的 Display 介绍</a></dd>
    
    <dt><a href="#">Setting</a></dt>
    <dd>【配置相关】<a href="<?=V::URL('ServiceHelper/index#Setting')?>">见 ServiceHelper 的 Setting 介绍</a></dd>
    <dt><a href="#">Config</a></dt>
    <dd>【配置相关】<a href="<?=V::URL('ServiceHelper/index#Config')?>">见 ServiceHelper 的 Config 介绍</a></dd>
    <dt><a href="#">LoadConfig</a></dt>    
    <dd>【配置相关】<a href="<?=V::URL('ServiceHelper/index#LoadConfig')?>">见 ServiceHelper 的 LoadConfig 介绍</a></dd>
    <dt><a href="#">ExitRedirect($url, $exit = true)</a></dt>
    <dd>【跳转】跳转到站内URL ，$exit 为 true 则附加 exit()</dd>
    <dt><a href="#">ExitRedirectOutside($url, $exit = true)</a></dt>
    <dd>【跳转】跳转到站外URL, $exit 为 true 则附加 exit()</dd>
    <dt><a href="#">ExitRouteTo($url, $exit = true)</a></dt>
    <dd>【跳转】跳转到相对 url , $exit 为 true 则附 exit</dd>
    <dt><a href="#">Exit404($exit = true)</a></dt>
    <dd>【跳转】报 404，显示后续页面，$exit 为 true 则附加 exit()</dd>
    <dt><a href="#">ExitJson($ret, $exit = true)</a></dt>
    <dd>【跳转】输出 json 结果，$exit 为 true 则附加 exit()</dd>
    
    
    <dt><a href="#">getRouteCallingMethod()</a></dt>
    <dd>【路由相关】获得当前的路由调用方法，用于权限判断等</dd>
    <dt><a href="#">setRouteCallingMethod</a></dt>
    <dd>【路由相关】设置当前的路由调用方法，用于跨方法调用时候 view 修正</dd>
    <dt><a href="#">getPathInfo()</a></dt>
    <dd>【路由相关】获得当前的 PATH_INFO</dd>
    <dt><a href="#">getParameters</a></dt>
    <dd>【路由相关】获得路由重写相关的数据</dd>
    
    <dt><a href="#">Show($data = [], $view = null)</a></dt>
    <dd>【内容处理】显示视图， 默认为 view/$view.php 的文件， 并会带上页眉页脚</dd>
    <dt><a href="#">setViewHeadFoot($head_file = null, $foot_file = null)</a></dt>
    <dd>【内容处理】设置页眉页脚</dd>
    <dt><a href="#">assignViewData($key, $value = null)</a></dt>
    <dd>【内容处理】分配视图变量，另一版本为 assignViewData($assoc);  </dd>
    <dt><a href="#">Pager()</a></dt>
    <dd>【内容处理】获得分页器对象, 分页器参考 DuckPhp\Ext\Pager。 DuckPHP 只是做了最小的分页器</dd>

    <dt><a href="#">assignExceptionHandler</a></dt>
    <dd>【异常处理】分配异常句柄</dd>
    <dt><a href="#">setMultiExceptionHandler</a></dt>
    <dd>【异常处理】设置多个异常处理</dd>
    <dt><a href="#">setDefaultExceptionHandler</a></dt>
    <dd>【异常处理】设置异常的默认处理</dd>

    <dt><a href="#">header</a></dt>
    <dd>【系统替代】 header 函数以兼容命令行模式</dd>
    <dt><a href="#">setcookie()</a></dt>
    <dd>【系统替代】 setcookie 函数以兼容命令行模式</dd>
    <dt><a href="#">exit</a></dt>
    <dd>【系统替代】 退出函数，以便于接管</dd>
    
    <dt><a href="#">SG</a></dt>
    <dd>【swoole 兼容】 SG()-> 前缀替代  超全局变量做 swoole 兼容， 如 C::SG()->_GET[] , C::SG()->_POST[] 等。</dd>
    </dl>
</fieldset>
</body>
</html>
