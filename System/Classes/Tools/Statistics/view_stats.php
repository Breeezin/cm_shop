<?php 
	$tableShow = '';
	foreach ($displayStats as $key => $display) {				
		if (count($display['assets'])) {				
			$this->param($key.'assets', $display['assets']);				
		}
	}
	
	if (!array_key_exists('Service',$this->ATTRIBUTES)) {
		$tableShow = "none";	
?>
	<FORM name="StatsForm" method="POST" action="index.php?act=statistics.Display">
	<INPUT type="hidden" name="Opened" value="<?=$this->ATTRIBUTES['Opened']?>">
	<p><BR>
<?php 
		$divIDs = '';
		$hiddenParams = '';
		
		foreach ($displayStats as $key => $display) {	
			$divIDs = ListAppend($divIDs, "'{$key}Stats'");		
			$this->param($key.'Param', '');		
			$hiddenParams .= '<INPUT name="'.$key.'Param" type="hidden" value="'.$this->ATTRIBUTES[$key.'Param'].'">';
			if ($key == 'RandomImages') {
				$divIDs = ListAppend($divIDs, "'{$key}DisplayStats'");		
				$this->param($key.'DisplayParam', '');		
				$hiddenParams .= '<INPUT name="'.$key.'DisplayParam" type="hidden" value="'.$this->ATTRIBUTES[$key.'DisplayParam'].'">';
			}
			
		}
		//ss_DumpVarDie($this);
		$data = array(
			'Dir'=> $this->classDirectory,
			'StatsTypes'	=>	$displayStats,
			'DivIDs'	=>	$divIDs,
			'Opened'	=>	$this->ATTRIBUTES['Opened'],
		);	
		
		foreach ($displayStats as $key => $display) {				
			
			if (count($display['assets'])) {				
				foreach(array('query','model','view') as $prefix) {					
					$name = $prefix.'_'.strtolower($key).'.php';
					if (file_exists(dirname(__FILE__).'/Services/'.$name)) 
						include("Services/".$name);
				}				
			}
			
		}
		
		
		$this->useTemplate("Head1", $data);
		
		/*
		foreach ($displayStats as $key => $display) {				
			if (count($display['assets'])) {
				print('<div id="'.$key.'Stats" style="display:none;"><a name="'.$key.'StatsAnchor"></a>');
				$this->param($key.'assets', $display['assets']);
				foreach(array('query','model','view') as $prefix) {					
					$name = $prefix.'_'.strtolower($key).'.php';
					if (file_exists(dirname(__FILE__).'/Services/'.$name)) 
						include("Services/".$name);
				}
				print('</div><BR>');
			}
		}
		*/
		$jsShow = '';
		foreach ($opened as $open) {
			if ($open == 'RandomImagesStats') {
				$jsShow .= "showhide('RandomImagesDisplayStats', false);";		
			}
			$jsShow .= "showhide('$open', false);";		
		}
		
		print($hiddenParams);
?>
	</p>
</FORM>
<SCRIPT language="javascript">
	<?=$jsShow?>	
</SCRIPT>
<?php 
	}  else {						
		$this->param('Service');
		//$this->display->layout = 'popup';
		$this->param("DateFrom", "");
		$this->param("DateTo", "");
		
		print <<< EOD
				
		<SCRIPT language="javascript">
				function openWindow(url, name, width, height ) {		
				     w = width
				     h = height
				     x = Math.round((screen.availWidth-w)/2); //center the top edge
				     y = Math.round((screen.availHeight-h)/2); //center the left edge
				     
				     var popup = window.open(url, "PreviewTest", "width="+w+",height="+h+",toolbar=0,location=0,scrollbars=1,statusbar=1,menubar=0,resizable=1,top="+y+",left="+x+",screeenY="+y+",screenX="+x);
				
				     popup.creator=self;	    
				     popup.focus();	     
				     return popup;
				}
		</script>
EOD;



		$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		$customFolder = $rootFolder.'Custom/Classes/statistics';
		
		foreach(array('query','model','view') as $prefix) {
			$name = $prefix.'_'.strtolower($this->ATTRIBUTES['Service']).'.php';
			if (file_exists($customFolder.'/Services/'.$name)) {
				include($customFolder."/Services/".$name);
			} else if (file_exists(dirname(__FILE__).'/Services/'.$name)) {
				include("Services/".$name);
			}
		}
	}
?>