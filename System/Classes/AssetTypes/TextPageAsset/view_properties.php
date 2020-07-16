<?php
	ss_paramKey($asset->cereal,'AST_PAGE_PAGECONTENT','');
	ss_paramKey($asset->layout,'LYT_LAYOUT','default');
	ss_paramKey($asset->layout,'LYT_STYLESHEET','main');

	$pageSize = sprintf("%1.1f",(strlen($asset->cereal['AST_PAGE_PAGECONTENT'])/1024));
?>	
<table cellpadding="0" cellspacing="2" width="100%">
	<tr>
		<td class="propertiesLabel">Size :</td>
		<td><?=$pageSize?> KiB</td>
	</tr>
	<tr>
		<td class="propertiesLabel">Layout :</td>
		<td><?=ss_HTMLEditFormat($asset->layout['LYT_LAYOUT'])?></td>
	</tr>
	<tr>
		<td class="propertiesLabel">Stylesheet :</td>
		<td><?=ss_HTMLEditFormat($asset->layout['LYT_STYLESHEET'])?></td>
	</tr>
</table>