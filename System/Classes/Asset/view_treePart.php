<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
	<title>Untitled</title>
	<SCRIPT LANGUAGE="Javascript">
	<!--
		var id = <?php print($this->ATTRIBUTES['as_id']); ?>;
		var theNode = parent.window.tree_1.getNodeById(id);
		if (theNode) {
		<?php
			$parent = $this->ATTRIBUTES['as_id'];
			
			foreach ($treeStructure as $node) {
				//$Click = "parent.openAsset({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
				$Click = $this->ATTRIBUTES['OnClick']."({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
				$DoubleClick = $this->ATTRIBUTES['OnDoubleClick']."({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
				print('var temp = theNode.newChild("icon'.$node['as_type'].'","'.ss_JSStringFormat($node['as_name']).'",'.$node['as_id'].',"'.ss_JSStringFormat($node['Path']).'","'.ss_JSStringFormat($Click).'","'.ss_JSStringFormat($DoubleClick).'"');
				if ($node['HasChildren']) print(',true');
				print(");\n");
			}
		?>		
//			theNode.newChild("page","New News",9,"/index.php/News/New News",parent.window.MT_UNOPENED_CHILDREN);
			// Must set to span display to 'block' mode before writing the HTML
			// or else IE 6.0 will display weird artifacts
			parent.window.MTreeNodeOpenClose(id,true);
			theNode.updateChildNodesHTML();
		}
	//-->
	</SCRIPT>
</head>
<body>
</body>
</html>


<?php


	
	
	
?>
