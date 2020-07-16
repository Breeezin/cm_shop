<html>
<head>
		<title>{[SiteName]} - {[Title]}</title>
		<meta name="keywords" 	     content="{[Keywords]}">
		<meta name="description" 	 content="{[Description]}">
		<link rel="STYLESHEET" 		 type="text/css" href="sty_main.css"      media="screen">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style media="screen" type="text/css">
body {
margin:20px;}

.popup-shell {
background:#3d3c3c;
padding:5px;
margin-bottom:5px;
}

.popup-content {
background:#cbc8c1;
font-size:85%;
padding:20px;
} 

h2 {
font-family:Georgia, "Times New Roman", Times, serif;
color:#FFFFFF;
font-size:120%;
padding:0px;
margin:5px;}

.popup-footer {
background:#3d3c3c;
color:#CCCCCC;
font-size:90%;
padding:10px;}

</style>
</head>

<body>
<div>
  <div align="center"><img src="Custom/ContentStore/Layouts/acmerockets/Images/logo-onblack.gif" alt="acme rockets" /></div>
</div>
<div class="popup-shell">
	
			<h2>{[TitleImage]}</h2>
		<div class="popup-content">
		{[Content]}
								</div>
							
								</div>
								<div class="popup-footer">&copy; {[CurrentYear]} AcmeRockets.com
								<div align="right"><a href="javascript:window.close();" class="GoldText">[Close	Window]</a></div>
								</div>
<?php include("Custom/ContentStore/Layouts/acmerockets/tracker.php"); ?>
</body>
</html>
