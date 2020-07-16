<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{[WindowTitle]}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="sty_main.css" rel="stylesheet" type="text/css" />
</head>

<body id="body">

<div id="container">
  <div id="header">
	  <ul id="primary">
		<li><a href="Chinese Site/Home">主页</a></li>
		<li><a href="Shop_System"  class="current"  >雪茄店</a></li>
	</ul>
      <a href="Home"><img src="Images/acmerockets-logo.gif" alt="Acme Express : Chilean Product Shop" width="147" height="78" border="0" /></a></div>
	<div id="main">
	  <div id="contents" align="left">
	  <div style="clear:both; height:2px;"></div>
	  <div id="topbar">
	    <ul id="topbar-ul">
	      <li><a href="#" id="vieworder" >您认为,r</a> </li>
          <li><span>网站搜索&nbsp;&nbsp;<input name="AST_SEARCH_KEYWORDS" type="text" class="form-Special" value="keyword" size="12" onFocus="this.value='';" onBlur="if (this.value.length==0) this.value='search site';">
		  &nbsp;&nbsp;<input name="imageField" type="image" class="noBorder" src="Images/search-go.gif" alt="Go">
	    </ul>
	    </div>
	  <div id="inside-banner"></div>
	    <div >
<table width="790" align="center" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top" id="sidebrands" >
      <h2 class="onblack"> 雪茄品牌 </h2> 
      <p><?php
                    				$result = new Request('Asset.Display',array(
                    					'as_id'	=>	'514',
                    					'Service'	=>	'TopLevelCategoryList',
                    				));
                    				print $result->display;
                    			?></p>
    <br>
       </td>
								<td valign="top" id="contenttop"><div id="contentrest"> <h1>{[TitleImage]}</h1>
       <table width="555" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>{[Content]} </td>
  </tr>
</table>
</div></td>
   
  </tr>
</table>

</div>

<div style="clear:both; height:5px;"></div>

      </div>
	</div>
	
	
<div id="footer"><p>我国网上购物系统支持以下网络浏览器:  <a href="http://www.microsoft.com/windows/ie/downloads/critical/ie6sp1/default.mspx" target="_blank" class="GoldText">Internet Explorer 6</a> and <a href="http://www.mozilla.org/" target="_blank" class="GoldText">Mozilla Firefox 1.0</a></p><p><span class="footerText">{tmpl_embed_asset assetid=&quot;531&quot;}&nbsp;|&nbsp;<a href="http://www.acmerockets.com/Links" class="footerText">Links</a></span><span class="footerText">&nbsp;|&nbsp;</span>&copy; {[CurrentYear]} AcmeRockets.com</p>
</div>
	
</div>
</body>
</html>
