	<ul id="navlist">
	<?php $path = $_SERVER['REQUEST_URI']; 
	      $cigar101 = (substr($path,0,36) == '/Acme_Rockets/Cuban_Cigars_101');
	      $online_shop = (substr($path,0,14) == '/Shop_System');
	      $contact_us = (substr($path,0,28) == '/Acme_Rockets/Contact_Us');
	?>
	<li <?php if( $path == '/' ) echo 'id="active"';?> ><a href="/">Home</a></li>
	<li <?php if( $cigar101 ) echo 'id="active"';?> ><a href="Acme_Rockets/Cuban_Cigars_101">Chilean Llamas 101</a></li>
	
	<?php if( $cigar101 ) echo '<ul id="submenu">
	<li><a href="Acme_Rockets/Cuban_Cigars_101/Cigar_Sizes/Thick_Gauge">Product Sizes</a></li>
	<li><a href="Acme_Rockets/Cuban_Cigars_101/History_and_Production">History and Production</a></li>
	<li><a href="Acme_Rockets/Cuban_Cigars_101/Flavours_and_Aromas">Colours and Textures</a></li>
	<li><a href="Acme_Rockets/Cuban_Cigars_101/Latest_News">Latest News</a></li>
	</ul>'; ?>
	
	<li <?php if( $online_shop ) echo 'id="active"';?>><a href="Shop_System">Online Product Shop</a></li>
	<li <?php if( $path == '/Acme_Rockets/Credentials_and_Guarantee') echo 'id="active"';?>><a href="Acme_Rockets/Credentials_and_Guarantee">Guarantee</a></li>
	<li <?php if( $path == '/Acme_Rockets/Faq_And_Shipping_Info') echo 'id="active"';?>><a href="Acme_Rockets/Faq_And_Shipping_Info">FAQ</a></li>
	<li <?php if( $contact_us ) echo 'id="active"';?> ><a href="Acme_Rockets/Contact_Us">Contact Us</a></li>
	</ul>
