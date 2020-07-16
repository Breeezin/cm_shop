<?php
	global $cfg;
	global $image_dir;

	$image_dir = 'Custom/ContentStore/Assets/5/14/ProductImages';

	function valid_image( $image_name, $row )
	{
		global $image_dir;

		return( array_key_exists( $image_name, $row )
			&& strlen( $row[$image_name] )
				&& file_exists( "$image_dir/{$row[$image_name]}" ));
	}

	$this->param("Image", "");   /* Relative path to image */
	$this->param("Size", "");	 /* ImageMagick mogrify -geometry format */
	$this->param("Rotate","");
	$this->param("MaxHeight","");
	$this->param("MaxWidth","");
	$this->param("Product","");
	$this->param("ProductV","");
	$this->param("ProductFull","");
	$this->param("ProductThumb","");
	$this->param("Category","");
	$this->param("Flag","");
	$this->param("N","1");

/*
	if( $_SERVER['REMOTE_ADDR'] == '67.231.16.120' )
	{
		exec( "/tmp/getme ".posix_getpid() );
		disconnect( );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_SERVER );
		ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $_REQUEST );
		die;
	}
*/

	require_once('System/Libraries/image/image.php');

	$img = NULL;

	if( strlen( $this->ATTRIBUTES["Image"] ) )
	{
		$path = $this->ATTRIBUTES["Image"];
		if( !ss_isAdmin( ) )
		{
			if( !strncmp( $path, 'issues', 6 ) )
			{
				// make sure that they own this image...
				$em = $_SESSION['User']['us_email'];
				if( !strpos( $path, '/'.$em[0].'/'.$em.'/' ) )
				{
					ss_log_message( "Unauthorized request for $path from $em" );
					die;
				}
			}
		}
		$srcPath = expandPath($path);
		$img = new image($srcPath);
	}

	// show image by itself
	if( strlen( $this->ATTRIBUTES["ProductFull"] ) )
	{
		$N = (int) $this->ATTRIBUTES["N"];
		$pr_id = (int) $this->ATTRIBUTES["ProductFull"];
		$Q_image = query( "select pr_image1_thumb, pr_image{$N}_normal, pr_add_watermark from shopsystem_products where pr_id = $pr_id" );
		if( $row = $Q_image->fetchRow() )
		{
			if( valid_image( "pr_image{$N}_normal", $row ) )
			{
				$srcPath = expandPath( "$image_dir/".$row["pr_image{$N}_normal"] );
				$img = new image($srcPath);
			}
			else
				if( valid_image( "pr_image2_normal", $row ) )
				{
					$srcPath = expandPath( "$image_dir/".$row["pr_image2_normal"] );
					$img = new image($srcPath);
				}
				else
					if( valid_image( "pr_image1_thumb", $row ) )
					{
						$srcPath = expandPath( "$image_dir/".$row["pr_image1_thumb"] );
						$img = new image($srcPath);
					}
					else
					{
						ss_log_message( "No image found for product ID $pr_id" );
						ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $row );
						$srcPath = expandPath( "$image_dir/coming_soon.png" );
						$img = new image($srcPath);
					}

			ss_log_message( "ProductFull src $srcPath" );

			if( $row['pr_add_watermark'] > 0 )
			{
				if( $Qw = getRow( "select lg_watermark from languages where lg_id = {$cfg['currentLanguage']}" ) )
					if( strlen( $Qw['lg_watermark'] ) )
						$img->addWatermark( $Qw['lg_watermark'] );
//					else
//						ss_log_message( "No watermark for language {$cfg['currentLanguage']}" );
			}
		}
	}

	// show image in product detail page
	if( strlen( $this->ATTRIBUTES["ProductV"] ) )
	{
		$pr_id = (int) $this->ATTRIBUTES["ProductV"];
		$N = (int) $this->ATTRIBUTES["N"];
		$Q_image = query( "select pr_image1_thumb, pr_image{$N}_normal, pr_image2_normal, pr_add_watermark from shopsystem_products where pr_id = $pr_id" );
		if( $row = $Q_image->fetchRow() )
		{
			if( valid_image( "pr_image{$N}_normal", $row ) )
			{
				$srcPath = expandPath( "$image_dir/".$row["pr_image{$N}_normal"] );
				$img = new image($srcPath);
			}
			else
				if( valid_image( "pr_image2_normal", $row ) )
				{
					$srcPath = expandPath( "$image_dir/".$row["pr_image2_normal"] );
					$img = new image($srcPath);
				}
				else
					if( valid_image( "pr_image1_thumb", $row ) )
					{
						$srcPath = expandPath( "$image_dir/".$row["pr_image1_thumb"] );
						$img = new image($srcPath);
					}
					else
					{
						$srcPath = expandPath( "$image_dir/coming_soon.png" );
						$img = new image($srcPath);
					}

			// lets be a bit smarter here.

			if( ( $img->getHeight() > 300 )
			 || ( $img->getWidth() > 300 ) )
				$img->addGeometryCommand( "300x300" );

			if( $row['pr_add_watermark'] > 0 )
				if( $Qw = getRow( "select lg_watermark from languages where lg_id = {$cfg['currentLanguage']}" ) )
					if( strlen( $Qw['lg_watermark'] ) )
						$img->addWatermark( $Qw['lg_watermark'] );

			$img->applyCommands();
		}
	}

	// show image in product detail page
	if( strlen( $this->ATTRIBUTES["Product"] ) )
	{
		$pr_id = (int) $this->ATTRIBUTES["Product"];
		$N = (int) $this->ATTRIBUTES["N"];
		$Q_image = query( "select pr_image1_thumb, pr_image{$N}_normal, pr_add_watermark from shopsystem_products where pr_id = $pr_id" );
		if( $row = $Q_image->fetchRow() )
		{
			if( valid_image( "pr_image{$N}_normal", $row ) )
			{
				$srcPath = expandPath( "$image_dir/".$row["pr_image{$N}_normal"] );
				$img = new image($srcPath);
			}
			else
				if( valid_image( "pr_image2_normal", $row ) )
				{
					$srcPath = expandPath( "$image_dir/".$row["pr_image2_normal"] );
					$img = new image($srcPath);
				}
				else
					if( valid_image( "pr_image1_thumb", $row ) )
					{
						$srcPath = expandPath( "$image_dir/".$row["pr_image1_thumb"] );
						$img = new image($srcPath);
					}
					else
					{
						$srcPath = expandPath( "$image_dir/coming_soon.png" );
						$img = new image($srcPath);
					}
					
		/*
			if( $img->getWidth()*3 < $img->getHeight() )
			{
				// will rotate
				if( $img->getHeight() > 300 )
					$img->addGeometryCommand( "100x300" );
				$img->addRotateCommand( "-90" );
			}
			else
			{
				// not rotate
				if( $img->getWidth() > 300 )
					$img->addGeometryCommand( "100x300" );
			}
		*/
			if( $row['pr_add_watermark'] > 0 )
			{
				if( $Qw = getRow( "select lg_watermark from languages where lg_id = {$cfg['currentLanguage']}" ) )
					if( strlen( $Qw['lg_watermark'] ) )
						$img->addWatermark( $Qw['lg_watermark'] );
//					else
//						ss_log_message( "No watermark for language {$cfg['currentLanguage']}" );
			}
			$img->applyCommands();
		}
	}

	// show thumbnail in multiple product pages
	if( strlen( $this->ATTRIBUTES["ProductThumb"] ) )
	{
		$pr_id = (int) $this->ATTRIBUTES["ProductThumb"];
		$Q_image = query( "select pr_image1_thumb, pr_image1_normal, pr_add_watermark, pr_ve_id from shopsystem_products where pr_id = $pr_id" );
		if( $row = $Q_image->fetchRow() )
		{
			if( valid_image( "pr_image1_thumb", $row ) )
			{
				$srcPath = expandPath( "$image_dir/".$row["pr_image1_thumb"] );
				$img = new image($srcPath);
			}
			else
				if( valid_image( "pr_image1_normal", $row ) )
				{
					$srcPath = expandPath( "$image_dir/".$row["pr_image1_normal"] );
					$img = new image($srcPath);
				}
				else
					if( valid_image( "pr_image2_normal", $row ) )
					{
						$srcPath = expandPath( "$image_dir/".$row["pr_image2_normal"] );
						$img = new image($srcPath);
					}
					else
					{
						$srcPath = expandPath( "$image_dir/coming_soon.png" );
						$img = new image($srcPath);
					}

			if( $row['pr_add_watermark'] > 0 )
			{
				if( $Qw = getRow( "select lg_watermark from languages where lg_id = {$cfg['currentLanguage']}" ) )
					if( strlen( $Qw['lg_watermark'] ) )
						$img->addWatermark( $Qw['lg_watermark'] );
			}

			if( $img->getHeight() > 300 or $img->getWidth() > 300 )
			{
				$img->addGeometryCommand( "300x300" );
				$img->applyCommands();
			}

			$img->display();
			return;
		}
	}

	if( strlen( $this->ATTRIBUTES["Category"] ) )
	{
		$ca_id = (int) $this->ATTRIBUTES["Category"];
		$Q_image = query( "select ca_image from shopsystem_categories where ca_id = $ca_id" );
		if( $row = $Q_image->fetchRow() )
		{
			$srcPath = expandPath( "Custom/ContentStore/Assets/5/14/CategoryImages/".$row['ca_image'] );
			$img = new image($srcPath);
		}
	}

	if( strlen( $this->ATTRIBUTES["Flag"] ) )
	{
		$cn = safe( substr( $this->ATTRIBUTES["Flag"], 0, 2 ) );

		$srcPath = expandPath( "Custom/ContentStore/Layouts/{$cfg['currentSiteFolder']}Images/$cn.png" );
		ss_log_message( $srcPath );
		$img = new image($srcPath);
	}

	if( $img )
	{
		if( strlen( $this->ATTRIBUTES['MaxHeight'] ) || strlen( $this->ATTRIBUTES['MaxWidth'] ) )
		{
			if( !strlen( $this->ATTRIBUTES['MaxHeight'] ) )
				$this->ATTRIBUTES['MaxHeight'] = 999;

			if( !strlen( $this->ATTRIBUTES['MaxWidth'] ) )
				$this->ATTRIBUTES['MaxWidth'] = 999;

			if (strlen($this->ATTRIBUTES['Rotate']))
				$img->addRotateCommand($this->ATTRIBUTES['Rotate']);
			else
				if( $img->getWidth()*3 < $img->getHeight() )
					$img->addRotateCommand( "-90" );

			$img->addGeometryCommand( $this->ATTRIBUTES['MaxWidth'].'x'.$this->ATTRIBUTES['MaxHeight'] );
			$img->applyCommands();
		}
		else
		{
			if (strlen($this->ATTRIBUTES['Rotate']))
				$img->addRotateCommand($this->ATTRIBUTES['Rotate']);
			if (strlen($this->ATTRIBUTES['Size']))
				$img->addGeometryCommand($this->ATTRIBUTES['Size']);	
			$img->applyCommands();
		}

		//ss_DumpVar($img);
		$img->display();
	}
	else
		ss_log_message( "Image $srcPath missing" );
?>
