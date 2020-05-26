<form method="post">
<input type="hidden" name="id" value="<?=$article['id']?>">
<div>
<label>标题<label>
<input type="text" name="title" value="<?=$article['title']?>">
</div>
<div>
<label>内容</label>
<textarea name="content"><?=$article['content']?></textarea>
</div>
<input type="submit" value="更新" />
</form>