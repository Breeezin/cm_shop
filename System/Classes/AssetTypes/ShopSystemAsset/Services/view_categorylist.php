<?
	$this->param('ca_id',null);
    $this->param('ThisCat',null);
    $this->param('Settings',array());

	ss_paramKey($this->ATTRIBUTES['Settings'],'JustCategories',false);
	ss_paramKey($this->ATTRIBUTES['Settings'],'Type','Standard');

	$data = array(
		'AssetPath'			=>	ss_EscapeAssetPath(ss_withoutPreceedingSlash($asset->getPath())),
		'as_id'			=>	$asset->getID(),
		'CurrentServer'		=>	$GLOBALS['cfg']['currentServer'],
        'ThisCat'           =>  $this->ATTRIBUTES['ThisCat'],
        'ca_id'				=>	$this->ATTRIBUTES['ca_id'],
		'Settings'	        =>	$this->ATTRIBUTES['Settings'],
	);
	
	// Always link in the shop style sheet
	ss_customStyleSheet($this->styleSheet);
	
	$this->useTemplate('CategoryList',$data);
/*	print("<p>
		The following categories are available. Click the category name you are interested
		in to view the products available in that category.
	</p>");

	function showCategories($data,$parent = null) {
		if ($parent === null) {
			$parentSQL = "IS NULL";	
		} else {
			$parentSQL = "= $parent";	
		}
		$Q_Subs = query("
			SELECT * FROM shopsystem_categories
			WHERE ca_parent_ca_id $parentSQL
				AND ca_as_id = {$data['as_id']}
			ORDER BY ca_sort_order, ca_name
		");	
		if ($Q_Subs->numRows()) {
			print("<ul>");
			while ($cat = $Q_Subs->fetchRow()) {
				$niceCategoryName = ss_alphaNumeric($cat['ca_name'],'_');
				print("<li><a href=\"{$data['AssetPath']}/Service/Engine/pr_ca_id/{$cat['ca_id']}/Category/{$niceCategoryName}.html\">".ss_HTMLEditFormat($cat['ca_name'])."</a></li>");
				showCategories($data,$cat['ca_id']);
			}
			print("</ul>");
		}
	}

	showCategories($data,$this->ATTRIBUTES['ca_id']);	*/
	
?>
