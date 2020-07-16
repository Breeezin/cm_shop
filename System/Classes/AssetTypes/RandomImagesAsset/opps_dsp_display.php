<?php
	$Q_Images = query("
		SELECT * FROM RandomImages
		WHERE
			( RaImExpiryDate >= Now()
			OR RaImExpiryDate IS NULL )
            and
            RaImAssetLink=".$asset->getID()."
            ORDER BY RAND()
        ");
    if ($row = $Q_Images->fetchRow() ) {
        $width = 150;
        $height = 150;
        $src = $this->imgDir. '/' . $row['RaImImage'];

        $image = '<img alt="'.$row['RaImAlt'].'" src="index.php?act=ImageManager.get&Image='.$src.'&Size='.$width.'x'.$height.'">';
        echo  $image;
    } else
		print "Sorry No Image Defined";


?>
