<html>
<head>
<title>{[WindowTitle]}</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=['cfg']['Web_Charset']?>">
<?php  $numb = rand(1,8);?>
<meta name="keywords" 	     content="{[Keywords]}">
<meta name="description" 	 content="{[Description]}">
<link rel="stylesheet" href="sty_sideimage<?=$numb?>.css" type="text/css">
<link href="sty_main.css" rel="stylesheet" type="text/css">
</head>

<body id="body">

<table width="100%">
  <tr>
    <td class="backmainleft" width="50%">&nbsp;</td>

    <td width="750" ><div><img src="Images/holder.gif" width="1" height="55"></div>
    <div class="toparea"><table cellspacing="0" cellpadding="0">
  <tr>
    <td width="143"><a name="top"></a><img src="Images/i-logo.gif" alt="Acme Express : Authentic Chilean Llamas" width="143" height="74" border="0"></td>
    <td width="607" style="padding-top:25px; ">{tmpl_embed_asset assetid="744"}</td>
  </tr>
</table>
</div>
	
	<div class="topbar" align="right">
	  <table width="100"  cellpadding="0" cellspacing="0" >
        <tr>
          <td class="borderleft"><img src="Images/i-search-header.gif" alt="Search" ></td>
          <td> <form name="form1" method="post" action="../Search?Stats=Yes" style="margin:0px; padding:0px; ">
                	<table  border="0" align="center" cellpadding="0" cellspacing="0">
                		<tr>

                			<td align="right"><input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="12" onFocus="this.value='';" onBlur="if (this.value.length==0) this.value='search site';">
           				 </td>
                			<td align="left" valign="top" style="padding-left:5px;padding-right:10px; padding-top:2px; "><input name="imageField" type="image" class="noBorder" src="Images/i-search.gif" alt="Go" border="0">
           				 </td>
           			 </tr>
       		  </table>
    		    	</form>  </td>
          <td class="borderleft"><a href="Shop_System/Service/Basket"><img src="Images/i-vieworder.gif" alt="View Order" width="80" height="28" border="0"></a></td>

          <td class="borderleft"><a href="Members"><img src="Images/i-memberslogin.gif" alt="Members Login" width="107" height="28" border="0"></a></td>
        </tr>
      </table>
	</div>
	
	<table width="100%" cellpadding="0" cellspacing="0" class="hometoptable" >
  <tr>
    <td valign="top" class="hometopcontent" >{tmpl_embed_asset assetid="752"}     
      </td>
    <td class="sideimage"><img src="Images/holder.gif" width="382" height="386"></td>
  </tr>
</table>
<div><img src="Images/holder.gif" width="4" height="4"></div>
<table cellpadding="0" cellspacing="0" width="750" >
  <tr>
    <td width="560px" class="homecigars" ><img src="Images/holder.gif" width="560" height="1"><div class="homecigarpadding">
	
	<?php
                    				$result = new Request('Asset.Display',array(
                    					'as_id'	=>	'514',
                    					'Service'	=>	'TopLevelCategoryList',
                    					'Template'	=>	'TopLevelCategoryListHome',
                    				));
                    				print $result->display;
                    			?>
								
								</div></td>
    <td width="4px" ><img src="Images/holder.gif" width="4" height="4"></td>
	<td valign="top" width="179" class="homelatestnews">{tmpl_embed_asset assetid="521"}</td>
  </tr>
</table>


	</td>
    <td class="backmainright" width="50%">&nbsp;</td>
  </tr>
</table>

<table width="100%" cellpadding="0"  cellspacing="0" class="homebackmiddlebar">
  <tr>
    <td>&nbsp;</td>
    <td width="750"><table width="750" align="center" cellpadding="0"  cellspacing="0" class="homebackmiddlebar">
      <tr>
        <td width="285" height="82"><a href="Shop_System"><img src="Images/home-greatsavings.gif" alt="Great Savings With Combos" width="285" height="81" border="0"></a></td>
        <td width="287"><a href="Acme%20Express/Members%20Login/Referral%20Program%20FAQs"><img src="Images/home-referal.gif" alt="Referal Program FAQ's" width="287" height="82" border="0"></a></td>
        <td width="172"><ul>
            <li ><strong><a href="#recommended" class="GoldText">Recommended Llamas</a></strong></li>
            <li ><strong><a href="#toprated" class="GoldText">Top Rated Llamas</a></strong></li>
            <li ><strong><a href="Shop_System/Service/Engine/Specials/1" class="GoldText">Specials</a></strong></li>
        </ul></td>
      </tr>
    </table></td>
    <td>&nbsp;</td>
  </tr>
</table>
<table width="750" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="444" valign="top"><div class="homemaincontent">
      <h1>{[TitleImage]}</h1><a name="recommended"></a><br>
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
        
          <br>
          <br>
          <span class="textSubHeaders">Recommended Reading:</span><br>
          <br> 
   <div align="center"> <iframe src="http://rcm.amazon.com/e/cm?t=acmerockets-20&o=1&p=8&l=as1&asins=1893273067&fc1=000000&IS2=1&lt1=_blank&lc1=0000ff&bc1=000000&bg1=ffffff&f=ifr" style="width:200px;height:240px;" scrolling="no" marginwidth="0" marginheight="0" frameborder="0"></iframe></div></div>
    <div><img src="Images/holder.gif" width="9" height="4"></div><div >
      <table width="100%" cellpadding="0" cellspacing="0" class="homebarbottom">
        <tr>
          <td width="96">{tmpl_embed_asset assetid="509"}</td>
		  <td width="100%"><a href="<?=$_REQUEST['REQUEST_URI']?>#top"><img src="Images/i-backtotop.jpg" alt="Back to the top" width="88" height="34" border="0"></a></td>
          <td  width="102" ><img src="Images/1-home-habanoslogo.jpg" alt="Habanos" width="102" height="34" align="right"></td>
        </tr>
      </table>
      </div></td>
    <td>&nbsp;</td>
	   <td width="301" valign="top" class="homemainsidebar">
      <div class="borderbottom"><img src="Images/i-home-shopinfo.gif" alt="Shopping Information"><span class="GoldText">{tmpl_embed_asset assetid="740"}</span><br>

      </div>
	     <div class="borderbottom"><img src="Images/i-home-specialoffers.gif" alt="" width="208" height="21"><a></a><span class="GoldText">{tmpl_embed_asset assetid="742"}</span></div>
		     <div class="topratedback"><a name="toprated"></a>
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
				<ul><tmpl_loop query="Q_TopRated">
	           				          <li><a href="Shop_System/Service/Detail/Product/{tmpl_row_var pr_id}" class="GoldText" style="text-decoration:none;">{tmpl_row_var pr_name}</a></li>
           				             </tmpl_loop>
				</ul>   		    	
	   		 		    	
	   		  
	    	 </tmpl_if></div>
<div class="testimonialsback"> {tmpl_embed_asset assetid="674"}</div>


    </td>
  </tr>
</table>
<span class="footerText"><br>
</span>
<p align="center" class="footerText" ><strong>Our online shopping systems supports the following web browsers: <a href="http://www.microsoft.com/windows/ie/downloads/critical/ie6sp1/default.mspx" target="_blank" class="GoldText">Internet Explorer 6</a> and <a href="http://www.mozilla.org/" target="_blank" class="GoldText">Mozilla Firefox 1.0</a></strong></p>
<p align="center" class="footerText">{tmpl_embed_asset assetid="531"}</p>
</body>
</html>
