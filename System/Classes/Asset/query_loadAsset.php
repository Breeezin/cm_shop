<?php
	
	global $cfg;
	
	// Figure out what asset we're using
	if (array_key_exists('as_id',$this->ATTRIBUTES) and is_numeric($this->ATTRIBUTES['as_id']))
	{
		// Assign the ID to this asset
		$this->id = $this->ATTRIBUTES['as_id'];
		
	}
	else if (array_key_exists('AssetPath',$this->ATTRIBUTES))
	{
		// Assign the Path to this asset
		$this->path = $this->ATTRIBUTES['AssetPath'];
	}
	else 
	{ 
		// No Asset ID or Path was supplied, so get the asset path from
		// $_SERVER['PATH_INFO'] which is passed into the attributes for us

		// Construct the asset path
		$directory = dirname($_SERVER['SCRIPT_NAME']);
		$indexDotPhp = basename($_SERVER['SCRIPT_NAME']);
		$fullURI = rawurldecode($this->ATTRIBUTES['REQUEST_URI']);
		
		$uri = substr($fullURI,strlen($directory));

		if (strlen($uri) > 1) {
			$this->path = $uri;
			$questionMarkPos = strpos($this->path,'?');
			if ($questionMarkPos !== false) {
				$this->path = substr($this->path,0,$questionMarkPos);
			}
		} else {
			$this->path = $indexDotPhp;	
		}
		
		$this->path = ss_withoutPreceedingSlash($this->path);
		$this->path = stri_replace('index.cfm','', $this->path);
		if (ss_optionExists('Allow Macrons'))
			$this->path = str_replace('AndSharp', '&#', $this->path);
			
		$id = $this->getID();

		if ($id === null)
		{
			// Now strip out any attibute/value pairs
			
			$splitAssetPath = ListToArray($this->path,"/");
			$splitAssetPath_temp = ListToKeyArray($this->path,'/');
			$currentAssetPath = '';
			$currentParentAsset = '1';
			$assetPath = '';
			$assetID = null;
			
			$isAttribute = true;
			$attributeName = '';
				
			foreach ($splitAssetPath as $splitAssetName)
			{
				if ($splitAssetName == 'index.php') $currentParentAsset = 'NULL';	
				if ($currentParentAsset == 'NULL')
					$parentSQL = "IS NULL";	
				else
					$parentSQL = "= {$currentParentAsset}";

				if (!strlen($parentSQL))
					$parentSQL = "IS NULL";	

				$pq = "SELECT as_id, as_name, as_deleted, as_parent_as_id FROM assets
						WHERE as_name LIKE '".escape($splitAssetName)."'
							AND as_parent_as_id $parentSQL";
				$pathAsset = query($pq);
				ss_log_message( $pq );

				if ($pathAsset->numRows() > 0)
				{
					$isDeleted = false;
					while($row = $pathAsset->fetchRow()) 
					{
						if ($row['as_deleted'] == 1)
						{
							// This asset must have been deleted, its in the recycle bin
							// So just use deleted asset
							$isDeleted = true;							
						}
						else
						{
							// This is part of the asset path
							$assetPath .= ss_comma($assetPath,"/").$row['as_name'];
							$assetID = $row['as_id'];
							$currentParentAsset = $row['as_id'];
							$isDeleted = false;
							ss_log_message( "Asset found with as_id:$assetID" );
							break;
						}
					}
					if ($isDeleted)
					{
						ss_log_message_r(  "request for deleted asset", $row );
						header("HTTP/1.0 404 Not Found");
						$this->id = ss_systemAsset('404 Error');						
						$assetID = $this->id;						
						// Load the asset path with the correct path too
						$this->assetPath = null;
						$assetPath = $this->getPath();
					}
				}
				else
				{
					
					//ss_DumpVar($assetPath, $currentAssetPath);
					
					// Ooops... can't find it.. we must have deleted the asset
					// or we're starting to get the asset's attributes
					$currentParentAsset = -12345;
					if ($isAttribute) {
						$attributeName = $splitAssetName;	
					} else {
						$this->ATTRIBUTES[$attributeName] = $splitAssetName;
					}
					$isAttribute = !$isAttribute;
				}
			}

			ss_log_message( "exiting asset search with result as_id:$assetID " );
		
			
			
			if (!$isAttribute)
				$this->ATTRIBUTES[$attributeName] = null;	
			
			// Update the fully qualified asset path;
			$this->path = $assetPath;
			$this->id = $assetID;
		}
		//till here
	} 
	
	if (array_key_exists("AssetParameters",$this->ATTRIBUTES) and $this->ATTRIBUTES['AssetParameters'] != '__PARAMETERS__') {
		eval('$tempAtt = '.$this->ATTRIBUTES['AssetParameters']);
		$this->ATTRIBUTES = array_merge($this->ATTRIBUTES,$tempAtt);	
	}
	
	// Now get the values for this asset from the DB.
	$id = $this->getID();
	
	if ($id === null) 
	{
		header("HTTP/1.0 404 Not Found");
		$this->id = ss_systemAsset('404 Error');
		$id = ss_systemAsset('404 Error');
	}
	
	if ($id !== NULL)
	{
	
		
		$pq = "SELECT * FROM assets left join users on as_owner_au_id = us_id left join asset_types on as_type = at_name WHERE as_id = $id";
		$result = query( $pq );

		if ($result->numRows() == 0)
		{
			// In the case of scheduled assets, the id might be found, but not return a page,
			// so in that case we just fall back to the 404 error.
			ss_log_message( "Umm, WHAT?  $pq" );

			header("HTTP/1.0 404 Not Found");
			$this->id = ss_systemAsset('404 Error');
			$id = ss_systemAsset('404 Error');
			$pq = "SELECT * FROM assets left join users on as_owner_au_id = us_id left join asset_types on as_type = at_name WHERE as_id = $id";
			$result = query( $pq );
		} 
		
		$row = $result->fetchRow();
//		ss_log_message_r(  'Asset Query returns', $row );
	
		// read some stuff into the asset	
		$this->fields = $row;
		
		// Deserialize some fields. Have to assume it's valid fields code
		$cerealField = 'as_serialized';
		
		// If we're using version control of some sort and we're 
		// editing a page then we want to load from the pending cereal if 
		// it has been filled with any data.
		$this->supportsReview = false;
		$this->liveContent = true;
		$this->underReview = false;
		if ($row['as_type'] == 'Page')
		{
			if ($loadForEdit == true)
			{
				if (ss_optionExists('Review Process')) {
					$this->supportsReview = true;
					if (strlen($row['as_pending_serialized']) and $row['as_pending_serialized'] !== 'Delete') {
						$this->liveContent = false;
						$cerealField = 'as_pending_serialized';	
					}
					if ($row['as_review'] == 1) {
						$this->underReview = true;
					}
				}
			}
			else if (array_key_exists('PendingCereal',$this->ATTRIBUTES))
			{
				// check that the user can review
				if (ss_HasPermission('CanReviewAsset',$this->getID()) and $row['as_pending_serialized'] !== 'Delete') {
					$cerealField = 'as_pending_serialized';
				}
			}
			
		}
			
		if (strlen($row[$cerealField]))
		{
			if( !($this->cereal = @unserialize($row[$cerealField]) ) )
				ss_log_message( "Unable to unserialize field $cerealField: of asset id $id '{$row[$cerealField]}'" );
			if ($this->cereal == NULL) $this->cereal = array();	
		}
		else
			$this->cereal = array();	

		if (strlen($row['as_layout_serialized']))
		{
			$this->layout = @unserialize($row['as_layout_serialized']);
			if ($this->layout == NULL) $this->layout = array();	
		}
		else 
			$this->layout = array();	
	}

//	ss_log_message_r(  'Asset finished loading ', $this );
?>
