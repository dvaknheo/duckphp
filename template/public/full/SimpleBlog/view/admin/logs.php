<table>
<tr>
	<th>ID</th>
	<th>详情</th>
	<th>类型</th>
	<th>删除</th>
</tr>
<?php foreach ($list as $v) {?>
<tr>
	<td><?=$v['id']?></td>
	<td><?=$v['content']?></td>
	<td><?=$v['type']?></td>
	<td><a href="<?=$v['url_delete']?>"><?=$v['id']?></a></td>
</tr>
<?php }?>
</table>