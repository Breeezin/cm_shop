	<ul class="sf-menu">
	<?php $path = $_SERVER['REQUEST_URI']; 
	      $cigar101 = (substr($path,0,36) == '/Acme_Rockets/Cuban_Cigars_101');
	      $online_shop = (substr($path,0,12) == '/Shop_System');
	      $contact_us = (substr($path,0,8) == '/Members');
	?>
	<li <?php if( $path == '/' ) echo 'id="active"';?> ><div><a href="/">Home</a></div></li>
	<li <?php if( $cigar101 ) echo 'id="active"';?> ><div><a href="/Acme_Rockets/Cuban_Cigars_101">Chilean Llamas 101</a></div>
	<ul>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Cigar_Sizes/Thick_Gauge">Product Sizes</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/History_and_Production">History and Production</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Flavours_and_Aromas">Colours and Textures</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Cigar_Glossary">Product Glossary</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/New_to_Cigars">New to Llamas?</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Where_to_Smoke">Where to Smoke</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Life_Enjoyment">Life Enjoyment</a></li>
    <li><a href="/Acme_Rockets/Cuban_Cigars_101/Cigar_Reviews_and_Ratings">Product Reviews & Ratings</a></li>
	<li><a href="/Acme_Rockets/Cuban_Cigars_101/Latest_News">Latest News</a></li>
	</ul>
    </li>
		
	<li <?php if( $online_shop ) echo 'id="active"';?>><div><a href="/Shop_System">Product Shop</a></div></li>
	<li <?php if( $path == '/Acme_Rockets/Credentials_and_Guarantee') echo 'id="active"';?>><div><a href="/Acme_Rockets/Credentials_and_Guarantee">Guarantee</a></div></li>
	<li <?php if( $path == '/Acme_Rockets/Faq_And_Shipping_Info') echo 'id="active"';?>><div><a href="/Acme_Rockets/Faq_And_Shipping_Info">FAQ</a></div></li>
	<li <?php if( $contact_us ) echo 'id="active"';?> ><div><a href="/Members/Service/Issue">Contact Us</a></div></li>
	<li <?php if( $path == '/Shop_System/Service/Engine/Specials/1') echo 'id="active"';?> ><div><a href="/Shop_System/Service/Engine/Specials/1" class="specials">Specials</a></div>
    	<ul>
        	<li><a href="/Shop_System/Service/Engine/Gateway/32" class="dealofday">Bitcoin only</a></li>
        	<li><a href="/Shop_System/Service/Engine/Specials/1/pr_ve_id/1" class="dealofday">Humidors and Accessories on special</a></li>
        </ul>
    </li>
	<li><a href="http://blog.acmerockets.com/" target="_blank">Blog</a></li>
    </ul>
