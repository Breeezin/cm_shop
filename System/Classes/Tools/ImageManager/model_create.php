<?php

	$this->param("Image1", "");   /* Relative path to image */
    $this->param("Image2", "");
    $this->param("bgSize", "");
    $this->param("smSize", "");
    $this->param("smRotate", "");
    $this->param("smW", "");
    $this->param("smH", "");
    $this->param("Path", "");
    $this->param("Name", "");

    //if we are creating the final card we need it to be 300 dpi
    $needsHeaderUpdate = false;

	require_once('System/Libraries/image/image.php');
    $smSrcPath = expandPath($this->ATTRIBUTES["Image1"]);
    if (file_exists($smSrcPath) and strlen($this->ATTRIBUTES["Image1"])){
        $smImgObject = new image($smSrcPath);
    } else {
        $missing = expandPath('System/Classes/Tools/ImageManager/Images/missing.gif');
        $smImgObject = new image($missing);
    }

    //we want to resize the to fit in the window
    if ($this->ATTRIBUTES["Image2"] == 'Greeting Cards'){
        //determine whether the small image is horizontal or vertical
        $needsHeaderUpdate = true;
        if ($smImgObject->getWidth() > $smImgObject->getHeight()) {
            $this->ATTRIBUTES["Image2"] = 'System/Classes/Tools/ImageManager/Images/Horizontal.jpg';
            $orderNoX = 1500;
            $orderNoY = 2400;
            //will need rotating 180
            $this->ATTRIBUTES['smRotate'] = 180;
        } else {
            $this->ATTRIBUTES["Image2"] = 'System/Classes/Tools/ImageManager/Images/Vertical.jpg';
            $orderNoX = 150;
            $orderNoY = 1600;
            //will need to be placed halfway
            $this->ATTRIBUTES['x'] = 1264;
        }
    } else if ($this->ATTRIBUTES["Image2"] == 'Postcards'){
        $this->ATTRIBUTES["Image2"] = 'System/Classes/Tools/ImageManager/Images/Postcard.jpg';
        $orderNoX = 0;
        $orderNoY = 0;
        $needsHeaderUpdate = true;
        //make it the width of the postcard
        $this->ATTRIBUTES['smSize'] = 1724;
        //will be overlap - put it in the middle
        $this->ATTRIBUTES['y'] = -16;
        if ($smImgObject->getWidth() < $smImgObject->getHeight()) {
            $this->ATTRIBUTES['smRotate'] = 90;
        }
    }

    //size of the window
    $small_width    = $this->ATTRIBUTES['smW'];
    $small_height   = $this->ATTRIBUTES['smH'];

    if (!strlen($this->ATTRIBUTES['smSize']) and strlen($this->ATTRIBUTES['smW'])){
        if ($smImgObject->getWidth() > $smImgObject->getHeight()) {
            //resize to the height
            $ratio = ($small_height/$smImgObject->getHeight());
            $desiredWidth = $smImgObject->getWidth() * $ratio;
            //check this width isn't smaller than the window..
            if ($desiredWidth < $small_width){
                $desiredWidth = $small_width;
            }
            $this->ATTRIBUTES['smSize'] = $desiredWidth;
        } else {
            //resize to the width
            $this->ATTRIBUTES['smSize'] = $small_width;
        }
    }

    //rotate and/or resize the small image before we merge
	if (strlen($this->ATTRIBUTES['smRotate'])) {
		$smImgObject->addRotateCommand($this->ATTRIBUTES['smRotate']);
		if (strlen($this->ATTRIBUTES['smSize'])) {
			$smImgObject->addGeometryCommand($this->ATTRIBUTES['smSize'], '-strip +profile "*" -quality 100 -compress Lossless -density 300x300');
		}
		$smImgObject->applyCommands();
	} else if (strlen($this->ATTRIBUTES["smSize"])) {
        $smImgObject->resize($this->ATTRIBUTES["smSize"], '-strip +profile "*" -quality 100 -compress Lossless -density 300x300');
    }


    if ($smImgObject->srcExt == 'gif'){
        $smallImage    = imageCreateFromGIF($smImgObject->src);
    } else {
        $smallImage    = imageCreateFromJPEG($smImgObject->src);
    }

	$bgSrcPath = expandPath($this->ATTRIBUTES["Image2"]);
    if (file_exists($bgSrcPath) and (filesize($bgSrcPath) > 0)){
        $bgImgObject = new image($bgSrcPath);
    } else {
        $missing = expandPath('System/Classes/Tools/ImageManager/Images/missing.gif');
        $bgImgObject = new image($missing);
    }

    //if we want the image to be saved somewhere else
    //**otherwise it will overwrite the existing - desirable if watermarking?**
    if(strlen($this->ATTRIBUTES['Path'])){
        $name = strlen($this->ATTRIBUTES['Name']) ? $this->ATTRIBUTES['Name']: null;
        $bgImgObject->makeNewTemporary(expandPath($this->ATTRIBUTES['Path']), $name);
    }

    if ($bgImgObject->srcExt == 'gif'){
        $bgImage    = imageCreateFromGIF($bgImgObject->src);
    } else {
        $bgImage    = imageCreateFromJPEG($bgImgObject->src);
    }

    //position of the inner images
    $dest_x     = isset($this->ATTRIBUTES['x']) ? $this->ATTRIBUTES['x'] : 0;
    $dest_y     = isset($this->ATTRIBUTES['y']) ? $this->ATTRIBUTES['y'] : 0;

    //takes part of an image
    if (strlen($this->ATTRIBUTES['smW'])){ //we are putting the image in the window
        $src_x = (imageSX($smallImage)/2) - ($small_width/2);
        $src_y = (imageSY($smallImage)/2) - ($small_height/2);
    } else {
        $small_width    = imageSX($smallImage);
        $small_height   = imageSY($smallImage);
        $src_x = 0;
        $src_y = 0;
    }

    //transparency of the overlying image
    $tranparency = isset($this->ATTRIBUTES['Tranparency']) ? $this->ATTRIBUTES['Tranparency'] : 100;

    imageCopyMerge($bgImage, $smallImage,$dest_x,$dest_y,$src_x,$src_y,$small_width,$small_height,$tranparency);

    //add the order number if there is one but not to the postcards..
    if (isset($this->ATTRIBUTES['OrderNo']) and ($this->ATTRIBUTES["Image2"] !== 'System/Classes/Tools/ImageManager/Images/Postcard.jpg')){
        $textcolor = imagecolorallocate($bgImage, 255, 0, 0);
        // write the string in red
        imagestring($bgImage, 5, $orderNoX, $orderNoY, $this->ATTRIBUTES['OrderNo'], $textcolor);
    }
    $this->display->layout = "None";
    imagejpeg ($bgImage, $bgImgObject->src, 99);
    imagedestroy($bgImage);
    imagedestroy($smallImage);

    //now resize the whole thing for the size we want on this page
    if (strlen($this->ATTRIBUTES['bgSize'])) {
        $bgImgObject->resize($this->ATTRIBUTES['bgSize'], '-quality 100 -compress Lossless -density 300x300');
	}
    if ($needsHeaderUpdate or isset($this->ATTRIBUTES['TestRun'])){
        //it's a final card so we just want to modify the headers to 300dpi
        //and return the path to the new image
        $bgImgObject->updateHeaders('-strip +profile "*" -quality 99 -compress Lossless -density 300x300');
        $path = ss_withoutPreceedingSlash(str_replace(getcwd(), '', $bgImgObject->src));
        print $path;
    } else {
        $bgImgObject->display();
    }
?>
