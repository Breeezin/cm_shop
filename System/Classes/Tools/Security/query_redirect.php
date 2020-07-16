<?
    $data['as_id'] = $this->ATTRIBUTES['as_id'];
    $cereal = ss_getAssetLayoutCereal($this->ATTRIBUTES['as_id']);
    $data['Error'] = 'Sorry you do not have permission to access this page.';
    if (isset($cereal['LYT_LAYOUT_SECURITYPAGE'])){
        $data['Error'] = strlen($cereal['LYT_LAYOUT_SECURITYPAGE']) ? $cereal['LYT_LAYOUT_SECURITYPAGE'] : 'Sorry you do not have permission to access this page.';
    }
?>