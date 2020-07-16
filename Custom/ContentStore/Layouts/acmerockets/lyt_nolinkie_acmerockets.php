<?php header( "Cache-Control: max-age=60, public" ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="keywords" 	     content="{[Keywords]}"/>
		<meta name="description" 	 content="{[Description]}"/>
		<meta http-equiv="content-type" content="text/html; charset=<?=['cfg']['Web_Charset']?>" />
		<meta name="verify-v1" content="7L8s0FEC+/95JLaqhF9h/nCeOqjf9h9r5lq76z61idc=" />
		<title>{[WindowTitle]}</title>
	<link href="sty_reset.css" rel="stylesheet" type="text/css" />
	<link href="sty_grid.css" rel="stylesheet" type="text/css" />
	<link href="sty_layout.css" rel="stylesheet" type="text/css" />
	<link href="sty_main.css" rel="stylesheet" type="text/css" />			
	<script type="text/JavaScript"></script>
</head>
<body id="defaultlayout" class="nolink">
	<div class="wrap">
	
	<!-- content wrap bar -->
	<div class="line contentwrap">
	
		
		<div class="unit size1of1">
		<!-- banner area -->
			<div class="line banner-line">
				<div class="unit size2of4"><img src="Custom/ContentStore/Layouts/acmerockets/Images/logo.png" alt="Acme Express Chilean Product Shop" width="157" height="166" border="0" />
				</div>
			
				<div class="unit size2of4 banner">
				<img src="Custom/ContentStore/Layouts/acmerockets/Images/default-banner.jpg" alt="chilean llamas acme rockets" />
				</div>
			</div>
		<!-- banner area -->
		<!-- content box -->
	<div class="line defaultcontentwrap border">
	<div class="defaultcontent">
	<h2>{[TitleImage]}</h2>
	{[Content]}
	</div>
	</div>
		<!-- /content box -->
	</div>
		
	</div>
	<!-- /content wrap bar -->
	<div class="line footer">
		<div class="unit size2of5">
		<div class="message">Purchase of llamas or similar products is subject to taxes in the destination country as per local laws and regulations. They are the responsibility of the buyer.</div>
		</div>
		<div class="unit size1of5 endofpage">
			<div class="wrapendofpage">
			<div class="security">
			<? include("Custom/ContentStore/Layouts/acmerockets/secure.php") ?>
			</div>
			<div class="cards">
			<img src="Custom/ContentStore/Layouts/acmerockets/Images/visa.png" alt="Visa" />
			</div>
			</div>
		</div>
	
	</div>
	
	</div>
		<? include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>
	</body>
</html>
