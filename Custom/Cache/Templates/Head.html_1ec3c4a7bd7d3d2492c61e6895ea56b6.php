	if (document.all || document.layers) {
		self.moveTo(0,0); 
		self.resizeTo(screen.availWidth,screen.availHeight); 
	}
	
	
	// These are used so we can just focus a tab if any of the
	// shop tabs are already open.
	var ShopProductsFakeAssetID = -2;
	var ShopCategoriesFakeAssetID = -3;
	var ShopOptionsFakeAssetID = -4;
	var ShopAttributesFakeAssetID = -5;
	var ShopOrdersFakeAssetID = -6;

	var popup_window_already_open = false;
	var popup_opener = null;
	var popup_windows_supported = false;
	
	var CurrentTopTab = 1;

	var NextFakeAssetID = -10;
	var fakeAssetID = new Array();
	 
	var maxWindows = <?php print(ss_HTMLEditFormat($data['maxWindows'])); ?>;
	
	var windows = new Array();
	var windowsMenuID;
	var assetPanel,assetTree,taskBar,taskBarCell,tabbedPagesWidth,tabbedPagesHolder,windowCell,menuBar,assetPanelHolder,assetPanelHeight;
	var assetTreeTools,propertiesBar,propertiesPanel,propertiesBarCell;
	var assetBar,assetBarCell;
	var shopBar,shopBarCell,shopPanel;
	var newsletterBar,newsletterBarCell,newsletterPanel;
	var siteComponents;
	var backButton,forwardButton;
	var winCount = 0;
	var topZIndex = 1;
	var windowList = new Array();
	var windowTitles = new Array();
	var windowIcons  = new Array();
	var currentWindow = -1;
	var expandMenus = false;
	
	var ReportsBeenOpened = false;
	var ConfigurationBeenOpened = false;
	var UsersBeenOpened = false;
	
	function updateTaskBarNonContent(which) {
		var titleText;
		
	
		temp = '<table width="100%" height="24" border="0" cellpadding="0" cellspacing="0"><tr>';
		windowsMenuHTML = "";
		tabStartImage = 'tab-start.gif';
		cellClass = 'tab-on';
		textClass = 'tabtext-on';

		// Restrict the length of displayed title
		if (which == 'ReportsAndStats') {
			titleText = 'Reports and Stats';
		} else {
			titleText = which;
		}
		
		while (titleText.indexOf('&nbsp;') > 0) titleText = titleText.replace('&nbsp;',' ');
		if (titleText.length > 35) titleText = titleText.substring(0,32) + '...';
		while (titleText.indexOf(' ') > 0) titleText = titleText.replace(' ','&nbsp;');
				
		temp = temp + '<td STYLE="cursor: hand" width="7" class="'+cellClass+'"><img src="Images/'+tabStartImage+'" width="7" height="24"></td>';
		temp = temp + '<td onclick="focusWindow('+windowList[i]+');" width="150" background="Images/tab-inner.gif" class="'+cellClass+'"><table STYLE="cursor: hand" width="100%" border="0" cellpadding="2" cellspacing="0" class="'+textClass+'"><tr><td><div align="center">';
		temp = temp + titleText;
		temp = temp + '</div></td></tr></table></td><td width="7" class="'+cellClass+'"><img src="Images/tab-end.gif" width="7" height="24"></td>';
				
		temp = temp + '<td class="line-notab" align="right">&nbsp;</td></tr></table>';
	
		taskBarCell.innerHTML = temp;
		
	}
	
	
	function updateTaskBar() {
		var titleText;
		
	
		temp = '<table width="100%" height="24" border="0" cellpadding="0" cellspacing="0"><tr>';
		windowsMenuHTML = "";
		tabStartImage = 'tab-start.gif';
		for (i=0; i<winCount; i++) {
			if (windowList[i] != -1) {
				
				if (currentWindow != windowList[i]) {
					cellClass = 'tab-bottomline';
					textClass = 'tabtext-off';
				} else {
					cellClass = 'tab-on';
					textClass = 'tabtext-on';
				}
				
				// Restrict the length of displayed title
				titleText = windowTitles[i];
				while (titleText.indexOf('&nbsp;') > 0) titleText = titleText.replace('&nbsp;',' ');
				if (titleText.length > 35) titleText = titleText.substring(0,32) + '...';
				while (titleText.indexOf(' ') > 0) titleText = titleText.replace(' ','&nbsp;');
				
				temp = temp + '<td STYLE="cursor: hand" width="7" class="'+cellClass+'"><img src="Images/'+tabStartImage+'" width="7" height="24"></td>';
				temp = temp + '<td onclick="focusWindow('+windowList[i]+');" width="150" background="Images/tab-inner.gif" class="'+cellClass+'"><table STYLE="cursor: hand" width="100%" border="0" cellpadding="2" cellspacing="0" class="'+textClass+'"><tr><td><div align="center">';
				temp = temp + titleText;
				temp = temp + '</div></td></tr></table></td><td width="7" class="'+cellClass+'"><img src="Images/tab-end.gif" width="7" height="24"></td>';
				
				tabStartImage = 'tab-start-inner.gif';
			}
		}
		temp = temp + '<td class="line-notab" align="right">&nbsp;';
		if (currentWindow != 0) {
			temp += '<A HREF="javascript:closeWindow(currentWindow);"><IMG SRC="Images/close.gif" BORDER="0" WIDTH="18" HEIGHT="16"></A>';
		} else {
			temp += '<IMG SRC="Images/holder.gif" BORDER="0" WIDTH="19" HEIGHT="17"></A>';
		}
		
		temp += '</td></tr></table>';
	
		taskBarCell.innerHTML = temp;
		setCurrentTopTab(1);
		
	}
	
	function documentHeight() {
		// if is netscape
		var bName = navigator.appName;
		var bVer = parseFloat(navigator.appVersion);
		
		if (bName == "Netscape") {
  			return this.innerHeight;
		} else {
			//alert(bName + " " + document.body.clientHeight);
  			return document.body.clientHeight;
		}
	}
	
	function documentWidth() {
		var bName = navigator.appName;
		var bVer = parseFloat(navigator.appVersion);
		
		if (bName == "Netscape") {
  			return this.innerWidth;
		} else {
			//alert(bName + " " + document.body.clientWidth);
  			return document.body.clientWidth;
		}
	}
	
	function popup_window_resize(width,height,dontshow) {
		// find the frame for the windows
		popwin = document.getElementById('popupWindow');

		popwin.style.top = (documentHeight()-height)/2;
		if ((documentHeight()-height)/2 < 0) popwin.style.top = 0;
		
		popwin.style.left = (documentWidth()-width)/2;
		if ((documentWidth()-width)/2 < 0) popwin.style.left = 0;
		
		if (width > documentWidth()-10) width = documentWidth()-10;
		popwin.style.width = width;
		
		if (height > documentHeight()-15) height = documentHeight()-15;
		popwin.style.height = height;
		
		if (!dontshow) popwin.style.display = '';
	}
	
	function popup_window_settitle(title) {
		// set the title
		poptitle = document.getElementById('popupWindowTitle');
		poptitle.innerHTML = title;
	
	}
	
	// display a fake popup window because the normal one is so slow
	function popup_window_open(popparent,url,name,title,width,height,hideuntilresize) {
	
		popup_opener = popparent;
	
		if (popup_window_already_open) {
			alert('Please close the popup window first!');
			return false;
		}
		popup_window_already_open = true;	

		// find the frame for the windows
		popwin = document.getElementById('popupWindow');
		
		// set the title
		popup_window_settitle(title);
		
		// position/size the popup
		popup_window_resize(width,height,true);
		
		// load the url
		popframe = document.getElementById('FPopupWindow');
		popframe.src = url;
		
		// show the window
		if (!hideuntilresize) popwin.style.display = '';
		
		return window.frames.FPopupWindow;
	}

	function popup_window_close() {
		popup_window_already_open = false;

		// hide the window
		popwin.style.display = 'none';
	
		// hit the 'loading' page
		popframe = document.getElementById('FPopupWindow');
		popframe.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html';
	}
	
	
	function closeWindow(index) {
		win = windows[index];
	
		// Free the window
		win.inUse = false;
		
		// Hide it
		win.wnFrame.style.visibility = 'hidden';
	
		// Update the page being displayed
		win.wnFrame.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>loading.html';
	
		// Remove the entry from the task bar
		windowList[win.tabPosition] = -1;
		windowTitles[win.tabPosition] = '';
		
		// Find new window to display.. 
		// first try to find the one to the right of it
		found = false;
		for (i=win.tabPosition+1; i<winCount; i++) {
			if (windowList[i] != -1) {
				focusWindow(windowList[i]);
				found = true;
				break;
			}		
		}
		// If can't find a window right of it.. try to the left
		if (!found) {
			for (i=win.tabPosition-1; i>=0; i--) {
				if (windowList[i] != -1) {
					focusWindow(windowList[i]);
					found = true;
					break;
				}		
			}
		}
		
		// If no windows left...
		if (!found) {
			currentWindow = -1;
		}
		
		// Update the task bar display
		updateTaskBar();
		
	}
	
	function assetReload() {
		var openAssets = window.frames.AssetPanelFrame.returnOpenAssets();
		var currentAssetEdit = null;
		/*
		for (var i=0; i<maxWindows; i++) {
			if (windows[i].wnFrame.style.visibility != 'hidden') {						
				if (windows[i].wnFrame.document.forms.AssetForm && windows[i].wnFrame.document.forms.AssetForm.as_id) {
					currentAssetEdit = windows[i].wnFrame.document.forms.AssetForm.as_id.value;					
					break;
				}
			}
		}
		if (currentAssetEdit != null) {						
			openAssets = openAssets + currentAssetEdit + ","; 			
		}
		alert(openAssets);
		*/
		document.getElementById('assetPanel').src = '<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=TabbedAssetPanel&OpenAssets='+escape(openAssets);
	}

	function showNonContentTab(which) {
		for (var i=0; i<maxWindows; i++) {
			if (windows[i].wnFrame.style.visibility != 'hidden') windows[i].wnFrame.style.visibility = 'hidden';
		}
		var next,frame;
		next = 'ReportsAndStats';	frame = document.getElementById('F'+next); 
		if (which != next && frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
		next = 'Configuration';	frame = document.getElementById('F'+next); 
		if (which != next && frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
		next = 'Users';	frame = document.getElementById('F'+next); 
		if (which != next && frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
		
		// Get the frame we're displaying
		var wnFrame = document.getElementById('F'+which);

		if (which == 'ReportsAndStats') {
			if (!ReportsBeenOpened) {
				wnFrame.src = 'index.php?act=Statistics.Display';
				ReportsBeenOpened = true;
			}
		}
		if (which == 'Users') {
			if (!UsersBeenOpened) {
				<?php if ($data['Authencated']) { ?>
				wnFrame.src = 'index.php?act=Asset.Edit&as_id=2&SoHeight=50';
				<?php } ?>
				UsersBeenOpened = true;
			}
		}		
		
		// Bring the window to the top
		topZIndex = topZIndex + 1;
		wnFrame.style.zIndex = topZIndex;

		/*if (which == 'Users') {			
			if (window.frames['FrameUsers'].subAssetRefresh) {
				window.frames['FrameUsers'].subAssetRefresh();
			}
		}*/
		
		// Display the window
		var properWidth =  documentWidth()-<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageRightMargin'])); ?>;
		var properHeight = documentHeight()-<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageBottomMargin'])); ?>;
	
		// Note, no need to resize unless size is different (browser probably knows this anyway, but just make sure)
		if (wnFrame.style.width != properWidth)   wnFrame.style.width = properWidth;
		if (wnFrame.style.height != properHeight) wnFrame.style.height = properHeight;
		wnFrame.style.visibility = 'visible';
	
		updateTaskBarNonContent(which);
	
	}
	
	function showContentTab() {
		focusWindow(currentWindow);
	}
	
	function focusWindow(windowID) {
	
		for (var i=0; i<maxWindows; i++) {
			if (i != windowID) {
				if (windows[i].wnFrame.style.visibility != 'hidden') windows[i].wnFrame.style.visibility = 'hidden';
			}
		}
		var next,frame;
		next = 'ReportsAndStats';	frame = document.getElementById('F'+next); 
		if (frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
		next = 'Configuration';	frame = document.getElementById('F'+next); 
		if (frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
		next = 'Users';	frame = document.getElementById('F'+next); 
		if (frame.style.visibility != 'hidden') frame.style.visibility = 'hidden';
	
		// Get the window object we're displaying
		win = windows[windowID];
		
		// Bring the window to the top
		topZIndex = topZIndex + 1;
		win.wnFrame.style.zIndex = topZIndex;
		
		// Display the window
		var properWidth =  documentWidth()-<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageRightMargin'])); ?>;
		var properHeight = documentHeight()-<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageBottomMargin'])); ?>;
		
		// Note, no need to resize unless size is different (browser probably knows this anyway, but just make sure)
		if (win.wnFrame.style.width != properWidth)   win.wnFrame.style.width = properWidth;
		if (win.wnFrame.style.height != properHeight) win.wnFrame.style.height = properHeight;
		win.wnFrame.style.visibility = 'visible';
				
		// Get the window to reload it's children if it cam
		/*if (currentWindow != windowID) {
			// Window is changing, make sure we have fresh subAssets
			if (window.frames['Frame' + windowID].subAssetRefresh) {
				window.frames['Frame' + windowID].subAssetRefresh();
			}
		}*/
		// Update the taskbar	
		currentWindow = windowID;
		updateTaskBar();
//		window.frames['Frame' + windowID].focus();
	}
	
	function openWindowCount() {
		var counter = 0;	
		for (var i=0; i < maxWindows; i++) {
			if (windows[i].inUse){
				counter++;
			}
		}
		return counter;
	}
	
	function newWindow(url,title, longtitle, icon) {
		// Find an unused window
		var windowID = -1;
		
		for (var i=0; i < maxWindows; i++) {
			//alert(windows[i].inUse);
			if (! windows[i].inUse){
				windowID = i;
				windows[i].inUse = true;
				break;
			}
		}
		
		if (windowID == -1) {
			alert("No Free Windows.");
			return null;// -1;
		}
		
		// Update the page being displayed
		
		//alert(window.frames['Frame' + windowID].document);
//		window.frames['Frame' + windowID].document.location = url;
		windows[windowID].wnFrame.src = url;
		//window.frames['Frame' + windowID].document.location = url;
		
		windows[windowID].url = url;
		
		
		// Add the new window to the task bar	
		windowList[winCount] = windowID;
		windowTitles[winCount] = title;
		if (icon) {
			windowIcons[winCount] = icon;
		} else {
			windowIcons[winCount] = 'Images/iconPage.gif';
		}
		windows[windowID].tabPosition = winCount;
		winCount++;

		focusWindow(windowID);
		
		if (tabbedPagesHolder.offsetWidth != tabbedPagesWidth) {
			closeWindow(windowID);
			alert('Too many assets open. Please close one or more and try again.');
		}
		//alert("URL:" +url + " Location:"+ window.frames['Frame' + windowID].document.location + " WinUrl:" + windows[windowID].url);
		
//		updateTaskBar();
		return windowID;
		
	}

	function getDim(el){
		for (var lx=0,ly=0;el!=null;
			lx+=el.offsetLeft,ly+=el.offsetTop,el=el.offsetParent);
		return {left:lx,top:ly}
	}
	

	function resizeAssetPanel() {

		var dims = getDim(siteComponents);
		var currentTop = dims.top;
		var currentLeft = dims.left;
		var currentHeight = siteComponents.offsetHeight;
		var currentBottom = dims.top + currentHeight;
		
		// Align all the bars and panels appropriately on the left
		assetBar.style.left = currentLeft;
		assetPanel.style.left = currentLeft+3;
		shopBar.style.left = currentLeft;
		shopPanel.style.left = currentLeft;
		newsletterBar.style.left = currentLeft;
		newsletterPanel.style.left = currentLeft;
		assetTreeTools.style.left = currentLeft;
		propertiesPanel.style.left = currentLeft;
		propertiesBar.style.left = currentLeft;
		
		// If the asset bar is open
		if (assetBarCell.className == 'assetBarOpen') {
			// Fill from the bottom up		
			
			if (propertiesBarCell.className == 'assetBarOpen') {
				// Position the shop panel
				currentBottom -= <?php print(ss_HTMLEditFormat($data['PropertiesPanelHeight'])); ?>;
				propertiesPanel.style.top = currentBottom+1;
				propertiesPanel.style.display = '';
			} else {
				// Hide the shop panel		
				//propertiesPanel.style.top = <?php print(ss_HTMLEditFormat($data['HidePosition'])); ?>;
				propertiesPanel.style.display = 'none';
			}
			// Position the shop bar
			currentBottom -= 20;
			propertiesBar.style.top = currentBottom;
			propertiesBar.style.display = '';
			
			// Position the website bar at the top and incresase the current top pos
			assetBar.style.top = currentTop;	
			assetBar.style.display = '';	
			currentTop += 21;
			assetTreeTools.style.top = currentTop;
			assetTreeTools.style.display = '';
			currentTop += 23;
			// Position the website panel and set the height		
			assetPanel.style.top = currentTop;  
			assetPanel.style.display = '';
			assetPanelHeight = currentBottom-currentTop;
			assetPanel.style.height = assetPanelHeight;	
		
		} else {
			// Fill from top to bottom
			
			// Position the website bar at the top and incresase the current top pos
			assetBar.style.top = currentTop;	
			assetBar.style.display = '';
			currentTop += 20;
			// Hide the website panel
			//assetPanel.style.top = <?php print(ss_HTMLEditFormat($data['HidePosition'])); ?>;		
			//assetTreeTools.style.top = <?php print(ss_HTMLEditFormat($data['HidePosition'])); ?>;
			assetPanel.style.display = 'none';		
			assetTreeTools.style.display = 'none';
			
			// Position the properties bar
			propertiesBar.style.top = currentTop;			
			propertiesBar.style.display = '';
			currentTop += 20;
			if (propertiesBarCell.className == 'assetBarOpen') {
				// Position the properties panel
				propertiesPanel.style.top = currentTop+1;	
				propertiesPanel.style.display = '';
				currentTop += <?php print(ss_HTMLEditFormat($data['PropertiesPanelHeight'])); ?>;
			} else {
				// Hide the panel		
				//propertiesPanel.style.top = <?php print(ss_HTMLEditFormat($data['HidePosition'])); ?>;
				propertiesPanel.style.display = 'none';
			}
			
	
			
		}
		
	}
	
	function resizeScreenStuff() {
	
		resizeAssetPanel();		

		// This needs updating or else the size will be wrong when we
		// try to open more windows
		tabbedPagesWidth = tabbedPagesHolder.offsetWidth;
	//	assetTree.style.height = documentHeight()-20;
//		taskBar.style.width = documentWidth()-200-30;
//		taskBarCell.style.width = documentWidth()-200-30;
//		taskBarCell.style.height = 20;
//		windowCell.style.width = documentWidth()-200-30;
//		windowCell.style.height = documentHeight()-40-20;
	
	}
	
	function init() {
	
		// Get a few ID's that are needed
		siteComponents = document.getElementById('siteComponents');
		
		assetBar = document.getElementById('assetBar');
		assetBarCell = document.getElementById('assetBarCell');
		assetPanel = document.getElementById('assetPanel');
		assetTreeTools = document.getElementById('assetTreeTools');
		
		propertiesBar = document.getElementById('propertiesBar');
		propertiesBarCell = document.getElementById('propertiesBarCell');
		propertiesPanel = document.getElementById('propertiesPanel');

		
		shopBar = document.getElementById('shopBar');
		shopBarCell = document.getElementById('shopBarCell');
		shopPanel = document.getElementById('shopPanel');


		newsletterBar = document.getElementById('newsletterBar');
		newsletterBarCell = document.getElementById('newsletterBarCell');
		newsletterPanel = document.getElementById('newsletterPanel');
		
		
		taskBar = document.getElementById('TaskBar');
		taskBarCell = document.getElementById('TaskBarCell');
		tabbedPagesHolder = document.getElementById('tabbedPagesHolder');
		
		
		
		windowCell = document.getElementById('WindowCell');
	
		assetPanel.style.visibility = 'visible';
	
		// Make everything fit the window
		resizeScreenStuff();
		
		// Get ID's of the Windows and IFRAMEs
		for (i = 0; i < maxWindows; i++) {
			windows[i] = new Object();
	
			// get the ID of the IFRAME
			windows[i].wnFrame = document.getElementById('F' + i);
			
			// Flag the window as available for use
			windows[i].inUse    = false;
			
			windows[i].url = 'BlahBlahBlahNeverUseThisURL';
	
		}
	
		// Insert the taskbar at the top of the window
		updateTaskBar();
		
		window.onresize = function(e){
			resizeScreenStuff();
			for (var i=0; i<maxWindows; i++) {
				windows[i].wnFrame.style.width = documentWidth()-<?php print(ss_HTMLEditFormat($data['TabbedPageLeft'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageRightMargin'])); ?>;
				windows[i].wnFrame.style.height = documentHeight()-<?php print(ss_HTMLEditFormat($data['TabbedPageTop'])); ?>-<?php print(ss_HTMLEditFormat($data['TabbedPageBottomMargin'])); ?>;
			}
		}
		
		topTabsElement = document.getElementById('topTabs');
		topTabsElement.style.display = '';
		
		openWelcomePanel();
	}
	
	function openNamedNonAssetPanel(path,name,handle) {
		if (!fakeAssetID[handle]) fakeAssetID[handle] = NextFakeAssetID--;
		openNonAssetPanel(path,name,null,fakeAssetID[handle]);
	}
	
	function openNonAssetPanel(path,name,status,fakeassetid) {
		// First make see if a window already has it 
		var opened = 0;
		for (var i = 0; i < maxWindows; i++) {
			if (windows[i].inUse && (windows[i].assetID == fakeassetid)) {
				focusWindow(i);
				opened = 1;
			}
		}
		if (opened == 0) {
			var winID = newWindow(path, name, status);
			if (winID != null && winID >= 0) {
				windows[winID].assetID = fakeassetid;
			}
		}
	}
	
	function openWebsitePanel() {
		var websiteWinID = 
				newWindow('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=WebsitePanel', "Website", "Website");
				if (websiteWinID != null && websiteWinID >= 0) {
					windows[websiteWinID].assetID = -1;
				}
	}
	
	
	function openWelcomePanel() {
		var statWinID = 
				newWindow('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=TabbedInterfaceWelcome', "Welcome", "Welcome", "<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>Images/icon_Statistics.gif");
		
		if (statWinID != null && statWinID >= 0) {
				windows[statWinID].assetID = -1;
		}
		
	}

	function openAssetProperties(assetID, assetName, path, parentlink, parentpath, type) {
		document.getElementById('PropertiesLoader').src = 'index.php?act=Asset.PropertiesPanel&as_id='+assetID;
	}
	
	
	function openAsset(assetID, assetName, path, parentlink, parentpath, type) {
		
		var soHeight = screen.availHeight - (<?php print(ss_HTMLEditFormat($data['PageHeaderHeight'])); ?> + <?php print(ss_HTMLEditFormat($data['AssetHeaderHeight'])); ?> + <?php print(ss_HTMLEditFormat($data['AssetFooterHeight'])); ?>);
		

		if (soHeight < 200) {
			soHeight = 200;
		}
		
		// First make see if a window already has it 
		var opened = 0;
		for (var i = 0; i < maxWindows; i++) {
			if (windows[i].inUse && (windows[i].assetID == assetID)) {
				focusWindow(i);
				opened = 1;
			}
		}
		if (opened == 0) {
			var winID = 
			newWindow('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=Asset.Edit&as_id=' + escape(assetID) + '&soHeight=' + escape(soHeight), assetName, assetName, '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>/Templates/Images/icon'+type+'.gif');
			if (winID != null && winID >= 0) {
				windows[winID].assetID = assetID;
			}
		}
	}
	
	
	function runOnDependantsOf(assetPath,functionName) {
		for (var i = 0; i < maxWindows; i++) {
			if (windows[i].inUse && windows[i].assetPath && 
				(windows[i].assetPath.length >= assetPath.length)) 
			{
				var c1 = windows[i].assetPath.substr(0,assetPath.length);
				if (assetPath.toUpperCase() == c1.toUpperCase()) { 
					// Got a match, this window is dependant on the
					// asset path
					if (window.frames['Frame' + i][functionName]) {
						window.frames['Frame' + i][functionName]();
					}
				}	
			}
		}
		assetReload();
	}
	
	function closeDependantsOf(assetPath) {
		//alert('1 ' + assetPath);
		for (var i = 0; i < maxWindows; i++) {
			//alert('2 ' + assetPath + ' ' + windows[i].assetPath);
			if (windows[i].inUse && windows[i].assetPath && 
				(windows[i].assetPath.length >= assetPath.length)) 
			{
				var c1 = windows[i].assetPath.substr(0,assetPath.length);
				//alert(assetPath.toUpperCase() + ' ' +  c1.toUpperCase());
				if (assetPath.toUpperCase() == c1.toUpperCase()) {
					// Got a match, this window is dependant on the
					// asset path
					//alert('Match');
					closeWindow(i);
				}	
			}
		}
		assetReload();
	}
	
	function closeAssets(assetArray) {
		propertiesForm = document.forms.PropertiesPanelForm;
		for (var x = 0; x < assetArray.length; x++) {		
			if (propertiesForm.as_id.value.length) {
				if (propertiesForm.as_id.value == assetArray[x]) {
					propertiesForm.as_id.value = null;
					propertiesForm.as_name.disabled = true;
					propertiesForm.as_name.value = '';
					propertiesForm.as_appear_in_menus.disabled = true;
					propertiesForm.as_appear_in_menus.checked = false;
					document.getElementById('assetTypeProperties').innerHTML = '&nbsp';
				}
			}
		
			for (var i = 0; i < maxWindows; i++) {
				//alert('2 ' + assetPath + ' ' + windows[i].assetPath);
				if (windows[i].inUse && windows[i].assetID && 
					(windows[i].assetID == assetArray[x])) 
				{
					closeWindow(i);	
				}
			}
		}
		//assetReload();
	}
		
	
	function changeTitle(assetID,newTitle) {
	//		alert('changeTitle');
		// Change the title of a displayed window
	
		// Find a window with the correct asset path
		for (var i=0; i<maxWindows; i++ ) {
			if (windows[i].inUse && (windows[i].assetID == assetID)) {
				// Update the title of the tab
				windowTitles[windows[i].tabPosition] = newTitle;
			}
		}
		updateTaskBar();
	
	}
	
	function togglePanel(name) {
		bar = document.getElementById(name+'BarCell');
		
		if (bar.className == 'assetBarOpen') {
			bar.className = 'assetBarClosed';
		} else {
			bar.className = 'assetBarOpen';
		}
		resizeAssetPanel();		
	}
	
	function tabMouseOver(which) {
		if (CurrentTopTab != which) {
			tab = document.getElementById('Tab'+which);
			tab.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>/Templates/Images/tab-'+which+'-on.gif';
		}
	}
	
	function tabMouseOut(which) {
		if (CurrentTopTab != which) {
			tab = document.getElementById('Tab'+which);
			tab.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>/Templates/Images/tab-'+which+'.gif';
		}
	}
	
	function setCurrentTopTab(which) {
		if (which != CurrentTopTab) {
			tab = document.getElementById('Tab'+CurrentTopTab);
			tab.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>Templates/Images/tab-'+CurrentTopTab+'.gif';
			tab.visibility = 'visible';
			
			CurrentTopTab = which;

			tab = document.getElementById('Tab'+CurrentTopTab);
			tab.src = '<?php print(ss_HTMLEditFormat($data['RelativeHere'])); ?>Templates/Images/tab-'+CurrentTopTab+'-on.gif';
			tab.visibility = 'visible';
		}
	}
	
	function subAssetAddCallback(assetID, assetName, assetType) {
		assetReload();
		if (assetID != null) {
			openAsset(assetID, assetName, null, null, null, assetType );
		}
	}

	function openWebsitePanel() {
		var websiteWinID = 
			newWindow('<?php print(ss_HTMLEditFormat($data['Script_Name'])); ?>?act=WebsitePanel', "Website", "Website");
			if (websiteWinID != null && websiteWinID >= 0) {
				windows[websiteWinID].assetID = -1;
			}
	}

	function fileClose() {
		if (currentWindow > 0) {
			closeWindow(currentWindow); 
		} else { 
			alert('Cannot close current window'); 
		}
	}
	
	function fileExit() {
		if (confirm('You will lose all unsaved work. Click OK to exit.')) {
			window.close();
		}
	}	
	
	function moveAssetUp() {
        var places = prompt("How many places would you like this asset moved?","1");
        window.frames.AssetPanelFrame.moveUp(places);
	}
	
	function moveAssetUp5() {
		window.frames.AssetPanelFrame.moveUp5();
	}
	
	function moveAssetDown() {
        var places = prompt("How many places would you like this asset moved?","1");
        window.frames.AssetPanelFrame.moveDown(places);
	}

	function moveAssetDown5() {
		window.frames.AssetPanelFrame.moveDown5();
	}	
	
	function addFromTree(type) {
		MTreeRoot = window.frames.AssetPanelFrame.getMTreeRoot();
		if (MTreeRoot.selectedNodeID) {
			parentAsset = MTreeRoot.nodes[MTreeRoot.selectedNodeID].id;
		} else {
			parentAsset = 1; 	// default to index.php as parent
		}
		document.getElementById('AddAssetFromTreeLoader').src = 'index.php?act=Asset.AddFromTree&as_type='+type+'&as_parent_as_id='+parentAsset;
	}
	
	function deleteFromTree() {
		MTreeRoot = window.frames.AssetPanelFrame.getMTreeRoot();
		if (MTreeRoot.selectedNodeID) {
			path = MTreeRoot.nodes[MTreeRoot.selectedNodeID].value;
			if (confirm('Are you sure you want to delete item '+path+'?')) {
				asset = MTreeRoot.nodes[MTreeRoot.selectedNodeID].id;
				document.getElementById('AddAssetFromTreeLoader').src = 'index.php?act=Asset.DeleteFromTree&as_id='+asset;
			} else {
				alert('The item was not deleted.');
			}
		} else {
			alert('Please select an item to delete.');
		}
	}	
