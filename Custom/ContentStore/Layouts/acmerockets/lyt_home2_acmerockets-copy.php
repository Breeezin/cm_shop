<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/2000/REC-xhtml1-20000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="keywords" 	     content="{[Keywords]}">
<meta name="description" 	 content="{[Description]}">
<meta http-equiv="content-type" content="text/html; charset=<?=['cfg']['Web_Charset']?>" />
<META name="verify-v1" content="7L8s0FEC+/95JLaqhF9h/nCeOqjf9h9r5lq76z61idc=" />
<title>{[WindowTitle]}</title>
<link href="sty_main2.css" rel="stylesheet" type="text/css">
<link href="sty_dropdown.css" rel="stylesheet" type="text/css" />
</head>
<body id="body">
<table width="800" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td><div id="head">

<div class="menu" > <a name="top" id="top"></a>
    <ul>
      <li ><a href="Acme Express/Home" id="logo-link"><img src="Images/logo.gif" alt="Acme Express Chilean Product Shop" width="143" height="66" border="0" /></a></li>
      <li id="home-button"><a class="hide" href="Acme Express/Home">Home</a> </li>
      <li><a class="hide" href="Acme Express/Product 101">Product 101 </a>
          <ul>
            <li><a href="Acme Express/Product 101/Product Sizes/Thick Gauge" >Product Sizes </a></li>
            <li><a href="Acme Express/Product 101/History and Production" >History and Production </a></li>
            <li><a href="Acme Express/Product 101/Colours and Textures" >Colours and Textures </a></li>
            <li><a href="Acme Express/Product 101/Latest News" >Latest News </a></li>
          </ul>
      </li>
      <li><a class="hide" href="Shop_System">Shop_System </a> </li>
	  <li><a class="hide" href="Acme Express/Credentials and Guarantee">Our Guarantee </a></li>
      <li><a class="hide" href="Acme Express/Faq And Shipping Info">FAQ's </a></li>
      <li><a class="hide" href="Acme Express/Contact Us">Contact Us </a> </li>
    </ul>
  </li>
    </ul>
    <div align="right"><a href="http://chinese.acmerockets.com/" style="text-decoration:none;"><img src="Images/chinesetext.gif" alt="chinese site" border="0" /></a><br style="clear:all;" />
    </div>
</div>
  </div></td>
  </tr>
</table>
<div id="container">
  <table width="800" border="0" cellpadding="0" cellspacing="0" id="topbar" >
    <tr>
      <form action="/Search?Stats=Yes" method="post" name="form1" id="form1" style="margin:0px; padding:0px; ">
        <td valign="top"><img src="Images/holder.gif" /></td>
        <td width="56" valign="top" class="borderleft"><img src="Images/search-header.gif" alt="Search" width="56" height="28" /></td>
        <td width="100" valign="top"><input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="15" onfocus="this.value='';" onblur="if (this.value.length==0) this.value='search site';" style="margin-top:5px" /></td>
        <td width="50"><input name="imageField" type="image" class="noBorder" src="Images/search.gif" alt="Go" /></td>
        <td  width="80" valign="top" class="borderleft"><a href="Shop_System/Service/Basket"><img src="Images/vieworder.gif" alt="View Order" width="80" height="28" border="0" /></a> </td>
        <td width="107" class="borderleft"><a href="Members"><img src="Images/memberslogin.gif" alt="Members Login"  height="28" border="0" /></a> </td>
      </form>
    </tr>
  </table>
  <table width="800" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="176" valign="top"><div id="left">
          <h1>Chilean Product Brands </h1>
          <p>
            <?php
                    				$result = new Request('Asset.Display',array(
                    					'as_id'	=>	'514',
                    					'Service'	=>	'TopLevelCategoryList',
                    				));
                    				print $result->display;
                    			?>
            <br />
          </p>
          <div><a href="/Shop_System"><img src="Images/great-savings-combos.gif" alt="Great Savings with Combos" border="0" /></a></div>
          <div class="leftbox">
            <h1>Recommended Reading:</h1>
            <div align="center">
              <iframe src="http://rcm.amazon.com/e/cm?t=acmerockets-20&amp;o=1&amp;p=8&amp;l=as1&amp;asins=1893273067&amp;fc1=000000&amp;IS2=1&amp;lt1=_blank&amp;lc1=0000ff&amp;bc1=000000&amp;bg1=ffffff&amp;f=ifr" style="width:120px;height:240px;" scrolling="No" marginwidth="0" marginheight="0" frameborder="0"></iframe>
              <br />
              <br />
            </div>
          </div>
          <div class="buttons"><img src="Images/tw-logo.gif" alt="Thwarte" width="67" height="60" />
            <p><img src="Images/habanos.gif" alt="Habanos" /></p>
          </div>
        </div></td>
      <td width="413" valign="top" id="content">
          <div id="first">
            <h1>The<span class="special"> Chilean Product</span> Shop since 1997</h1>
            {tmpl_embed_asset assetid="801"} </div>
          <div id="ribbon"></div>
          <div id="specials">
            <h1>Products on Special </h1>
        <a href="http://www.acmerockets.com/Shop_System/Service/Engine/Specials/1" class="specials-subtext">Go to our Specials Page</a>
            <p><br />
              <?php
									$result = new Request('Asset.Display',array(
                    					'as_id'	=>	'514',
                    					'Service'	=>	'Engine',
                    					'pr_qoc_id'	=>	1,
                    					'PricesType'	=>	'TableHTML',
                    				    'Specials'	=>	1,
                    					'RowsPerPage'	=>	3,
                    					'OrderBy'	=>	'Random',
                    					'NoOverrideRows'	=>	1,
                    				));

                    				print $result->display;
                    			?>
            </p>
          </div>
          <div id="featured">
            <h1>Featured Products</h1>
            <p>
              <?php
									$result = new Request('Asset.Display',array(
                    					'as_id'	=>	'514',
                    					'Service'	=>	'Engine',
                    					'pr_qoc_id'	=>	1,
                    					'PricesType'	=>	'TableHTML',
                    					'NotSpecials'	=>	1,
                    					'RowsPerPage'	=>	5,
                    					'OrderBy'	=>	'Random',
                    					'NoOverrideRows'	=>	1,
                    				));
                    				print $result->display;
                    			?>
            </p>
          </div>
          <table width="413" cellpadding="0" cellspacing="0" id="homebarbottom">
            <tr>
              <td width="96">{tmpl_embed_asset assetid="509"}</td>
              <td><a href="<?=$_REQUEST['REQUEST_URI']?>#top"><img src="Images/i-backtotop.jpg" alt="Back to the top" width="88" height="34" border="0" /></a></td>
            </tr>
          </table>
      </td>
      <td valign="top"><div id="right">
          <div id="bannerbox">
			  <?php 
