<?php declare(strict_types=1);
require(__DIR__.'/../../autoload.php');  // @DUCKPHP_HEADFILE

function getfile($f)
{
    if (!$f) {
        return;
    }
    $ref = new ReflectionClass(\DuckPhp\DuckPhp::class);
    $path = realpath(dirname($ref->getFileName()) . '/../docs').'/';
    $file = realpath($path.$f);
    if (substr($file, 0, strlen($path)) != $path) {
        return;
    } // 安全处理
    
    $str = file_get_contents($file);
    
    
    /////////////// show
    //TODO 缓存处理
    if (substr($file, -4) === '.svg') {
        header('content-type:image/svg+xml');
        echo $str;
    } elseif (substr($file, -3) === '.md') {
        $str = preg_replace('/([a-z_]+\.gv\.svg)/', "?f=$1", $str);
        header('content-type:application/json');
        $json = ['s' => $str];
        echo json_encode($json, JSON_UNESCAPED_UNICODE); // 纯文本太折腾，用json
    }
    
    exit;
}
if (!defined('VIEW')) {
    $f = $_GET['f'] ?? null;
    getfile($f);
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>文档</title>
  <script src="//cdn.jsdelivr.net/npm/marked/lib/marked.min.js"></script>
  <link rel="stylesheet" media="all" href="doc.css" /><!-- Highlighter.css -->
  <style>
  pre {background-color:#eeeeee;}
  </style>
</head>
<body>
<div>
一个简单的 md 文件读取器，够本文档用就行了。 <br />
<a href="#">返回文档主页</a>
<a href="/">返回主页</a>
</div>
<div>
  <div id="wrapper" style="border:1px solid gray;padding:0.5em;">
  正在打开文档。请保证 cdn.jsdelivr.net ，外接 js 能访问
  </div>
</div>
<script>

function fetchMarkdown(url){
    url=url?url:'##/index.md';
    url=url.substring(2);
    //baseUrl:"/" marked 的这项好像无效。
    var a =location.hash.substring(2).split('/');
    a.pop();
    baseUrl=a.join('/')+'/';
    url =location.pathname +"?f="+url;
    fetch(url).then(function(response){return response.json()})
    .then(function(data){
    
        var txt=data.s;
        document.getElementById('wrapper').innerHTML = marked.parse(txt,{ },function(err,res){
            res=res.replace(/href="/g,'href="##'+baseUrl);
            return res;
        });
    })
}
fetchMarkdown(location.hash);
window.onhashchange= function(){ fetchMarkdown(location.hash);}; 
</script>
</body>
</html>