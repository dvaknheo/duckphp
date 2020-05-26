<!doctype html>
<html>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<body>
<fieldset>
	<legend><?=$article['title']?></legend>
	<div>
		<?=$article['content']?>
	</div>
</fieldset>
<fieldset>
	<legend>评论列表</legend>
	<ul>
<?php foreach ($article['comments'] as $v) {?>
		<li><?=$v['content']?> (<?=$v['username']?> |<?=$v['created_at']?>)</li>
<?php }?>
	</ul>
	<?=$html_pager?>
</fieldset>
<fieldset>
	<legend>添加评论</legend>
<?php if ($user) {?>
	<form method="post" action="<?=$url_add_comment?>">
		<input name="article_id" type="hidden"  value="<?=$article['id']?>">
		<textarea name="content"></textarea>
		<input type="submit" value="提交">
	</form>
<?php } else { ?>
	<a href="<?=$url_login_to_commment?>">登录以评论TODO</a>
<?php }?>
</fieldset>
</body>
</html>