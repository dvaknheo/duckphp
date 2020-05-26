<table>
<tr>
	<th>ID</th>
	<th>用户名</th>
	<th>删除</th>
</tr>
<?php foreach ($list as $v) {?>
<tr>
	<td><?=$v['id']?></td>
	<td><?=$v['username']?></td>
	<td><a href="<?=$v['url_delete']?>"><?=$v['id']?></a></td>
</tr>
<?php }?>
</table>