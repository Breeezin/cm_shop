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
	function showProd(what)
		{  
		showme=document.getElementById('more'+what);
		showme.style.display='none';
		showme=document.getElementById('less'+what);
		showme.style.display='block';
		}
	function hideProd(what)
		{  
		showme=document.getElementById('more'+what);
		showme.style.display='block';
		showme=document.getElementById('less'+what);
		showme.style.display='none';
		}
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
	
		<div class="unit size1of5"><a href="/" id="logo-link"><img src="Custom/ContentStore/Layouts/acmerockets/Images/logo.png" alt="Acme Express Chilean Product Shop" width="260" height="190" border="0" /></a> 
	<div class="announcement-wrap">
<form name="ccoptin" action="http://visitor.constantcontact.com/d.jsp" target="_blank" method="post">
    <input type="image" src="Custom/ContentStore/Layouts/acmerockets/Images/newsletter-signup-acmerockets.png" alt="newsletter signup" />
    <input type="hidden" name="m" value="1101883814564">
    <input type="hidden" name="p" value="oi">
</form>
</div>
	<div class="categories border"><? include("Custom/ContentStore/Layouts/acmerockets/categories.php") ?></div>
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
		<div class="defaultcontent">
	  	<table width="100%">
			<tr>
		<td align='left'><h1><a href='/Members'>{[TitleImage]}<a/></h1></td><td align='right'><h3><a href='/Members/Service/Issue'>Open a new issue (Contact Support Team).</a></h3></td>
			</tr>
		</table>
	    <table width="100%"> <tr> <td style="border-bottom-style:solid; border-bottom-width:1px;">&nbsp;  </td> </tr> </table>
		{[Content]}
		<br />
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
