<SCRIPT LANGUAGE="Javascript">
	alert('Complete!');
	<? 
		if ($location == 'nothing') {
			// do nothing
		} else if ($location !== null) { 
			print ("document.location = '".ss_JSStringFormat($location)."';");
		} else {
			print ("window.close();");	
		}
	?>
</SCRIPT>
</body>
</html>