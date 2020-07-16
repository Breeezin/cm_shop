

<form action="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/Engine" method="POST">
<h2 class="in-content-header">Search Products</h2>
<div class="IntroText">
   	 You may also search for products by category by selecting
   		a category from the dropdown below, and clicking go, or search for products
   		by entering
   		a	keyword in the &quot;Keyword&quot; input field below.
   		</div>
		<div class="shop-search-box">
  <table border="0" cellpadding="5" cellspacing="0" >
  	<tr>
  		<td class="form-label">Category:</td>
  		<td align="left">
  			<select name="pr_ca_id" class="EmailFormField">
  				<option value="">All Categories</option>
  				<?php foreach($data['CategoriesArray'] as $data['category'] => $data['id']) { ?>
  				<option value="<?php print(ss_HTMLEditFormat($data['id'])); ?>"><?php print(ss_HTMLEditFormat($data['category'])); ?></option>
  				<?php } ?>
		 </select>
	  </td>
	  <td></td>
	  </tr>
	  <tr>
  		<td align="left" class="form-label">Keywords:</td>
	    <td align="left"><input name="Keywords" type="text" class="EmailFormField" value="" size="40"></td>
	    <td align="left"><input type="IMAGE" name="Go2" src="Images/but-go.gif" class="input-button"></td>
     </tr>
  	</table>
</form>
</div>
<div align="center">
<table border="0" cellpadding="5" cellspacing="0" class="category-list">
 
 	<tr>
	<td colspan='4' align='center'><h1>Chilean Llama Brands</h1></td>
 	</tr>
	<?php
			global $cfg;

			$restrictedCategoriesSQL = 'AND 1=1';
			if (ss_optionExists('Shop Category Restricted')) {
				if (array_key_exists('CanViewCategory',$_SESSION) and count($_SESSION['CanViewCategory'])) {
					$allowedRestrictedCategories = ArrayToList($_SESSION['CanViewCategory']);
					$restrictedCategoriesSQL = " AND (ca_password IS NULL OR ca_id IN ($allowedRestrictedCategories))";
				} else {
					$restrictedCategoriesSQL = ' AND (ca_password IS NULL)';
				}
			}
			$data['Q_MainCats'] = query("
			SELECT * FROM shopsystem_categories
			  join site_category_mask on scm_ca_id = ca_id and scm_lg_id = {$cfg['currentLanguage']} and scm_ca_active = 1
			WHERE ca_as_id = {$data['as_id']}
				AND ca_parent_ca_id IS NULL
				AND ca_origin_cn_id = 192
				$restrictedCategoriesSQL
			ORDER BY ca_sort_order, ca_name
		");
		$wantLine = false;
		$data['nextRowHTML'] = '';
		$counter = 0;
	?>
	<?php $tmpl_loop_rows = $data['Q_MainCats']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_MainCats']->fetchRow()) { $tmpl_loop_counter++; ?>
		<?php if ($counter % 4 == 0) { ?>
			<tr>	
		<?php } ?>
	
		<td align="center"><?php if (strlen($row['ca_image'])) { ?><a href="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/Engine/pr_ca_id/<?php print(ss_HTMLEditFormat($row['ca_id'])); ?>"><img src="<?=ss_storeForAsset($data['as_id'])?>CategoryImages/<?php print(ss_HTMLEditFormat($row['ca_image'])); ?>" alt="<?php print(ss_HTMLEditFormat($row['ca_name'])); ?>" border="0" width="60"></a><?php } ?></td>
		<?php
			$data['nextRowHTML'] .= '<td align="center"><a href="'.ss_HTMLEditFormat($data['AssetPath']).'/Service/Engine/pr_ca_id/'.$row['ca_id'].'">'.ss_HTMLEditFormat($row['ca_name'])."</a><br /><br /></td>";
		?>
		
		<?php if ($counter % 4 == 3) { ?>
	</tr>	
			<tr align="center" valign="middle">
				<?php print($data['nextRowHTML']); ?>
				<?php $data['nextRowHTML'] = ''; ?>
			</tr>
		<?php } ?>
		<?php $counter++; ?>
   	<?php } ?>

	<?php if ($counter % 4 != 0) { ?>
		</tr>	
		<tr align="center" valign="middle">
			<?php print($data['nextRowHTML']); ?>
		</tr>
	<?php } ?>
 
 	<tr>
	<td colspan='4' align='center'><h1>Non Chilean Llama Brands</h1></td>
 	</tr>
	<?php
			global $cfg;
			$restrictedCategoriesSQL = 'AND 1=1';
			if (ss_optionExists('Shop Category Restricted')) {
				if (array_key_exists('CanViewCategory',$_SESSION) and count($_SESSION['CanViewCategory'])) {
					$allowedRestrictedCategories = ArrayToList($_SESSION['CanViewCategory']);
					$restrictedCategoriesSQL = " AND (ca_password IS NULL OR ca_id IN ($allowedRestrictedCategories))";
				} else {
					$restrictedCategoriesSQL = ' AND (ca_password IS NULL)';
				}
			}
			$data['Q_MainCats'] = query("
			SELECT * FROM shopsystem_categories
			  join site_category_mask on scm_ca_id = ca_id and scm_lg_id = {$cfg['currentLanguage']} and scm_ca_active = 1
			WHERE ca_as_id = {$data['as_id']}
				AND ca_parent_ca_id IS NULL
				AND ca_origin_cn_id != 192
				$restrictedCategoriesSQL
			ORDER BY ca_sort_order, ca_name
		");
		$wantLine = false;
		$data['nextRowHTML'] = '';
		$counter = 0;
	?>
	<?php $tmpl_loop_rows = $data['Q_MainCats']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_MainCats']->fetchRow()) { $tmpl_loop_counter++; ?>
		<?php if ($counter % 4 == 0) { ?>
			<tr>	
		<?php } ?>
	
		<td align="center"><?php if (strlen($row['ca_image'])) { ?><a href="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/Engine/pr_ca_id/<?php print(ss_HTMLEditFormat($row['ca_id'])); ?>"><img src="<?=ss_storeForAsset($data['as_id'])?>CategoryImages/<?php print(ss_HTMLEditFormat($row['ca_image'])); ?>" alt="<?php print(ss_HTMLEditFormat($row['ca_name'])); ?>" border="0" width="60"></a><?php } ?></td>
		<?php
			$data['nextRowHTML'] .= '<td align="center"><a href="'.ss_HTMLEditFormat($data['AssetPath']).'/Service/Engine/pr_ca_id/'.$row['ca_id'].'">'.ss_HTMLEditFormat($row['ca_name'])."</a><br /><br /></td>";
		?>
		
		<?php if ($counter % 4 == 3) { ?>
	</tr>	
			<tr align="center" valign="middle">
				<?php print($data['nextRowHTML']); ?>
				<?php $data['nextRowHTML'] = ''; ?>
			</tr>
		<?php } ?>
		<?php $counter++; ?>
   	<?php } ?>

	<?php if ($counter % 4 != 0) { ?>
		</tr>	
		<tr align="center" valign="middle">
			<?php print($data['nextRowHTML']); ?>
		</tr>
	<?php } ?>
	
</table>
</div>
