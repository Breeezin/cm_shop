<?php
	ss_RestrictPermission('CanAdministerAtLeastOneAsset');
	$this->display->title = 'Administration';
?>

<!---
	- Popup a window at 800 x 600 with the administration frame set.
	--->
<SCRIPT language="JavaScript">
	function isDefined(check) {
		try	{
			if (eval(check)) return true;
		} catch (e) {
			return false;	
		}
    }
    
	function openAdminWindow() {
		if (isDefined('NS_ActualOpen') || isDefined('SymRealWinOpen')) {
			document.write('<P>Zone Alarm, Norton Internet Security or Norton Personal Firewall has been detected on your computer.</P>');
			document.write('<P>Please disable any ad blocking and/or cookie control functions you have enabled in these program(s).</P>');
			document.write('<P>Please try <A HREF="Javascript:void(0);" ONCLICK="newWindow = window.open(\'index.php?act=TabbedInterface\', \'AdminWindow<?=md5($GLOBALS['cfg']['currentServer']);?>\', \'width=\' + (screen.availWidth) + \',height=\' + (screen.availHeight) + \',innerWidth=\' + (screen.width - 40) + \',innerHeight=\' + (screen.height - 80) + \',screenX=0,screenY=0,left=0,top=0,hotkeys,resizable\'); if (newWindow) { newWindow.focus(); }">clicking here</A> to view the administration interface.</P>');

			document.write('<P>If you are unsure how to do this then we recommend you consult your software\'s documentation or shutdown these program(s) completely while accessing your website administration area.</P>');


		} else {
			newWindow = window.open("index.php?act=TabbedInterface", "AdminWindow<?=md5($GLOBALS['cfg']['currentServer']);?>", 'width=' + (screen.availWidth) + ',height=' + (screen.availHeight) + ',innerWidth=' + (screen.width - 40) + ',innerHeight=' + (screen.height - 80) + ',screenX=0,screenY=0,left=0,top=0,hotkeys,resizable');
			if (newWindow) {
				document.write('<P>The administration interface will open in a new window.</P>');
				newWindow.focus();
			} else {
				document.write('<P>You appear to be running software that blocks pop-up windows. (e.g. Google Toolbar, Netscape)</P>');
				document.write('<P>Please try <A HREF="Javascript:void(0);" ONCLICK="newWindow = window.open(\'index.php?act=TabbedInterface\', \'AdminWindow<?=md5($GLOBALS['cfg']['currentServer']);?>\', \'width=\' + (screen.availWidth) + \',height=\' + (screen.availHeight) + \',innerWidth=\' + (screen.width - 40) + \',innerHeight=\' + (screen.height - 80) + \',screenX=0,screenY=0,left=0,top=0,hotkeys,resizable\'); if (newWindow) { newWindow.focus(); }">clicking here</A> to view the administration interface.</P>');
				document.write('<P>You may wish to add this site into your \'allow list\' to avoid seeing this message again.</P>');
			}
		}
	}
	openAdminWindow();
</SCRIPT>	