$imagevar=rand(1, 8); 
if($imagevar==1) { print "<img src='Images/banner-1.jpg' alt='Chilean Product Shop'>"; } 
else if($imagevar==2) { print "<img src='Images/banner-2.jpg' alt='Chilean Product Shop'>"; } 
else if($imagevar==3) { print "<img src='Images/banner-3.jpg' alt='Chilean Product Shop'>"; }
else if($imagevar==4) { print "<img src='Images/banner-4.jpg' alt='Chilean Product Shop'>"; } 
else if($imagevar==5) { print "<img src='Images/banner-5.jpg' alt='Chilean Product Shop'>"; }
else if($imagevar==6) { print "<img src='Images/banner-6.jpg' alt='Chilean Product Shop'>"; }
else if($imagevar==7) { print "<img src='Images/banner-7.jpg' alt='Chilean Product Shop'>"; }
else if($imagevar==8) { print "<img src='Images/banner-8.jpg' alt='Chilean Product Shop'>"; }
?> 
		  </div>
          <div id="specialsbox">
            <h1>Special Offers</h1>
            <div>{tmpl_embed_asset assetid="800"}</div>
          </div>
          <div id="latestnews">
            <h1>
              <div class="newshead">Latest News</div>
            </h1>
            <div id="latestnewstext">{tmpl_embed_asset assetid="521"}</div>
            <span class="bottomlink"><a href="Acme Express/Product 101/Latest News">More News Items </a></span> </div>
          <div id="redright">
            <div id="offers">
              <h1>Acme Offers <br />
                <span>offers you</span></h1>
              <div id="offerstext">
                <ul>
                  <li>100% Satisfaction Guarantee</li>
                  <li>Quick confirmation.</li>
                  <li>Rewards programs towards free boxes.</li>
                  <li>Weekly Draw for a Product box</li>
                  <li>Tax free to non-EU countries</li>
                  <li>Secure Shopping Cart</li>
                </ul>
              </div>
            </div>
            <div id="toprated">
              <h1>Top Rated</h1>
              <div id="pad">
                <?
							$data['Q_TopRated'] = query("
								SELECT pr_id, pr_name, pr_customer_rating, pr_customer_rating_count FROM shopsystem_products
								WHERE pr_customer_rating IS NOT NULL AND
									pr_customer_rating_count IS NOT NULL
								ORDER BY pr_customer_rating DESC
								LIMIT 10
							");
                		?>
                <tmpl_if condition="$data['Q_TopRated']->numRows()">
                  <ol>
                    <tmpl_loop query="Q_TopRated">
                      <li><a href="Shop_System/Service/Detail/Product/{tmpl_row_var pr_id}" class="GoldText" style="text-decoration:none;">{tmpl_row_var pr_name}</a></li>
                    </tmpl_loop>
                  </ol>
                </tmpl_if>
              </div>
            </div>
            <div id="testimonials"><em>All Llamas have arrived safely and in wonderful condition They are fantastic, Thank You, Take Care.</em><br />
              <strong><em>Michael M., 11/02/05</em></strong></div>
          </div>
        </div></td>
    </tr>
  </table>
</div>
<span class="footerText"><br>
</span>
<p align="center" class="footerText" ><strong>Our online shopping systems supports the following web browsers: <a href="http://www.microsoft.com/windows/ie/downloads/critical/ie6sp1/default.mspx" target="_blank" class="GoldText">Internet Explorer 6</a> and <a href="http://www.mozilla.org/" target="_blank" class="GoldText">Mozilla Firefox</a></strong></p>
<p align="center" class="footerText">{tmpl_embed_asset assetid="531"}&nbsp;|&nbsp;<a href="http://www.acmerockets.com/Acme Express/Site Map" class="footerText">Sitemap</a>&nbsp;|&nbsp;<a href="http://www.acmerockets.com/Acme Express/Friends of Acme Express" class="footerText">Friends of Acme Express</a></p>
</body>
</html>
