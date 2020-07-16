<?php

	$this->param("Path");
	$current = "";
	$items = ListToArray($this->ATTRIBUTES['Path'],"/");
	foreach ($items as $item) {
		if (!is_dir (expandPath($current.$item.'/'))) {
			mkdir(expandPath($current.$item));			
		}
		$current = $current.$item."/";
	}
?>