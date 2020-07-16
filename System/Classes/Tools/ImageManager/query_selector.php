<?php
	/* We'll be needing the image class */
	require_once('System/Libraries/image/image.php');
	
	/* Make sure we have the appropriate things */
	$this->param("Directory", "images/"); // Relative path to images directory

	/* Make path absolute */
	$srcPath = expandPath($this->ATTRIBUTES["Directory"]);
			
	/* Get a list of the images in the directory */
	$directory = dir($srcPath);
	
	// set the title for the page
	$this->Display['Title'] = "Image Manager";
	
	$data['files'] = array();
	$evenRow = true;
	while($entry = $directory->read()) { 
		if ($entry != '.' and $entry != '..' 
			and !is_dir($srcPath . "/" . $entry))
			{
			list($base, $ext) = explode('.', $entry);
			if (in_array($ext, array('jpg','gif','png'))) {
				// Add an entry to the files array
				$temp = array();
				$temp['fileName'] = $entry;
				$temp['URL'] = $this->ATTRIBUTES["Directory"] . "/$entry";
				
				$tempImage = new image("$srcPath/$entry");
				list($imWidth,$imHeight) = $tempImage->getDimensions();
				$temp['dimensions'] = "$imWidth x $imHeight";
				
				$temp['rowClass'] = $evenRow ? 'EvenRow' : 'OddRow';
				$evenRow = !$evenRow;
						
				array_push($data['files'], $temp);
			}
		}
	}
?>