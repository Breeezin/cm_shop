<?php
	if (array_key_exists('linkclass',$data)) {
		$linkClass = $data['linkclass'];
	} else {
		$linkClass = 'BreadCrumbs';
	}

	$separator = '';
	$assetPath = '';
	print("<SPAN CLASS=\"{$linkClass}\">");
	foreach(explode('/',$data['this']->assetPath) as $assetName) {
		$assetPath .= '/'.$assetName;
		if (($assetName != NULL) && (strlen($assetName) > 0)) {
			// We want to ignore the first asset: index.php
			print $separator.'<A CLASS="'.$linkClass.'" HREF="'.ss_EscapeAssetPath(ltrim($assetPath,'/')).'">'.$assetName.'</A>';
			if (array_key_exists('separator',$data)) {
				$separator = $data['separator'];
			} else {
				$separator = ' : ';
			}
		}
	}
	if ($data['this']->breadCrumbs !== null) {		
		if (array_key_exists('separator',$data)) {
			$separator = $data['separator'];
		} else {
			$separator = ' : ';
		}
		if (is_array($data['this']->breadCrumbs)) {
			foreach($data['this']->breadCrumbs as $crumb => $link) {
				if (strlen($link)) {
					print $separator.'<A CLASS="'.$linkClass.'" HREF="'.$link.'">'.$crumb.'</A>';
				} else {
					print $separator.$crumb;
				}
			}
		} else {
			print $separator.$data['this']->breadCrumbs;
		}
			
	}
	print("</SPAN>");
?>