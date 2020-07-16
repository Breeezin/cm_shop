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
	<link href="sty_main.css" rel="stylesheet" type="text/css" />			
	<link href="sty_layout.css" rel="stylesheet" type="text/css" />
	<link href="sty_superfish.css" rel="stylesheet" type="text/css" />		
	<script type="text/javascript">
	 $(document).ready(function(){ 
	       $("ul.nav").superfish(); 
	   }); 
	</script>
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
        <div class="logo-wrap">
			<a href="/" id="logo-link"><img src="Custom/ContentStore/Layouts/acmerockets/Images/logo.png" alt="Acme Express Chilean Product Shop" width="206" height="190" border="0" /></a>
		</div>
		<div class="categories border"><? include("Custom/ContentStore/Layouts/acmerockets/categories.php") ?></div>
		<a href="/Shop_System/Service/Engine/Gateway/32"><img src="Custom/ContentStore/Layouts/acmerockets/Images/bitcoin_only.png" alt="bitcoin only" /></a>
		<a href="/Bitcoin"><h3>Why use bitcoin?</h3></a>
		</div>
		<div class="unit size4of5">
		<!-- banner area -->
			<div class="line banner-line">
				<div class="unit size2of4 banner">
				<img src="Custom/ContentStore/Layouts/acmerockets/Images/default-banner.jpg" alt="chilean llamas acme rockets" />
				</div>
				<div class="unit size1of4">
					
					<div class="search border"><form action="/Search?Stats=Yes" method="post" name="form1" id="form1" >
							<h4>Search</h4>
							
							<input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="18" onfocus="this.value='';" onblur="if (this.value.length==0) this.value='search site';" />
						<input name="imageField" type="submit" value=">" class="gold-button" />
					</form>  </div>
				</div>
				<div class="unit size1of4">
					<div class="cart border"><? include("Custom/ContentStore/Layouts/acmerockets/cart.php") ?></div>
				</div>
			</div>
		<!-- banner area -->
		<div class="linemenu">
			<div class="unit menu border"><? include("Custom/ContentStore/Layouts/acmerockets/menu.php") ?></div>
		</div>
		<!-- content box -->
	<div class="line defaultcontentwrap border">
    
    <div class="shop-img-wrap">
	<?php
	if( array_key_exists( 'LYT_BANNER', $data['this']->assetLayoutSettings) && strlen( $data['this']->assetLayoutSettings['LYT_BANNER'] ))
		echo "<img src='{$data['this']->assetLayoutSettings['LYT_BANNER']}' />";
	?>
    </div>
	<div class="defaultcontent">
	<h2>{[TitleImage]}</h2>
	<div class="curvybox stockalert boxshadow ">If a box you wanted is out of stock, you can <a href="Shop_System/Service/AddToWishList"> request a Personal Stock Alert here</a>. You will be emailed when stock arrives.</div>
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
