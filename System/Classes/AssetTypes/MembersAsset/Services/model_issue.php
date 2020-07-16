<?php 
	$errors = array();
	if (array_key_exists('Do_Service',$this->ATTRIBUTES))
	{	
		if( ( $us_id = (int)$this->ATTRIBUTES['us_id'] ) > 0 )
		{
			$cq_id = 0;
			if( array_key_exists( 'canned_question', $this->ATTRIBUTES ) )
				$cq_id = (int) $this->ATTRIBUTES['canned_question'];

			ss_log_message( "claiming user id $us_id submitting issue" );
			// create new issue
			$tr_id = 0;
			if( array_key_exists( 'order_number', $this->ATTRIBUTES ) )
				$tr_id = (int)$this->ATTRIBUTES['order_number'];
			if( $tr_id > 0 )
			{
				$existing = getRow( "select * from client_issue where ci_us_id = $us_id and ci_transaction_number = $tr_id" );
				if( $existing && $existing['ci_transaction_number'] ==  $tr_id )
					$issue_num = $existing['ci_id'];
				else
				{
					if( $cq_id )
						query( "insert into client_issue (ci_us_id, ci_transaction_number, ci_cq_id) values ($us_id, $tr_id, $cq_id)" );
					else
						query( "insert into client_issue (ci_us_id, ci_transaction_number) values ($us_id, $tr_id)" );
					$issue_num = getLastAutoIncInsert();
				}
			}
			else
			{
				$existing = getRow( "select * from client_issue where ci_us_id = $us_id and ci_transaction_number IS NULL" );
				if( $existing )
					$issue_num = $existing['ci_id'];
				else
				{
					if( $cq_id )
						query( "insert into client_issue (ci_us_id, ci_cq_id) values ($us_id, $cq_id)" );
					else
						query( "insert into client_issue (ci_us_id) values ($us_id)" );
					$issue_num = getLastAutoIncInsert();
				}
			}

			$text = trim(escape(strip_tags( $this->ATTRIBUTES['issue'])));

			if( strlen( $text ) )
			{
				if( strlen( $this->ATTRIBUTES['chosen_box'] ) )
					$text .= escape(strip_tags( "\nChosen box for this order is ".$this->ATTRIBUTES['chosen_box']));

				ss_log_message( "text length is ".strlen( $text ) );

				// create an issue entry
				query( "insert into client_issue_entry (ce_ci_id, ce_text, ce_session, ce_website) values ($issue_num, '$text','"
									.session_id()."', '".$GLOBALS['cfg']['currentServer']."')" );
				query( "update client_issue set ci_closed = NULL where ci_id =  $issue_num" );

				ss_log_message( "New Entry for issue  $issue_num" );
				ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_FILES );

				if( array_key_exists( 'photo', $_FILES ) && strlen( $_FILES['photo']['name'] ) && is_uploaded_file($_FILES['photo']['tmp_name']) )
				{
					$photo_name = escape( $_FILES['photo']['name'] );
					if( $pos = strrpos( $photo_name, '.' ) )
						$photo_name = substr( $photo_name, 0, $pos );
					$user = ss_getUser();
					$email = safe( $user['us_email'] );
					if( strlen( $email ) )
					{
	//					$finfo = finfo_open(FILEINFO_MIME_TYPE);
	//					$mime_type = finfo_file($finfo, $_FILES['photo']['tmp_name']);
	//					if( strstr( $mime_type, 'image' ) )
	/*
	UPLOAD_ERR_OK - Value: 0; There is no error, the file uploaded with success.
	UPLOAD_ERR_INI_SIZE - Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
	UPLOAD_ERR_FORM_SIZE - Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
	UPLOAD_ERR_PARTIAL - Value: 3; The uploaded file was only partially uploaded.
	UPLOAD_ERR_NO_FILE - Value: 4; No file was uploaded.
	UPLOAD_ERR_NO_TMP_DIR - Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
	UPLOAD_ERR_CANT_WRITE - Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
	*/
						if( $_FILES['photo']['error'] == UPLOAD_ERR_OK )
						{
							$old_error_handler = set_error_handler("ignoreErrorHandler");

							$info = array();
							$info[] = 0;
							$info[] = 0;
							$info[] = 0;
							$info = @getimagesize($_FILES['photo']['tmp_name']);
							$srcWidth  = $info[0];
							$srcHeight = $info[1];
							$imageType = $info[2];
							$isImage = false;
							$filenameExtenstion = "";

							if( $imageType == IMAGETYPE_GIF )
							{
								$isImage = true;
								$filenameExtenstion = 'gif';
							}
							if(  $imageType == IMAGETYPE_JPEG )
							{
								$isImage = true;
								$filenameExtenstion = 'jpeg';
							}
							if(  $imageType == IMAGETYPE_PNG )
							{
								$isImage = true;
								$filenameExtenstion = 'png';
							}
							if(  $imageType == IMAGETYPE_SWF )
							{
								$isImage = true;
								$filenameExtenstion = 'swf';
							}
							if(  $imageType == IMAGETYPE_PSD )
							{
								$isImage = true;
								$filenameExtenstion = 'psd';
							}
							if(  $imageType == IMAGETYPE_BMP )
							{
								$isImage = true;
								$filenameExtenstion = 'bmp';
							}
							if(  $imageType == IMAGETYPE_TIFF_II )
							{
								$isImage = true;
								$filenameExtenstion = 'tiff';
							}
							if(  $imageType == IMAGETYPE_TIFF_MM )
							{
								$isImage = true;
								$filenameExtenstion = 'gif';
							}
							if(  $imageType == IMAGETYPE_JPC )
							{
								$isImage = true;
								$filenameExtenstion = 'tiff';
							}
							if(  $imageType == IMAGETYPE_JP2 )
							{
								$isImage = true;
								$filenameExtenstion = 'jp2';
							}
							if(  $imageType == IMAGETYPE_JPX )
							{
								$isImage = true;
								$filenameExtenstion = 'jpx';
							}
							if(  $imageType == IMAGETYPE_JB2 )
							{
								$isImage = true;
								$filenameExtenstion = 'jb2';
							}
							if(  $imageType == IMAGETYPE_XBM )
							{
								$isImage = true;
								$filenameExtenstion = 'xbm';
							}
							if(  $imageType == IMAGETYPE_ICO )
							{
								$isImage = true;
								$filenameExtenstion = 'ico';
							}


							if( !$isImage )
							{
								echo "This is not an image, please make it an image";
								die;
							}
							else
							{
								query( "insert into client_issue_attachment (cia_ci_id) values ($issue_num)" );
								$attach_num = getLastAutoIncInsert();
								$new_dir_name = "issues/photos/{$email[0]}/{$email}/";
								if( !is_dir( $new_dir_name ) )
									mkdir( $new_dir_name, 0777, true );

								$new_name = $new_dir_name.$attach_num;

								if( move_uploaded_file($_FILES['photo']['tmp_name'], $new_name) )
										query( "update client_issue_attachment set cia_filename = '$new_name', cia_name = '$photo_name.$filenameExtenstion' where cia_id = $attach_num" );
							}
						}
						else
						{
							ss_log_message( "Issue  $issue_num upload error" );
							switch( $_FILES['photo']['error'] )
							{
							case UPLOAD_ERR_INI_SIZE:
							case UPLOAD_ERR_FORM_SIZE:
								echo "Your photo is too large";
								die;

							case UPLOAD_ERR_PARTIAL:
								echo "Comms error, please try again";
								die;
							
							case UPLOAD_ERR_NO_FILE:
								echo "No file";
								die;

							case UPLOAD_ERR_NO_TMP_DIR:
							case UPLOAD_ERR_CANT_WRITE:
								echo "System error";
								die;
							}
						}
	//					finfo_close($finfo);
					}
				}
			}
			else
			{
				echo "No text detected, try again";
				ss_log_message( "No text supplied" );
				die;
			}

			locationRelative($assetPath.'/Service/Messages');								
		}
		else
		{
			ss_log_message( "guest submitting issue" );

			$error = '';
			$text = trim(escape(strip_tags( $this->ATTRIBUTES['issue'] )));

			if( array_key_exists( 'email', $this->ATTRIBUTES ) )
			{
				$email = safe( $this->ATTRIBUTES['email'] );
				$exists = getRow( "select * from users where us_email = '$email'" );
				if( $exists )
					location( "/Members?Email=".$email );
				else
					if( strlen( $text ) )
					{
						if( stristr( $this->ATTRIBUTES['human'], 'tobor' ) === FALSE )
						{
							echo "you typed in {$this->ATTRIBUTES['human']}";
							die;
						}
						// verify it....
						set_error_handler('noErrorHandler');

						require_once( "System/Libraries/SMTPVerify/SMTPVerify.php" );
						$sender = 'admin@acmerockets.com';
						$SMTP_Validator = new SMTP_validateEmail();
						$SMTP_Validator->debug = true;
						$results = $SMTP_Validator->validate(array($email), $sender);

						if( !$results[$email] )
						{
							ss_log_message( "invalid email address $email" );
							$error = "Your email address is unusable, please provide another<br />I got this >".$results[$email].'.error';
						}
						else
						{
							query( "insert into client_issue (ci_verified_email) values ('$email')" );
							$issue_num = getLastAutoIncInsert();
							
							$ins = 'from '.escape(strip_tags( $this->ATTRIBUTES['name'] ))."\n".$text;
							query( "insert into client_issue_entry (ce_ci_id, ce_text, ce_session, ce_website) values ($issue_num, '$ins','"
								.session_id()."', '".$GLOBALS['cfg']['currentServer']."')" );
						}
						echo "<html>Your issue has been saved and will be responded to with 24 hours.<br/><a href='/'>Click here to return home</a></html>";
						die;
					}
					else
						ss_log_message( "No text supplied" );
			}
		}
	}
?>
