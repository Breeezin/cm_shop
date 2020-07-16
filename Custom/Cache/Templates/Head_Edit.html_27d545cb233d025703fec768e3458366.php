<script language="JavaSCript"><!--
function mykeyhandler() {
	if (window.event && window.event.keyCode == 8) { // try to cancel the backspace
		if (window.event.srcElement.tagName.toUpperCase() != "INPUT" && window.event.srcElement.tagName.toUpperCase() != "TEXTAREA") {
			window.event.cancelBubble = true;
			window.event.returnValue = false;
			return false;
		}		 
	}
}

document.onkeydown = mykeyhandler;
//--></script>


<script language="JavaScript" type="text/JavaScript">
<!--
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
//-->
</script>
<SCRIPT language="Javascript">
		<!--
			function _PB_onError(form_object, input_object, object_value, error_message)
		    {
				alert(error_message);
				input_object.focus()
		       	return false;	
		    }
		//-->
		</SCRIPT>
<STYLE TYPE="text/css">
			.required { color : red }
			.displayName { font-weight : bold } 
			.note { font-size : smaller }
			.errorFields {
				background : LightSkyBlue;
			}
			.fields {
				
			}

</STYLE>
<SCRIPT language="Javascript">
	
	function pipeWindow() {
		return window.frames.PipeFrame;
	}
	
	function moveAsset() {
		// open a popup to choose where to move
		window.open('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.Move&as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?>', 'MoveAsset', 'height=390,width=340,scrollbars');
	}

	
	function moveCallback(newAssetName) {
		// Get the tree to reload
		parent.assetReload();
		//processForm();
		document.forms.AssetForm.as_name.value = newAssetName;
	}

	function copyAsset(){
		// open a popup to choose where to copy
		window.open('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.Copy&as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?>', 'CopyAsset', 'height=390,width=450,scrollbars');
	}
	
	function copyCallback() {
		// get the asset panel to reload
		
		parent.assetReload();
		
		//processForm();
	}
	
	function deleteAsset() {
		// after confirming, open a popup to delete this asset
		if (confirm("Are you sure you wish to delete (also deletes sub assets !) ?")) {
			disableButtons();
			var pipe = pipeWindow();
			pipe.document.location = '<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.Delete&as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?>';
		}
	}
	
	function deleteCallback(assetsToClose) {
		// get the asset panel to reload and close this window (asset has been removed)
		parent.closeAssets(assetsToClose);
		// parent.closeDependantsOf(document.forms.AssetForm.AssetPath.value);
	}
	
	
	function viewAsset() {
		// alter form action and submit
		document.forms.AssetForm.ViewAfter.value = "Yes";
		//alert("Hoo Roo" + document.forms.AssetForm.ViewAfter.value);
		return true;
		//document.forms.AssetForm.SaveB.click();
	}
<? /*
	function subAssetUp() {
		// move the selected sub asset up in the list
		var assets = document.forms.AssetForm['SubAssets[]'].options;
		var selectedItem = assets.selectedIndex;
		
		if (selectedItem > 0) {	
			previous = assets[selectedItem - 1];
			assets[selectedItem - 1] 
				= new Option(  assets[selectedItem].text, assets[selectedItem].value, true, true);
			assets[selectedItem] = new Option( previous.text, previous.value, false, false);
		}
	}
	
	function subAssetDown() {
		// move the selected sub asset down in the list
		var assets = document.forms.AssetForm['SubAssets[]'].options;
		var selectedItem = assets.selectedIndex;
		
		if (selectedItem < (assets.length - 1)) {	
			previous = assets[selectedItem + 1];
			assets[selectedItem + 1] 
				= new Option(  assets[selectedItem].text, assets[selectedItem].value, true, true);
			assets[selectedItem] = new Option( previous.text, previous.value, false, false);
		}
	}
	
	function subAssetEdit() {
		// open a document window on the selected sub asset
		var assets = document.forms.AssetForm['SubAssets[]'].options;
		var selectedItem = assets.selectedIndex;
	
		window.parent.openAsset(assets[selectedItem].value, assets[selectedItem].text);
	
	}
	
	function subAssetAdd() {
		// open a popup window for adding a sub asset
		window.open('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.Add&as_parent_as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?>', 'AddSubasset', 'height=250,width=300,scrollbars');
	}
	
	function subAssetAddCallback(assetID, assetName, assetType) {
		parent.assetReload();
		processForm();
		parent.openAsset(assetID, assetName, null, null, null, assetType);
	}
	
	function subAssetRefresh() {
		var pipe = pipeWindow()
		pipe.document.location ='<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.ChildrenRefresh&as_id=<?php print(ss_HTMLEditFormat($data['as_id'])); ?>';
	}
	
	function subAssetRefreshCallback(childrenOptions) {
		var opts = document.forms.AssetForm['SubAssets[]'].options;
		
		for (var x = opts.length - 1; x >= 0; x--) {
			if (! inOptArray(childrenOptions, opts[x])) {
				opts[x] = null;
			}
		}
		
		for (var x = childrenOptions.length - 1; x >= 0; x--) {
			if (! inOptArray(opts, childrenOptions[x])) {
				opts[opts.length] = new Option(childrenOptions[x].text, childrenOptions[x].value, false); 
			}
		}
	}

*/ ?>	
	function inOptArray(optArray, opt) {
		for (var x = 0; x < optArray.length; x++) {
			if (optArray[x].value == opt.value && optArray[x].text == opt.text) 
				{ return true; }
		} 
		return false;	
	}

	var extraProcesses = new Array();
	
	function processForm() {
		// Make the subassetsortorder correct
		/*document.forms.AssetForm.subAssetSortOrder.value = "";
		for (var x = 0; x < document.forms.AssetForm['SubAssets[]'].options.length; x++) {
			document.forms.AssetForm.subAssetSortOrder.value += ',' + document.forms.AssetForm['SubAssets[]'].options[x].value;
		}*/
		
		for (var x = 0; x < extraProcesses.length; x++) {
			extraProcesses[x]();
		}
	}
	
</SCRIPT>