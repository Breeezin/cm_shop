<?php header( "Cache-Control: max-age=60, public" ); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="keywords" 	     content="{[Keywords]}"/>
		<meta name="description" 	 content="{[Description]}"/>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<meta name="verify-v1" content="7L8s0FEC+/95JLaqhF9h/nCeOqjf9h9r5lq76z61idc=" />
		<title>{[WindowTitle]}</title>
	<link href="sty_reset.css" rel="stylesheet" type="text/css" />
	<link href="sty_grid.css" rel="stylesheet" type="text/css" />
	<link href="sty_layout.css" rel="stylesheet" type="text/css" />
	<link href="sty_main.css" rel="stylesheet" type="text/css" />	
	<link href="sty_layoutie.css" rel="stylesheet" type="text/css" />		
	<link href="sty_superfish.css" rel="stylesheet" type="text/css" />		
	<script type="text/javascript" src="scripts/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="scripts/hoverIntent.js"></script>
	<!--<script type="text/javascript" src="Scripts/addthis.js"></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-514930032f9be116"></script>-->
	<script type="text/javascript" src="scripts/superfish.js"></script>
	<script type="text/javascript">
	 $(document).ready(function(){ 
	       $("ul.nav").superfish(); 
	   }); 
	</script>
	<script defer type="text/javascript" src="Custom/ContentStore/Layouts/acmerockets/Scripts/pngfix.js"></script>
</head>
<body id="defaultlayout">
	<!-- top bar -->
	<div class="line topbar">
	<div class="wrap">
		<div class="unit size4of5 top"><? include("Custom/ContentStore/Layouts/acmerockets/country.php") ?></div>
		<div class="unit size1of5 topRight"><? include("Custom/ContentStore/Layouts/acmerockets/login.php") ?></div>
	</div>
	</div>
	<!-- /top bar -->
	<div class="wrap">
	
	<!-- content wrap bar -->
	<div class="line contentwrap">
    
		<div class="unit size1of5">
        <div id="logo-link img"><a href="/" id="logo-link"><img src="Custom/ContentStore/Layouts/acmerockets/Images/logo.png" alt="Acme Express Chilean Product Shop" width="206" height="190" border="0" /></a>
        </div>
        <!--<div class="announcement-wrap">
        <img src="Custom/ContentStore/Layouts/acmerockets/Images/newsletter-signup-acmerockets.png" width="206" height="277" alt="newsletter signup" />
        </div>-->
		<div class="categories border"><? include("Custom/ContentStore/Layouts/acmerockets/categories.php") ?></div>
		</div>
        
		<div class="unit size4of5">
		<!-- banner area -->
			<div class="line banner-line">
				<div class="unit size2of4 banner">
				<img src="Custom/ContentStore/Layouts/acmerockets/Images/default-banner.jpg" alt="chilean llamas acme rockets" />
				</div>
				<div class="unit size1of4">
				<div class="cart border"><? include("Custom/ContentStore/Layouts/acmerockets/cart.php") ?></div>
				</div>
				<div class="unit size1of4">
					
					<div class="search border"><form action="/Search?Stats=Yes" method="post" name="form1" id="form1" >
							<h4>Search</h4>
							
							<input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="18" onfocus="this.value='';" onblur="if (this.value.length==0) this.value='search site';" />
						<input name="imageField" type="submit" value=">" class="gold-button" />
					</form>  </div>
				</div>
			</div>
		<!-- banner area -->
		<div class="linemenu">
			<div class="unit menu border"><? include("Custom/ContentStore/Layouts/acmerockets/menu.php") ?></div>
		</div>
		<!-- content box -->
	<div class="line defaultcontentwrap border">
	<div class="defaultcontent">
	<h2>{[TitleImage]}</h2>
	<div class="curvybox stockalert boxshadow ">If a box you wanted is out of stock, you can <a href="Shop_System/Service/AddToWishList"> request a Personal Stock Alert here</a>. You will be emailed when stock arrives.</div>
    	<!-- AddThis Button BEGIN -->
        <div class="shareBox">
            <div class="addthis_toolbox addthis_default_style ">
            <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
            <a class="addthis_button_tweet"></a>
            <a class="addthis_button_google_plusone" g:plusone:annotation="bubble"></a>
            <a class="addthis_counter addthis_pill_style"></a>
            </div>
        </div>
        <!-- AddThis Button END -->
	{[Content]}
	</div>
	</div>
		<!-- /content box -->
	</div>
		
	</div>
	<!-- /content wrap bar -->

	<? include("Custom/ContentStore/Layouts/acmerockets/footer.php") ?>
	<? include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>
	</body>
</html>
