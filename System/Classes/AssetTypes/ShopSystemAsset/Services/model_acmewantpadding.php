<?
	$errors = '';
	if (array_key_exists('submit',$this->atts)) {
		if (!array_key_exists('Type',$this->atts) or strlen($this->atts['Type']) == 0) {
			$errors = '<strong>Please make a selection!</strong>';
		} else {
			location($this->atts['BackURL'].'&AcmePadding='.ss_URLEncodedFormat($this->atts['Type']));
		}
	}
?>