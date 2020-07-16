<?php
  // Make accessing the backstack a bit nicer 
  function getBackURL(){
		return $_SESSION['BackStack']->getURL();
	}
	
	class BackStack {
	
		var $attributeSets = array();
		var $currentAttributeSet;
		var $currentURL = NULL;
		var $lastBackStructureRequested = NULL;
		var $currentBackStructure = NULL;
		
		function restoreAttributeSet($index) {
		
			// Check a valid BackStructure was requested
			if ($index > count($this->attributeSets)) {
				locationRelative('index.php');
				exit;				
			}

			if( !array_key_exists( $index, $this->attributeSets ) || !is_array( $this->attributeSets[$index] ) )
			{
				// do something nasty.
				sleep( 300 );
				exit;
			}

			// Restore the back stack. Don't over write any existing attributes
			// $_REQUEST = $this->attributeSets[$index];
			foreach ($this->attributeSets[$index] as $key => $value) {
				if (!array_key_exists($key,$_REQUEST)) {
					$_REQUEST[$key] = $value;
				}
			}

			// Update $act to match the new attribute set
			if (array_key_exists('act',$_REQUEST)) {
				$GLOBALS['act'] = $_REQUEST['act'];
			}

			$this->lastBackStructureRequested = $index;
            $this->currentBackStructure = $index;
			
		}
		
		function storeAttributeSet() {
			// Copy the ATTRIBUTES into temp
			$temp = $_REQUEST;
			
			// Remove the session id and other things we dont need
			unset($temp['PHPSESSID']);
			unset($temp['BackStructure']);
			if (array_key_exists('ProductAdded',$temp)) unset($temp['ProductAdded']);
			
			// Remember it for later
			$this->currentAttributeSet = $temp;
			$this->currentURL = NULL;
		}
		
		// Returns a URL to the current page and commits the current page
		// into the attribute set array
		function getURL() {
			global $cfg;
		
			if ($this->currentURL == NULL) {
				
				// If a back structure was requested and no attributes 
				// were modified... we can use the same backstructure again
				if (($this->lastBackStructureRequested != NULL) && 
					($this->attributeSets[$this->lastBackStructureRequested] ==	$this->currentAttributeSet)) {
					$currentSet = $this->lastBackStructureRequested;
                    $this->currentBackStructure = $this->lastBackStructureRequested;
				} else {
					// Otherwise.. add it to the set
					$this->attributeSets[count($this->attributeSets)+1] = $this->currentAttributeSet;
                    $this->currentBackStructure = count($this->attributeSets);
					$currentSet = count($this->attributeSets);
				}
				$this->currentURL = $cfg['currentServer']."index.php?BackStructure=".$currentSet;
			}
			
			return $this->currentURL;
		}
	
		// call to getURL sets $lastBackStructureRequested, so lets pass in this to get the URL
		function getIndexedURL($index,$default='/') {
			global $cfg;
            $url = $default;
			if ( count($this->attributeSets) >= $index ) {
                $url = $cfg['currentServer']."index.php?BackStructure=".$index;
            }
			return $url;
		}
        
        
		// call to getURL, then grab the index, then use this to grab the indexedURL with getIndexedURL
		function getCurrentIndex() {
            $this->getURL();
             return $this->currentBackStructure;
		}
	
	}
?>
