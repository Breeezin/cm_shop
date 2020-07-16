<?php

if( !array_key_exists( 'ForceCountry', $_SESSION )
 || !is_array( $_SESSION['ForceCountry'] ) )
	$_SESSION['ForceCountry'] = getRow( "select * from countries where cn_two_code = '".ss_getCountry(NULL, 'cn_two_code')."'");

$here = null;
$lang = 'en';
if( substr( $_SERVER['REQUEST_URI'], 0, 3 ) == '/fr' )
	$lang = 'fr';
	
switch( $lang ) {
case 'fr':
	$pref_message = "Vous expédiez à ";
	$suf_message = "Si ce n'est pas votre pays, il changer ici!";
	break;
default:
	$pref_message = "You are shipping to ";
	$suf_message = "If this is not your country change it here!";
}

?>
<script language="javascript" type="text/javascript">
function changeCountry() 
{
    var agt=navigator.userAgent.toLowerCase();
	var is_ie     = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
	var is_gecko = ((agt.indexOf('gecko') != -1) && (agt.indexOf('like gecko') == -1));
	var is_opera = (agt.indexOf("opera") != -1);

	newwindow2=window.open('','name','height=160,width=380');
	var tmp = newwindow2.document;
	tmp.write('<html><head><title>Shipping Destination?</title>');
	tmp.write('<script language="javascript" type="text/javascript">');
//	tmp.write('function newCountry( val ) { alert( window.opener.parent.location.href ); self.close(); }');
	if( is_ie || is_gecko || is_opera )
	{
		tmp.write('function newCountry( val ) { var foo = window.opener.parent.location;\n');
		tmp.write('var xmlhttp=false;\n');
		tmp.write(' try {\n');
		tmp.write('  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");\n');
		tmp.write(' } catch (e) {\n');
		tmp.write('  try {\n');
		tmp.write('   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");\n');
		tmp.write('  } catch (E) {\n');
		tmp.write('   xmlhttp = false;\n');
		tmp.write('  }\n');
		tmp.write(' }\n');
		tmp.write('if (!xmlhttp && typeof XMLHttpRequest!="undefined") {\n');
		tmp.write('	try {\n');
		tmp.write('		xmlhttp = new XMLHttpRequest();\n');
		tmp.write('	} catch (e) {\n');
		tmp.write('		xmlhttp=false;\n');
		tmp.write('	}\n');
		tmp.write('}\n');
		tmp.write('if (!xmlhttp && window.createRequest) {\n');
		tmp.write('	try {\n');
		tmp.write('		xmlhttp = window.createRequest();\n');
		tmp.write('	} catch (e) {\n');
		tmp.write('		xmlhttp=false;\n');
		tmp.write('	}\n');
		tmp.write('}\n');
		tmp.write('if( xmlhttp ) {\n');
		tmp.write('xmlhttp.open("GET", "index.php?act=Security.SetCountry&CC="+val+"&BackURL="+window.opener.parent.location.href,true);\n');
		tmp.write('xmlhttp.onreadystatechange=function() {\n');
		tmp.write('if (xmlhttp.readyState==4) {\n');
		tmp.write('foo.reload( true );\n');
		tmp.write('}}; \n');
		tmp.write('xmlhttp.send(null);\n');
		tmp.write('foo.reload( true );\n');
		tmp.write('window.setTimeout("self.close()", 4000);\n');
		tmp.write('}else{\n');
		tmp.write('window.opener.parent.location.href="index.php?act=Security.SetCountry&CC="+val+"&BackURL="+window.opener.parent.location.href ; self.close();\n');
		tmp.write('}\n');
		tmp.write('}\n');
	}
	else
	{
		tmp.write('function newCountry( val ) {\n');
		tmp.write('window.opener.parent.location.href="index.php?act=Security.SetCountry&CC="+val+"&BackURL="+window.opener.parent.location.href ; self.close();\n');
		tmp.write('}\n');
	}
//	tmp.write('function newCountry( val ) { window.opener.parent.location.href="index.php?act=Security.SetCountry&CC="+val+"&BackURL="+window.opener.parent.location.href ; self.close(); }');
//	tmp.write('function newCountry( val ) { window.opener.parent.location.href="index.php?act=Security.SetCountry&CC="+val+"&BackURL=Shop_System"; self.close(); }');
	tmp.write('</scr');
	tmp.write('ipt>');
	tmp.write('<link href="Custom/ContentStore/Layouts/acmerockets/sty_shop.css" rel="stylesheet" type="text/css" />');
	tmp.write('</head><body><p>Select new destination country.</p>');
	tmp.write('Note: this may clear your shopping basket if you are shipping to the EU.<br/><br/>');
//	tmp.write('<p><a href="javascript:alert(self.location.href)">view location</a>.</p>');
	tmp.write('<select name="CurrencyThreeCode" onchange="newCountry(this.value);">');
	tmp.write('<?php
	$Q_Countries = query("
		SELECT cn_id, cn_name, cn_two_code FROM countries
		WHERE (cn_disabled IS NULL OR cn_disabled = 0)
		 AND (cn_restrict_shipping IS NULL OR cn_restrict_shipping = 0 OR cn_redirect_url IS NOT NULL)
		ORDER BY cn_name
	");	

	while( $row = $Q_Countries->fetchRow())
	{
		if( $row['cn_id'] == $_SESSION['ForceCountry']['cn_id'] )
		{
			echo "<option value=\"".$row['cn_two_code']."\" selected=\"selected\">".$row['cn_name']."</option>";
			$here = $row['cn_name'];
		}
		else
			echo "<option value=\"".$row['cn_two_code']."\" >".$row['cn_name']."</option>";
	}
	?>');
	tmp.write('</select>');
	tmp.write('<p><a href="javascript:self.close()">Cancel</a></p>');
	tmp.write('</body></html>');
	tmp.close();
}
</script>
<div class="unit size4of5 top">
<?php
	if( $here == null )
	{
		switch( $lang ) {
		case 'fr':
			$pref_message = "Désolé, nous ne livrons pas à ";
			break;
		default:
			$pref_message = "Sorry, we can't ship to ";
		}
//		$suf_message = "If this is not your country change it here!";
	}
	echo "<div class='countrychoose'>";
	echo "<input name=\"Country\" type=\"image\" class=\"noBorder\"  src=\"index.php?act=ImageManager.get&Flag="
			.strtolower($_SESSION['ForceCountry']['cn_two_code'])."\" alt=\"Change Country\" onclick=\"changeCountry();\" />";
	echo "<a href=\"javascript:changeCountry();\" class=\"eu-orders light-link\">$pref_message $here. $suf_message</a>";
	echo "</div>";

	if( substr( $_SERVER['REQUEST_URI'], 0, 3 ) != '/fr' )
	{		// english
		$search = array( "/Acme%20Express/" );
		$replace = array(  "Cigare%20Express" );

		$frlink = "/fr".preg_replace($search, $replace, $_SERVER['REQUEST_URI'] );
		$enlink = $_SERVER['REQUEST_URI'];
	}
	else
	{		// french
		$search = array( "/Cigare%20Express/" );
		$replace = array(  "Acme%20Express" );

		$frlink =  $_SERVER['REQUEST_URI'];
		$enlink = preg_replace($search, $replace, substr( $_SERVER['REQUEST_URI'], 3 ));
	}

/*
<div class="language-shell">
<h4>Site Language:</h4> <div class="chooselanguage"><a href="<?php echo $enlink;?>" class="textNews light-link"><img width=16 border="0" src="/Custom/ContentStore/Layouts/acmerockets/Images/gb.png" alt="british flag" /></a>
<a href="<?php echo $frlink;?>" class="textNews light-link"><img width=16 border="0" src="/Custom/ContentStore/Layouts/acmerockets/Images/fr.png" alt="french flag" /></a></div>
</div>
*/
?>
<div class="language-shell">
You must be at least <strong>18 years old.</strong> By purchasing you confirm this.
</div>
<div>
<img  src="/images/GBR_black_ribbon.png" alt="usa flag" />
</div>
</div>
