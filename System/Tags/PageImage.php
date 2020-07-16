<?php

    $id =  new Request("Asset.IDFromPath", array(
        'AssetPath'	=> 'Images Folder/' .$data['this']->title,
    ));
    if ($id->value){
        print "<img alt=\"{$data['this']->title}\" src=\"/index.php?act=Asset.EmbedImage&AssetPath=Images Folder/{$data['this']->title}\">";
    }

?>
