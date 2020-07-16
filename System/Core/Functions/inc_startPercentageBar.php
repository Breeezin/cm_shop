<html>
<head>
<link rel="stylesheet" TYPE="text/css" HREF="System/Classes/MDI/sty_admin.css">
</head>
<body class="adminBackground">
	<TABLE WIDTH="100%" HEIGHT="100%">
		<TR><TD ALIGN="CENTER">
		<P><?=$caption?></P>
		<SPAN ID="percentageBarHolder" STYLE="border:1px solid black;width:202px;height:20px;text-align:left;background-color:white;">
			<SPAN ID="percentageBar" STYLE="background-color:#277FBC;width:1px;height:18px;">&nbsp;</SPAN>
		</SPAN>&nbsp;<SPAN ID="percentageBarValue" STYLE="width:20px">0</SPAN>%
		</TD></TR>
	</TABLE>
	
	<SCRIPT LANGUAGE="Javascript">
		var pb = document.getElementById('percentageBar');
		var pbv = document.getElementById('percentageBarValue');
		function sw(percentage) {
			pb.style.width = 200*percentage+'px';
			pbv.innerHTML = parseInt(percentage*100);
		}
	</SCRIPT>