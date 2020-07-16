<?php 
	/*
		FileSystem
	*/
	function ss_storeForAsset($assetID) {
		// To keep down the number of directories in the content store 
		// we get assets to store thier stuff in Custom/ContentStore/Assets/<0..n>/<0..99>/
		// so that for example, asset 107 will be in Custom/ContentStore/Assets/1/7
		// and asset 97 will be in Custom/ContentStore/Assets/0/97
		// this will give 10000 assets with only 100 directories at the first level
		$temp = (int)($assetID / 100);
		$temp2 = $assetID % 100;
		$root = '';//getcwd();
			
		$assetStore = "Custom/ContentStore/Assets/";
		
		if (!file_exists($assetStore.$temp)) {
			mkdir($assetStore.$temp);
		}
			
		if (!file_exists($assetStore.$temp."/".$temp2)) {
			if( !mkdir($assetStore.$temp."/".$temp2) )
				ss_log_message( "Permission ERROR: unable to mkdir $assetStore$temp/$temp2" );
		} 
			
		//return $root."/Assets/";		
		return $assetStore.$temp."/".$temp2."/";		
	}
	
	function ss_secretStoreForAsset($assetID, $privateFolder) {
		// To keep down the number of directories in the content store 
		// we get assets to store thier stuff in Custom/ContentStore/Assets/<0..n>/<0..99>/
		// so that for example, asset 107 will be in Custom/ContentStore/Assets/1/7
		// and asset 97 will be in Custom/ContentStore/Assets/0/97
		// this will give 10000 assets with only 100 directories at the first level
		$temp = (int)($assetID / 100);
		$temp2 = $assetID % 100;
		$root = '';
		$assetStore = "Custom/ContentStore/Assets/";	
		if (!file_exists($assetStore.$temp)) {			
			mkdir($assetStore.$temp);
		}
			
		if (!file_exists($assetStore.$temp."/".$temp2)) {
			mkdir($assetStore.$temp."/".$temp2);
		} 
		
		if (!file_exists($assetStore.$temp."/".$temp2."/".$privateFolder)) {
			mkdir($assetStore.$temp."/".$temp2."/".$privateFolder);
		} 
			
		//return $root."/Assets/";		
		return $assetStore.$temp."/".$temp2."/".$privateFolder;		
	}
	
	function ss_deleteFiles($dir){
		// delete all the files in the directory
		$result = false;
		$dh=opendir($dir);
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath)){
					unlink($fullpath);						
				}
			}
		}
		closedir($dh);
		return true;
	}
	
	
	function ss_deleteFile($dir, $fileName){
		// delete the specified file in the directory
		$result = false;
		$dh=opendir($dir);
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath) AND $file == $fileName){
					$result = unlink($fullpath);
					//print("deleted file: ".$file." suppose to ".$fileName);
					break;
				}
			}
		}
		closedir($dh);
		return $result;
	}
	
	
	
	function ss_deleteFilesWithSub($dir){
		// delete all the files in the directory and its subdirectores as well.
		$dh=opendir($dir);
		while ($file=readdir($dh)){
			if($file!="." && $file!=".."){
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath)){
					unlink($fullpath);
				} else {
					ss_deleteFilesWithSub($fullpath);
				}
			}
		}
		closedir($dh);
		rmdir($dir);
	}
	
	function ss_copyDirectory ($sourceDir, $destinationDir) {	
		
		ss_log_message( "ss_copyDirectory $sourceDir, $destinationDir" );
		$source_dir = opendir($sourceDir) or die ("Opendir $sourceDie Failed!");
		
		if (!is_dir($destinationDir)) mkdir($destinationDir);
		while ($file=readdir($source_dir)){
	
			$source_fullpath = $sourceDir."/".$file;
			$destination_fullpath = $destinationDir."/".$file;
			
			if($file != "." && $file != ".." && !is_dir($file)) {
				copy ($source_fullpath, $destination_fullpath);			
			}
		}
		closedir($source_dir);
	}
	
	function ss_copyDirectoryWithSub ($sourceDir, $destinationDir) {
		
		ss_log_message( "ss_copyDirectoryWithSub $sourceDir, $destinationDir" );
		if (substr($sourceDir, -1) != "/") $sourceDir .="/";
		if (substr($destinationDir, -1) != "/") $destinationDir .="/";
		
		$source_dir = opendir($sourceDir) or die ("Opendir $sourceDie Failed!");
		
		if (!is_dir($destinationDir)) {
			
			mkdir($destinationDir);
		}
		
		while ($file=readdir($source_dir)){
				
			$source_fullpath = $sourceDir.$file;
			$destination_fullpath = $destinationDir.$file;
			
			if($file != "." && $file != "..") {
				if(!is_dir($source_fullpath)) {
					copy ($source_fullpath, $destination_fullpath);			
				} else {
					ss_copyDirectoryWithSub($source_fullpath, $destination_fullpath."/");
				}				
			}
		}
		closedir($source_dir);
	}

	function ss_httpGet($url,$timeout = 3600) {

		$u = parse_url($url);
		ss_paramKey($u,'host','phpcm.im.co.nz');
		ss_paramKey($u,'port',80);
		ss_paramKey($u,'path','/');
		ss_paramKey($u,'query','');

		$location = $u['path'] . (strlen($u['query']) ? '?'.$u['query'] : '');
		
		$fp = fsockopen($u['host'], $u['port'], $errno, $errstr, 30);	// 30 second timeout on initial connection
		stream_set_timeout($fp, $timeout);	// one hour timeout
		if (!$fp) {
		   echo "$errstr ($errno)<br />\n";
		} else {
		   $out = "GET $location HTTP/1.1\r\n";
		   $out .= "Host: {$u['host']}\r\n";
		   $out .= "Connection: Close\r\n\r\n";
		
		   fwrite($fp, $out);
		   while (!feof($fp)) {
		       echo fgets($fp, 128);
		   }
		   fclose($fp);
		}
		
	}

	function ss_log_message_stack( $message )
	{
		$position = 'At ';
		foreach( debug_backtrace() as $index=>$stack )
			if( array_key_exists( 'file', $stack ) )
				$position .= ' '.$stack['file'].':'.$stack['line'];
		ss_log_message( $position."|".$message );
		//ss_log_message( $message );
	}

	function ss_log_message_r(  $announcement, $message_r )
	{
		$message = print_r($message_r, true );
		return ss_log_message( $announcement.'->'.$message );
	}

	function ss_log_message( $message )
	{
		$logFile = '/tmp/messages';

		if( is_array( $GLOBALS ) )
			if( array_key_exists( 'cfg', $GLOBALS ) )
				if( array_key_exists( 'multiSiteLog', $GLOBALS['cfg'] ) )
					if( array_key_exists( 'currentServer', $GLOBALS['cfg'] ) )
						if( array_key_exists( $GLOBALS['cfg']['currentServer'],  $GLOBALS['cfg']['multiSites'] ) )
							if( array_key_exists( $GLOBALS['cfg']['multiSites'][$GLOBALS['cfg']['currentServer']], $GLOBALS['cfg']['multiSiteLog'] ) )
								$logFile = '/tmp/'.$GLOBALS['cfg']['multiSiteLog'][$GLOBALS['cfg']['multiSites'][$GLOBALS['cfg']['currentServer']]];

		$sid = session_id();
		if( strlen( $sid ) > 4 )
			$s = substr( $sid, strlen( $sid ) - 4 );
		else
			$s = "NONE";
		$t = strftime( '%F %T' );
		$file = fopen( $logFile, "a+" );
		fwrite( $file, "$s:$t:" );
		fwrite( $file, $message );
		fwrite( $file, "\n" );
		fclose( $file );
	}

	function ss_grep_log( $session, $currentServer )
	{
		$return_buffer = array();
		$count = 0;
		$countFound = 0;

		set_time_limit ( 600 );

		$logFile = '/tmp/messages';

		if( array_key_exists( 'multiSiteLog', $GLOBALS['cfg'] ) )
			if( array_key_exists( $GLOBALS['cfg']['multiSites'][$currentServer], $GLOBALS['cfg']['multiSiteLog'] ) )
				$logFile = '/tmp/'.$GLOBALS['cfg']['multiSiteLog'][$GLOBALS['cfg']['multiSites'][$currentServer]];

		$sid = $session;
		if( strlen( $sid ) > 4 )
			$s = substr( $sid, strlen( $sid ) - 4 );
		else
			$s = "NONE";


		$file = fopen( $logFile, "r" );
		if( $file )
		{
			while( $line = fgets( $file, 256 ) )
			{
				$count++;
				if( !strncmp( $line, $s, 4 ) )
				{
					$countFound++;
					$return_buffer[] = $line;
				}
			}
			fclose( $file );
		}
		else
			ss_log_message( "unable to open $logFile for read" );

		ss_log_message( "Admin search for session:$session site:$currentServer in file:$logFile returning $countFound lines out of $count lines" );

		return $return_buffer;
	}

	function ss_audit( $operation, $table, $key, $notes )
	{
		$us_id = false;

		$key = (int) $key;
		if( array_key_exists( 'User', $_SESSION )
		 && array_key_exists( 'us_id', $_SESSION['User'] ) )
		 	$us_id =  $_SESSION['User']['us_id'];

		if( $us_id !== false )
		{
			if( ($operation == 'view')
			 || ($operation == 'update')
			 || ($operation == 'delete'))
			{
//				ss_log_message( "insert into audit (au_userid, au_operation, au_table, au_key, au_notes) values ($us_id, '$operation', '".escape( $table )."', $key, '".escape( $notes )."')" );
				query( "insert into audit (au_userid, au_operation, au_table, au_key, au_notes) values ($us_id, '$operation', '".escape( $table )."', $key, '".escape( $notes )."')" );
			}
			else
			{
				query( "insert into audit (au_userid, au_operation, au_table, au_key, au_notes) values ($us_id, 'unknown', '".escape( $table )."', $key, '".escape( $notes )."')" );
			}
		}
	}

?>
