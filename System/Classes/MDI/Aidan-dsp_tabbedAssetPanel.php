<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
	<link type="text/css" rel="stylesheet" HREF="System/Classes/MDI/sty_scrollBars.css">
</head>
<?php 
	$this->display->layout = "None";
	$this->param('OpenAssets','');
	$this->ATTRIBUTES['OpenAssets'] .= ",".ss_systemAsset('index.php');
?>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"  rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0" BGCOLOR="white">
<SCRIPT LANGUAGE="Javascript">
	function returnOpenAssets() {
		return MTreeRoot.getExpandedNodesList();	
	}
	
	function updateAssetOrder(theNode) {
		if (theNode.parentNode != null) {
			parentNode = theNode.parentNode;	
			currentNode = parentNode.firstChild;
			assetList = '';
			while (currentNode != null) {
				if (assetList.length) assetList += ',';
				assetList += currentNode.id;
				currentNode = currentNode.nextNode;
			}
			//alert(assetList);
			document.getElementById('MoveTreeLoader').src = 'index.php?act=Asset.UpdateOrder&ParentAssetID='+parentNode.id+'&AssetList='+escape(assetList);
		}		
	}
	
	function getMTreeRoot() {
		return MTreeRoot;	
	}
	
	function moveUp() {
		if (MTreeRoot.selectedNodeID != null) {
			theNode = MTreeRoot.nodes[MTreeRoot.selectedNodeID];
			theNode.moveUp();
			updateAssetOrder(theNode);
		}
			
	}
	function moveDown() {
		if (MTreeRoot.selectedNodeID != null) {
			theNode = MTreeRoot.nodes[MTreeRoot.selectedNodeID];
			theNode.moveDown();
			updateAssetOrder(theNode);
		}
	}
</SCRIPT>
<?php 
$result = new Request("Asset.Tree",array(
					'CallBack' 			=>	'parent.openAsset',
					'OnDoubleClick'		=>	'parent.openAsset',
					'OnClick'			=>	'parent.openAssetProperties',
					'NoHusk'			=>	TRUE,
					'Width'				=>	'100%',
					'Height'			=>	'100%',
					'Layout'			=>	'None',
					'Border'			=>	'0px',
					//'IncludeChildrenOf'	=> 	ListToKeyArray($this->ATTRIBUTES['OpenAssets']),
					'OpenAssets'		=> 	ListToKeyArray($this->ATTRIBUTES['OpenAssets']),
					'NoDiv'				=>	'Yes',
					'FilterByAdmin'		=>	true,
));
//ss_DumpVar("result ", $result);
print($result->display);
?>
	<IFRAME ID="MoveTreeLoader" SRC="" STYLE="width:100px; height:100px;display:none;"></IFRAME>

</body>
</html>
