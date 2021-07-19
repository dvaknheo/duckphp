<table>
<tr>
	<th>ID</th>
	<th>详情</th>
	<th>类型</th>
</tr>
<?php foreach ($list as $v) {?>
<tr>
	<td><?=$v['id']?></td>
	<td><?=$v['content']?></td>
	<td><?=$v['type']?></td>
</tr>
<?php }?>
</table>