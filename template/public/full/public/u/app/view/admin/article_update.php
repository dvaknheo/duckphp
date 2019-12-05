<form method="post">
<input type="hidden" name="id" value="<?=$article['id']?>">
<input type="text" name="title" value="<?=$article['title']?>">
<textarea name="content"><?=$article['content']?></textarea>
<input type="submit" value="更新" />
</form>