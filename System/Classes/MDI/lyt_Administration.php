<?php header( "X-XSS-Protection: 0" ); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>{[SimpleTitle]}</title>
	<META NAME="keywords" 	     CONTENT="{[Keywords]}">
	<META NAME="description" 	 CONTENT="{[Description]}">
	<meta http-equiv="Content-Type" content="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>">
	<link rel="stylesheet" href="sty_Administration<?php print ss_optionExists('Advanced Administration')?'Upgraded':'';?>.css" type="text/css">
</head>
<script language="Javascript">
<!--
    var needsReload;

    function doOnFocus()
    {
        if( needsReload > 0 )
        {
            location.reload();
            needsReload = 0;
        }
    }
-->
</script>

<BODY  STYLE="border:0px; background-color:#f9f9f9;" onFocus="doOnFocus();">
<STRONG>{[Title]}</STRONG>

{[Content]}

</BODY>
</html>
