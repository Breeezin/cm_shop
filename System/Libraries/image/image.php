<?php

// error handler function
function ignoreErrorHandler($errno, $errstr, $errfile, $errline)
{
}

	class image {
		var $src = NULL;
		var $dest = NULL;
		var $srcWidth  = NULL;
		var $srcHeight = NULL;
		var $imageType = NULL;
		var $mimeType  = NULL;
		var $filenameExtenstion = NULL;
		var $mime_types = NULL;
		var $watermark = NULL;
		var $date = null;
		var $commandString = '';
		var $path = '/usr/bin/';
		var	$cache_dir = 'picture_cache/';
		var $cache_id = NULL;
		var $applied = false;
		
		function __construct($src)
		{
			global $savedMime;
			
			$this->src = $src;
			$this->dest = $src;
			if ($savedMime == NULL) {
				require('mimeLookup.php');
				$savedMime = $this->mime_types;
			} else {
				$this->mime_types = $savedMime;
			}

			if (file_exists($this->src)) {
				$this->date = filemtime($this->src);
			}
			
			$this->examine();
		}
			
		function getEtag()
		{
//			return '"'.md5($this->src.$this->date).'"';
			return md5($this->src.$this->date.$this->commandString.$this->watermark.session_id());
			
		}
		
		function examine()
		{
			/* Note that this function does not call out to imageMagick
			 * the getimagesize() function built into PHP is a bajillion
			 * times faster and it's not part of GD anyway.
			 */
			 $this->srcWidth = 50;	
			 $this->srcHeight = 50;	
			 $this->imageType = 'GIF';	

			 $this->dest = urldecode($this->dest);
			 if (file_exists($this->dest)) {
				$old_error_handler = set_error_handler("ignoreErrorHandler");
				$info = array();
				$info[] = 0;
				$info[] = 0;
				$info[] = 0;
				$this->dest = str_replace( "//", "/", $this->dest );
//				ss_log_message( $this->dest );
				$info = @getimagesize($this->dest);
				$this->srcWidth  = $info[0];
				$this->srcHeight = $info[1];
				$this->imageType = $info[2];
				$isImage = false;

				if( $this->imageType == IMAGETYPE_GIF )
				{
					$isImage = true;
					$this->filenameExtenstion = 'gif';
				}
				if(  $this->imageType == IMAGETYPE_JPEG )
				{
					$isImage = true;
					$this->filenameExtenstion = 'jpeg';
				}
				if(  $this->imageType == IMAGETYPE_PNG )
				{
					$isImage = true;
					$this->filenameExtenstion = 'png';
				}
				if(  $this->imageType == IMAGETYPE_SWF )
				{
					$isImage = true;
					$this->filenameExtenstion = 'swf';
				}
				if(  $this->imageType == IMAGETYPE_PSD )
				{
					$isImage = true;
					$this->filenameExtenstion = 'psd';
				}
				if(  $this->imageType == IMAGETYPE_BMP )
				{
					$isImage = true;
					$this->filenameExtenstion = 'bmp';
				}
				if(  $this->imageType == IMAGETYPE_TIFF_II )
				{
					$isImage = true;
					$this->filenameExtenstion = 'tiff';
				}
				if(  $this->imageType == IMAGETYPE_TIFF_MM )
				{
					$isImage = true;
					$this->filenameExtenstion = 'gif';
				}
				if(  $this->imageType == IMAGETYPE_JPC )
				{
					$isImage = true;
					$this->filenameExtenstion = 'tiff';
				}
				if(  $this->imageType == IMAGETYPE_JP2 )
				{
					$isImage = true;
					$this->filenameExtenstion = 'jp2';
				}
				if(  $this->imageType == IMAGETYPE_JPX )
				{
					$isImage = true;
					$this->filenameExtenstion = 'jpx';
				}
				if(  $this->imageType == IMAGETYPE_JB2 )
				{
					$isImage = true;
					$this->filenameExtenstion = 'jb2';
				}
				if(  $this->imageType == IMAGETYPE_XBM )
				{
					$isImage = true;
					$this->filenameExtenstion = 'xbm';
				}
				if(  $this->imageType == IMAGETYPE_ICO )
				{
					$isImage = true;
					$this->filenameExtenstion = 'ico';
				}

				if( !$isImage )
				{
					if( !ss_isAdmin() )
					{
						echo "Not an image ".$this->imageType;
//						ss_log_message( "Dying here" );
//						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );
						die;
					}
					$this->mimeType = mime_content_type( $this->dest );
				}
				else
					$this->mimeType = image_type_to_mime_type( $this->imageType );


				if( ss_isAdmin() )
				{
//					ss_log_message( "ImageHandler loaded" );
//					ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $this );
				}
				set_error_handler( $old_error_handler );
			 }

			/*
			$explosion = explode(".", $this->dest);
			if (count($explosion) > 1) {
				$this->filenameExtenstion = $explosion[count($explosion) - 1];
			} else {
				$this->filenameExtenstion = '';	
			}
			*/
			
			// because using PHP earlier than 4.3 we must work out the 
			// mime outself.
			//$this->mimeType = $this->mime_lookup($this->filenameExtenstion);
		}
		
		function getDimensions()
		{
			return array($this->srcWidth, $this->srcHeight);
		}
		
		function getWidth()
		{
			return $this->srcWidth;
		}
		
		function getHeight() 
		{
			return $this->srcHeight;
		}

		function display()
		{
			$headers = getallheaders();
//			ss_log_message( "image.display" );
//			ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $headers );
			if (array_key_exists("If-None-Match",$headers) )
			{
				$srch = $headers["If-None-Match"];
				if( ($srch[0] == '"') || ($srch[0] == "'" ) )
					$srch = substr( $srch, 1 );
				$srch = substr( $srch, 0, strspn( $srch, "0123456789abcdef" ) );

				$sql  = "select * from etag_user_tracking where ut_sent_checksum = '$srch' and ut_cache_id = '{$this->cache_id}'";
//				ss_log_message( "Current User ID:".ss_getUserID() );
//				ss_log_message( $sql );
				if( $rw = getRow( $sql ) )
				{
//					ss_log_message( "found entry in etag_user_tracking for user id {$rw['ut_us_id']} for image {$this->cache_id}" );
					// current User ?
					if( ss_getUserID() > 0 )	// logged in user
					{
						if( ( ss_getUserID() != $rw['ut_us_id'] ) && ($rw['ut_ip_address'] == $_SERVER['REMOTE_ADDR'] ) )
						{
							if( $rw['ut_us_id'] >= 0 )
							{
								ss_log_message( "Etag mismatch on logged in user should be user {$rw['ut_us_id']} but currently ".ss_getUserID() );
								query( "insert into user_tracking (ut_us_id, ut_alt_us_id ) values ('{$rw['ut_us_id']}', '".ss_getUserID()."')" );
								query( "insert into user_tracking (ut_alt_us_id, ut_us_id ) values ('{$rw['ut_us_id']}', '".ss_getUserID()."')" );
								$errors = '';
								$_SESSION['WARNING'] = "This computer also logged in as different user {$rw['ut_us_id']}";

								if( $ur = getRow( "select * from users where us_id = {$rw['ut_us_id']}" ) )
								{
									if( $ur['us_bl_id'] > 0 )
									{
										ss_audit( 'other', 'users', ss_getUserID(), "user was seen as BLACKLISTED ID:{$rw['ut_us_id']} {$ur['us_first_name']} {$ur['us_last_name']}" );
										$_SESSION['Blacklist'] = true;
										$_SESSION['WARNING'] = "This computer also logged in as blacklisted user ID {$rw['ut_us_id']} {$ur['us_first_name']} {$ur['us_last_name']}";
									}
									else
										ss_audit( 'other', 'users', ss_getUserID(), "user was seen as ID:{$rw['ut_us_id']} {$ur['us_first_name']} {$ur['us_last_name']}" );
								}

//								ss_login( $ur['us_id'], $errors );
//								if( strlen( $errors ) )
//									ss_log_message( $errors );
							}
							else
							{
								// was a guest, update that part of the table to the new us_id
								// query( "update etag_user_tracking set ut_us_id = ".ss_getUserID()." where ut_us_id = {$rw['ut_us_id']}" );
								query( "delete from etag_user_tracking where ut_us_id = {$rw['ut_us_id']}" );
							}
						}
						else
							{}
					}
					else		// guest user, still has session
						if( $rw['ut_us_id'] >= 0 )	// a real user, log them in
						{
							if( $ur = getRow( "select * from users where us_id = {$rw['ut_us_id']}" ) )
							{
								if( false )
								//if( $ur['us_do_not_track'] == "false" && !($ur['us_admin_level'] > 0) && ($rw['ut_ip_address'] == $_SERVER['REMOTE_ADDR'] ) )
								{
									ss_log_message( "auto logging in as ID:{$ur['us_id']} {$ur['us_first_name']} {$ur['us_last_name']}" );
									$errors= '';
									ss_login( $ur['us_id'], $errors );
									if( strlen( $errors ) )
										ss_log_message( $errors );
								}
								else
									ss_log_message( "Stopping autologin for ID:{$ur['us_id']} {$ur['us_first_name']} {$ur['us_last_name']} as us_do_not_track is {$ur['us_do_not_track']} or admin level is {$ur['us_admin_level']}" );
							}
						}
						else				// a guest user, reset them to their last session, nuke this one
						{
/*							if( $rw2 = getRow( "select * from guest_users where gu_id = ".(-$rw['ut_us_id']) ) )
							{
								if( $rw['ut_us_id'] != ss_getUserID() )
								{
									ss_log_message( "mismatch on guest removing guest id ".ss_getUserID());
									query( "delete from guest_users where  gu_id = ".ss_getUserID() );

									$_SESSION['User']['us_id'] = $rw['ut_us_id'];
								}
							}
							else
								ss_log_message( "user not found" );
								*/
						}
				}
				else
				{
					// make one with current session data
					// ss_log_message( "not found" );
					//$sql = "insert into etag_user_tracking (ut_us_id, ut_cache_id, ut_sent_checksum) values (".ss_getUserID().", '{$this->cache_id}', '".$this->getEtag()."' )";
					$sql = "insert into etag_user_tracking (ut_us_id, ut_cache_id, ut_sent_checksum, ut_ip_address) values (".ss_getUserID().", '{$this->cache_id}', '$srch', '${_SERVER['REMOTE_ADDR']}' )";
//					ss_log_message( $sql );
					query( $sql );
				}
			}
//			else
//				ss_log_message( "No headers 'If-None-Match' here" );

			if (file_exists($this->dest))
			{
				// check if we know the modified date
				if ($this->date !== null) {
					$gmt_mtime = gmstrftime("%a, %d %b %Y %T",$this->date).' GMT';

					// check if not modified
					$notModified = false;
					if (array_key_exists("ETag",$headers) and $headers["ETag"] == '"'.$this->getEtag().'"') {
						$notModified = true;	
					} else if (array_key_exists("If-Modified-Since",$headers) and $headers["If-Modified-Since"] == $gmt_mtime) {
						$notModified = true;
					}
					
					// if not modified.. don't return the image again
					if ($notModified) {
       					header("HTTP/1.1 304 Not Modified");
						// header("Expires: ".gmstrftime("%a, %d %b %Y %T",time()-86400*300).' GMT');
						//header("Expires: -1");
						header("Cache-Control: max-age=600, private, must-revalidate");
						header("Pragma: ");
	       				exit;
					}
					
					header("Last-Modified: ".$gmt_mtime);
				}   				
		//		ss_log_message("Expires: ".gmstrftime("%a, %d %b %Y %T",time()+86400*300).' GMT');
	//			header("Expires: -1");
				header("Cache-Control: max-age=600, private, must-revalidate");
				header("Pragma: ");
				
				header("Content-Type: {$this->mimeType}");
				header("Content-Length: ".filesize($this->dest));
			//	ss_log_message('ETag: "'.$this->getEtag().'"');
				header('ETag: "'.$this->getEtag().'"');

				$sql = "select * from etag_user_tracking where ut_us_id = ".ss_getUserID()." and ut_cache_id = '{$this->cache_id}'";
				if( $rw = getRow( $sql ) )
				{
					if( $rw['ut_sent_checksum'] != $this->getEtag() )
						@query( "update etag_user_tracking set ut_sent_checksum = '".$this->getEtag()."', ut_ip_address = '${_SERVER['REMOTE_ADDR']}' where ut_us_id = ".ss_getUserID()." and ut_cache_id = '{$this->cache_id}'" );
				}
				else
				{
					$sql = "insert into etag_user_tracking (ut_us_id, ut_cache_id, ut_sent_checksum, ut_ip_address) values (".ss_getUserID().", '{$this->cache_id}', '".$this->getEtag()."', '${_SERVER['REMOTE_ADDR']}')";
			//		ss_log_message( $sql );
					query( $sql );
				}

				/* Read in the file, and send it to the browser. */
  				readfile($this->dest);
	//			ss_log_message( "sending ".$this->dest);
			}
			exit;			
		}

		function addCommand($command) {
//			$command = str_replace( "\"';", "", $command );
//			$this->commandString .= ' '.$command;	
		}
		
		function addGeometryCommand($geometry) {
			if( strlen( $geometry ) == strspn( $geometry, "0123456789x" ) )
				$this->commandString .= ' -geometry '.$geometry;
//			else
//				ss_DumpVarDie( $this );
		}

		function addRotateCommand($degrees) {
			if( strlen( $degrees ) == strspn( $degrees, "0123456789-" ) )
				$this->commandString .= ' -rotate '.$degrees;	
//			else
//				ss_DumpVarDie( $this );
		}

		function addWatermark( $file )
		{
			$this->watermark = $file;
			/*
			if( strlen( $file ) && file_exists( "images/".$file ) )
			{
				$newFilename = $picture_cache. '/' . md5("2".$this->src.$file.$this->commandString) . ".{$this->filenameExtenstion}";

				if (!file_exists($newFilename) and file_exists($this->src))
				{
					$cmd = $this->path."composite -dissolve 25% -gravity south images/$file {$this->src} $newFilename";
					ss_log_message( $cmd );
					if( exec( $cmd ) )
					{
						ss_log_message( "Failed" );
					}
				}
				$this->src = $newFilename;
				$this->examine();

			}
			else
				ss_log_message( "Missing watermark "."images/".$file );
			*/
		}

		function applyCommands() 
		{
			if( $this->applied )
			{
//				ss_log_message( "Image: applied already" );
				return;
			}

			// look in cache first

//			if( strlen( $this->commandString ) || strlen( $this->watermark ) )		// source needs modifying
			if( true )
			{


				$sql = "select * from picture_cache where source = '{$this->src}' and commands = '{$this->commandString}' and watermark = '{$this->watermark}'";
//				ss_log_message( $sql );
				$cacheRow = getRow( $sql );

				if( is_array( $cacheRow ) and array_key_exists( 'cache', $cacheRow ) and file_exists( $cacheRow['cache'] ) )
				{
//					ss_log_message( "grabbing cached version of '{$this->src}' from {$cacheRow['cache']}" );
					$this->dest = $cacheRow['cache'];
					$this->cache_id = $cacheRow['cache_id'];
				}
				else
				{
					if( !file_exists( $cacheRow['cache'] ) )
						query( "delete from picture_cache where source = '{$this->src}' and commands = '{$this->commandString}' and watermark = '{$this->watermark}'" );

					/* Take a size specification in ImageMagick format 
					 * copy the image
					 * apply the command list
					 * become the copy
					 */

					// hash all inputs to get hopefully unique name
					$newFilename = $this->cache_dir . md5($this->src.$this->commandString.$this->watermark) . "." .$this->filenameExtenstion;

					$revision = 1;
					while( file_exists( $newFilename ) && $revision < 10 )		// bugger, clash
					{
						$newFilename = $this->cache_dir . md5($this->src.$this->commandString.$this->watermark) . "-$revision." .$this->filenameExtenstion;
						$revision++;
					}

					if( $revision >= 10 )
					{
						ss_log_message( "Image: clash revisions exceeded" );
						die;
					}

					if( !is_file( $this->src ) )
						die;

					copy($this->src, $newFilename);

					if( strlen( $this->watermark ) && file_exists( "images/".$this->watermark ) )
					{
						$cmd = $this->path."composite -dissolve 25% -gravity south images/$this->watermark {$this->src} $newFilename";
						ss_log_message( $cmd );
						if( exec( $cmd ) )
							ss_log_message( "Image '$cmd' Failed" );
					}

					if( strlen( $this->commandString ) )
					{
						$cmd = $this->path."mogrify ".$this->commandString." $newFilename 2>&1";
						ss_log_message( $cmd );
						if( exec( $cmd ) )
							ss_log_message( "Image '$cmd' Failed" );
					}

					if( !Query( "insert into picture_cache( source, commands, watermark, cache ) values ('{$this->src}', '{$this->commandString}', '{$this->watermark}', '{$newFilename}')" ) )
						ss_log_message( "Image cache insert failed" );

					$this->cache_id = getLastAutoIncInsert();
					$this->dest = $newFilename;
					$this->examine();
				}
			}
			else
			{
				
			}

		$this->applied = true;
		}
		
		function mime_lookup($ext) {
			if( $this->mime_types == null )
				return null;
			if (array_key_exists(strtolower($ext),$this->mime_types)) {
				return $this->mime_types[strtolower($ext)];	
			} else {
				return null;	
			}
		}
	}
?>
