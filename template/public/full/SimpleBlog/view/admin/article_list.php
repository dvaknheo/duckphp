<a href="<?=$url_add?>">写文章</a>
<table>
<tr>
	<th>ID</th>
	<th>标题</th>
	<th>编辑</th>
	<th>删除</th>
</tr>
<?php foreach ($list as $v) {?>
<tr>
	<td><?=$v['id']?></td>
	<td><?=$v['title']?></td>
	<td><a href="<?=$v['url_edit']?>">编辑</a></td>
	<td><a href="<?=$v['url_delete']?>">删除</a></td>
</tr>
<?php }?>
</table>
<?php include(__DIR__. '/inc_script.php'); ?>