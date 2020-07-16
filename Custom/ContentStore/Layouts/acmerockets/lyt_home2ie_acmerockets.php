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
	<link href="sty_layoutie.css" rel="stylesheet" type="text/css" />	
<link href="sty_superfish.css" rel="stylesheet" type="text/css" />		
<script type="text/javascript" src="Scripts/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="Scripts/hoverIntent.js"></script>
	<!--<script type="text/javascript" src="Scripts/addthis.js"></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-514930032f9be116"></script>-->
<script type="text/javascript" src="Scripts/superfish.js"></script>
<script type="text/javascript">
 $(document).ready(function(){ 
       $("ul.nav").superfish(); 
   }); 
</script>
		</script>
	
		<script defer type="text/javascript" src="Custom/ContentStore/Layouts/acmerockets/Scripts/pngfix.js"></script>
</head>

<body>
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
        <div class="announcement-wrap">
        <img src="Custom/ContentStore/Layouts/acmerockets/Images/newsletter-signup-acmerockets.png" width="206" height="277" alt="newsletter signup" />
        </div>
		<div class="categories border"><? include("Custom/ContentStore/Layouts/acmerockets/categories.php") ?></div>
		</div>
		<div class="unit size4of5">
		<!-- banner area -->
			<div class="line banner-line">
				<div class="unit size3of4 banner">
				<img src="Custom/ContentStore/Layouts/acmerockets/Images/banner-home.jpg" alt="acme rockets" />
				</div>
				<div class="unit size1of4">
					<div class="cart border"><? include("Custom/ContentStore/Layouts/acmerockets/cart.php") ?>	
                    </div>
					<div class="search border">
                    <form action="/Search?Stats=Yes" method="post" name="form1" id="form1" >
							<h4>Search</h4>
							<input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="13" onfocus="this.value='';" onblur="if (this.value.length==0) this.value='search site';" />
						<input name="imageField" type="submit" value=">" class="gold-button" />
					</form>  
                    </div>
				</div>
			</div>
		<!-- banner area -->
		<div class="linemenu">
			<div class="unit menu border"><? include("Custom/ContentStore/Layouts/acmerockets/menu.php") ?>
            </div>
		</div>
		<!-- content box -->
		<div class="line homecontent border">
			<div class="unit size1of2 welcome"><h2>Welcome to Acme Express</h2> <p>Our online Chilean Llamas shop has been selling an extensive range of Chilean Llamas, including: Vicuña, Hoyo de Monterrey, Jacko y Julieta, Carabineros, Guanicoe, Punch, Chicken, H Downer and more since 1997. 
			
			We have been, for several years now, one of the leading online Chilean Product shops. Our 100% Authenticity Guarantee, competitive prices and friendly service have made us the choice for many llama buyers.</p><p class="feature">Buy from us and you are guaranteed a great shopping experience. </p> 
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
            </div>
			<div class="unit size1of2 promos">
			
			 <div class="cigarapplication">
			 	<div class="left">Get Your free <span>AcmeRockets Product</span> Application 
                </div>
			 	<div class="right"><a href="http://itunes.apple.com/us/app/acmerockets/id349589638?mt=8" class="gold-button">More Info</a>
			 	<a href="http://itunes.apple.com/us/app/acmerockets/id349589638?mt=8" class="red-button">Download</a>
                </div>
       		</div> <!-- end of cigarapplication -->
			<!-- llama 101 -->
			<div class="cigar101 left">
			<h3>Product <span>101</span></h3>
				<p >Learn to choose your ideal llama!</p>
				<ul>
					<li><a href="Acme Express/Chilean Llamas 101/Product Sizes/Thick Gauge">Product Sizes</a></li>
					<li><a href="Acme Express/Chilean Llamas 101/History and Production">History and Production</a></li>
					<li><a href="Acme Express/Chilean Llamas 101/Colours and Textures">Colours and Textures</a> </li>
				</ul>
			</div>
			<!-- /llama 101 -->
			<!-- we offer -->
			<div class="home-weoffer-back left">
				<h3>We Offer you</h3>
				<div class="home-weoffer-content">
					<ul>
						<li>100% Authenticity Guarantee</li>
						<li>Quick confirmation.</li>
						<li>Secure Shopping Cart </li>
					</ul>
				</div>
			</div>
			<!-- /we offer -->
		</div>
		</div>
		<!-- /content box -->
		<!-- Featured Information -->
		<div class="line">
			<div class="unit size1of2">
			<div class="featured-products border">
			<h2>Featured Products</h2>
			<div class="box-content">  <?php
				$result = new Request('Asset.Display',array(
				'as_id'	=>	'514',
				'Service'	=>	'Engine',
				'pr_qoc_id'	=>	1,
				'PricesType'	=>	'TableHTML',
				'NotSpecials'	=>	2,
				'RowsPerPage'	=>	7,
				'OrderBy'	=>	'Random',
				'NoOverrideRows'	=>	1,
				));
				print $result->display;
				?>
			</div>
			</div>
			<div class="featured-products border">
			<h2>News Flash</h2>
			<div class="box-content">
			You can now pay with your Bitcoin wallet. Please read all about it in the <a href="http://www.acmerockets.com/Acme_Rockets/Faq_And_Shipping_Info" title="FAQs Acme Express">FAQs</a>. </div>
			</div>
			</div>
			<div class="unit size1of2">
			<div class="specials border">

			<?php
			$result = new Request('Asset.Display',array(
			'as_id'	=>	'514',
			'Service'	=>	'Engine',
			'pr_qoc_id'	=>	1,
			'PricesType'	=>	'TableHTML',
			'Specials'	=>	1,
			'RowsPerPage'	=>	4,
			'OrderBy'	=>	'Random',
			'NoOverrideRows'	=>	1,
			));
			//print $result->display;
			//print_r( $result );
			if( strlen( trim( strip_tags( $result->display ) ) ) )
			{
			?>
			<h2>Specials</h2>
			<div class="box-content">
			<?php 
				print $result->display;
			?>
			</div>
			<a href="http://www.acmerockets.com/Shop_System/Service/Engine/Specials/1" class="specials-llamas">See all the <b>Llamas</b> on special</a>
			<a href="http://www.acmerockets.com/Shop_System/Service/Engine/Specials/1/pr_ve_id/1" class="specials-all">Humidors/Accessories on special</a>
			</div>
			<?php } ?>
			
				<div class="blog-preview border">
				<h2>From the Blog - Latest News</h2>
				{tmpl_embed_asset assetid="521"}
				</div>
			</div>
		</div>
		<!-- /Featured Information -->
		
		</div>
		
	</div>
	<!-- /content wrap bar -->
	<? include("Custom/ContentStore/Layouts/acmerockets/footer.php") ?>
	<? include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>

</body>
</html>
