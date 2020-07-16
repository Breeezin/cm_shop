	<?php 
	//print ('<!----');
	//ss_DumpVar($this->ATTRIBUTES['OpenAssets'],'kk');
	//print ('---->');
		$display = "";
		if (strlen($this->ATTRIBUTES['TreeStyle'])) { 				
				$display .= "<p>{$this->ATTRIBUTES['TreeDescription']}</P><DIV ID=\"AssetTree\" STYLE=\"{$this->ATTRIBUTES['TreeStyle']}\" CLASS=\"treeBackground\">";
				$display .= "<TABLE BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"0\" WIDTH=\"100%\"><TR><TD ID=\"mainTree\">";
		} 
		
		print $display;
	?>
	<SCRIPT LANGUAGE="Javascript" SRC="System/Libraries/MTree/MTree2.js"></SCRIPT>
	<link type="text/css" rel="stylesheet" href="System/Libraries/MTree/MTree.css">
	<SCRIPT LANGUAGE="Javascript">
	<!--
		var closeAssets = new Array();
		function TreeOnClick(as_id,AssentName,AssetPath,AssetParentID,AssetParentPath) {
			<?=$this->ATTRIBUTES['TreeOnClick']?>
		}
		var nextHiddenIFrame = 0;
		function addChildren(id) {
			document.getElementById('TreeLoader'+nextHiddenIFrame).src = 'index.php?act=Asset.TreePart&OnClick=<?=$this->ATTRIBUTES['OnClick']?>&OnDoubleClick=<?=$this->ATTRIBUTES['OnDoubleClick']?>&as_id='+id;
			nextHiddenIFrame = (nextHiddenIFrame+1) & 7;
		}
		var tree_1 = null;
		
		<?php
			
			function defineTree( $onClick,$onDoubleClick,$node,$parent = 'tree',$index = 1, $filterBy) {
				$display = true;
				$nolink = false;
				if ($filterBy) {
					if (!$node['display'] and !$node['displayonlylink']) {
						$display = false;				
					} else {
						if (!$node['display']) {
							if($node['displayonlylink']) {
								$nolink = true;													
							} else {
								$display = false;
							}						
						}						
					}
				} 
				
				if ($display) {
					print("var {$parent}_{$index} = ");
					if ($parent == 'tree') {
						print("new MTreeNode");
					} else {
						print("{$parent}.newChild");
					}
					
					/*
					if (!strlen($onClick)) {
						//ss_DumpVar("node", $node);
						$Click = "parent.openAsset({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
					} else {
						$Click = $onClick."({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
					}
					*/
					if ($onClick == "void") {
						$Click = "void(0);";
					} else {
						if (!$nolink) {
							$Click = $onClick."({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";					
						} else {
							//$Click = 'alert(\'You do not have permission to administer this item.\');void(0);';
							$Click = '';
						}
					}
					
					if ($onDoubleClick == "void") {						
						$doubleClick = "void(0);";
					} else {
						if (!$nolink) {
							$doubleClick = $onDoubleClick."({$node['as_id']},'".ss_JSStringFormat($node['as_name'])."', '".ss_JSStringFormat($node['Path'])."', '".ss_JSStringFormat($parent)."', '".ss_JSStringFormat($node['ParentPath'])."', '".ss_JSStringFormat($node['as_type'])."');";
						} else {
							//$doubleClick = 'alert(\'You do not have permission to administer this item.\');void(0);';
							$doubleClick = '';
						}
					}
					//ss_DumpVar($node);				
					print('("icon'.$node['as_type'].'","'.ss_JSStringFormat($node['as_name']).'",'.$node['as_id'].',"'.ss_JSStringFormat($node['Path']).'", "'.ss_JSStringFormat($Click).'", "'.ss_JSStringFormat($doubleClick).'"');
					/*$tempShowLink = !$nolink?1:0;
					print('('.$tempShowLink.',"icon'.$node['as_type'].'","'.ss_JSStringFormat($node['as_name']).'",'.$node['as_id'].',"'.ss_JSStringFormat($node['Path']).'", "'.ss_JSStringFormat($Click).'", "'.ss_JSStringFormat($doubleClick).'"');
						*/				
					if ($node['HasChildren']) {
						print(',MT_UNOPENED_CHILDREN');
						//print(', true');
					} 
					print(');'."\n");
					if ($parent == 'tree') {				
						print ("{$parent}_{$index}.expandProvidedChildren = false;//{$node['as_id']}assetid\n");									
					}
					if (count($node['Children'])) {
						$newIndex = 1;
						foreach ($node['Children'] as $childNode) {
							if ($childNode['display'] or $childNode['displayonlylink']) {
								defineTree($onClick,$onDoubleClick,$childNode,"{$parent}_{$index}",$newIndex, $filterBy);
							}
							$newIndex++;
						}
					}
				}
			}
			
			// Define the tree	
			defineTree($this->ATTRIBUTES['OnClick'],$this->ATTRIBUTES['OnDoubleClick'], $treeStructure[0], 'tree', 1,$this->ATTRIBUTES['FilterByAdmin']);
			//ss_DumpVar("tree", $treeStructure);
			
			// Set up our callback functions
			/*
			foreach (array('onClick','onDoubleClick') as $action) {
				if (strlen($this->ATTRIBUTES[$action])) {
					print('tree_1.'.$action.' = '.$this->ATTRIBUTES[$action].";\n");
				}
			}*/
		?>
		
			tree_1.onAddChildren = addChildren;
			
			// MTree Settings
			tree_1.relativePathToMTree = 'System/Libraries/MTree/';	
			tree_1.relativePathToIcons = '<?php print($this->classDirectory); ?>/Images/';	
			tree_1.wantContainerDiv = true;				
			
			// Render the tree
			document.write(tree_1.render());
		
		<?
		foreach($this->ATTRIBUTES['OpenAssets'] as $id => $value) {			
			if ($id != 1) {
				print ("MTreeSelectNode($id)\n");											
				print ("MTreeNodeOpenClose($id)\n");											
			}
		}
		?>
		//MTreeSelectNode(500);
		//MTreeNodeOpenClose(500,false);
	//-->
	</SCRIPT>
	<?php 
		$display = "";
		if (strlen($this->ATTRIBUTES['TreeStyle'])) { 				
				$display .= "</DIV></TD></TR></TABLE>";
		} 
		print $display;
	?>
	<!-- Hidden IFRAME used to progressively load the tree -->
	<IFRAME ID="TreeLoader0" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader1" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader2" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader3" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader4" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader5" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader6" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>
	<IFRAME ID="TreeLoader7" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>


