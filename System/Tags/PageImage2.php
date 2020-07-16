<?php
    //looks at the id of page
    $id =  new Request("Asset.IDFromPath", array(
        'AssetPath'	=> 'Images Folder/' .$data['this']->assetID,
    ));
    if ($id->value){
        print "<img alt=\"{$data['this']->assetID}\" src=\"/index.php?act=Asset.EmbedImage&AssetPath=Images Folder/{$data['this']->assetID}\">";
    }

?>
