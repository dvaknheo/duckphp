<?php declare(strict_types=1);
// var_dump(get_defined_vars());var_dump($this);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hello DuckPhp!</title>
</head>
<body>
<h1>Hello DuckPhp T: <?= __l("hello")?></h1>
Now is [<?=$var?>]
<hr/>

<div>
    <?=__l('main.welcome')?> <?php echo $var;?>
    <a href="<?=__url('test/done')?>"><?=__l('main.see_demo')?></a>
</div>
<hr />
<div>
<?=__l('main.switch_language')?>:
<a href="<?=__url('')?>?lang=en"><?=__l('main.language_en')?></a> |
<a href="<?=__url('')?>?lang=zh_CN"><?=__l('main.language_zh')?></a>
</div>
<hr />
<a href="<?=__url('doc')?>"> <?=__l('main.doc_in_app')?></a> <a href="/doc.php"> <?=__l('main.doc_standalone')?></a> 
<hr />
<div>
<?=__l('main.examples_blurb')?>
<ul>
    <li><a href="<?=__url('files')?>"><?=__l('main.link.files')?></a>
    <li><a href="/demo.php"><?=__l('main.link.demo')?></a>
    <li><a href="/helloworld.php"><?=__l('main.link.helloworld')?></a>
    <li><a href="/just-route.php"><?=__l('main.link.just_route')?></a>
    <li><a href="/api.php/test.index"><?=__l('main.link.api')?></a>
    <li><a href="/traditional.php"><?=__l('main.link.traditional')?></a>
    <li><a href="/rpc.php"><?=__l('main.link.rpc')?></a>
    <li><a href="<?=__url('db_test/')?>"><?=__l('main.link.dbtest_in')?></a> <a href="/dbtest.php"><?=__l('main.link.dbtest_out')?></a>
    <li><?=__l('main.current_url')?> (<?=__url('')?>)
    <li><a href="/cover_test.php"><?=__l('main.link.cover_test')?></a>
</ul>
</div>
</body>
</html>
