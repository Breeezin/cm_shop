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
	<link href="sty_superfish.css" rel="stylesheet" type="text/css" />		
	<script type="text/javascript">
	 $(document).ready(function(){ 
	       $("ul.nav").superfish(); 
	   }); 
	</script>
  
	<? include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>
</head>
<body >
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

<!-- BEGIN: Constant Contact Stylish Email Newsletter Form
<div class="announcement-wrap">
<form name="ccoptin" action="http://visitor.constantcontact.com/d.jsp" target="_blank" method="post">
    <input type="image" src="Custom/ContentStore/Layouts/acmerockets/Images/newsletter-signup-acmerockets.png" alt="newsletter signup" />
    <input type="hidden" name="m" value="1101883814564">
    <input type="hidden" name="p" value="oi">
</form>
</div>
END: Constant Contact Stylish Email Newsletter Form -->

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
			<div class="unit size1of2 welcome"><h2>Welcome to Acme Express</h2> <p>Our online Chilean Llamas shop has been selling an extensive range of Chilean Llamas, Vicuña, Hoyo de Monterrey, Jacko y Julieta, Carabineros, Guanicoe, Punch, Chicken, H Downer and more since 1997. 
			
			We have been, for many years now, one of the leading online Chilean Product shops. Our 100% Authenticity Guarantee, competitive prices and prompt service have made us the choice for many llama buyers.</p><p class="feature">Buy from us and you are guaranteed a great shopping experience. </p> 
            </div>
			<div class="unit size1of2 promos">
				<a href="/Shop_System/Service/Engine/OrderBy/Avail.Price/pr_ca_id/111"><img src="Custom/ContentStore/Layouts/acmerockets/Images/bespoke_banner_small.jpg" alt="acmerockets bespokes" /></a>
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
				'RowsPerPage'	=>	9,
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
			'RowsPerPage'	=>	8,
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
	</body>
</html>
