<?php if (strlen($data['Tax'])) { ?>
	<?php if ($data['Type'] == 'standard') { ?>
	<span class="onlineShop_taxNote">
        <?php if (ss_optionExists('Shop Tax Excluded')) { ?>
            Price excludes <?php print(ss_HTMLEditFormat($data['Tax'])); ?> for shoppers from <?php print(ss_HTMLEditFormat($data['TaxCountry'])); ?>
        <?php } else { ?>
            Price includes <?php print(ss_HTMLEditFormat($data['Tax'])); ?> for shoppers from <?php print(ss_HTMLEditFormat($data['TaxCountry'])); ?>
		    (<a class="onlineShop_taxNoteLink" href="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/ChangeTaxCountry?BackURL=<?php print(ss_URLEncodedFormat($data['BackURL'])); ?>">change country</a>).
        <?php } ?>
	</span>
	<?php } ?>
	<?php if ($data['Type'] == 'basketWithInputs') { ?>
		<select name="CountryThreeCode" onchange="this.form.submit();">
		<?php $tmpl_loop_rows = $data['Q_Countries']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Countries']->fetchRow()) { $tmpl_loop_counter++; ?>
			<option value="<?php print(ss_HTMLEditFormat($row['cn_three_code'])); ?>" <?php if ($data['CurrentTaxCountry'] == $row['cn_three_code']) { ?>selected="selected"<?php } ?>><?php print($row['cn_name']); ?></option>
		<?php } ?>
	</select>~<strong><?php print(ss_HTMLEditFormat($data['Tax'])); ?><?php print(ss_HTMLEditFormat($data['TaxIncluded'])); ?></strong>
	<?php } ?>
	<?php if ($data['Type'] == 'basketNoInputs') { ?>
		N/A~<strong><?php print(ss_HTMLEditFormat($data['Tax'])); ?><?php print(ss_HTMLEditFormat($data['TaxIncluded'])); ?></strong>
	<?php } ?>
<?php } else { ?>
	<?php if ($data['Type'] == 'standard') { ?>
	<span class="onlineShop_taxNote">
		No tax is charged for shoppers from <?php print(ss_HTMLEditFormat($data['TaxCountry'])); ?> 
		(<a class="onlineShop_taxNoteLink" href="<?php print(ss_HTMLEditFormat($data['AssetPath'])); ?>/Service/ChangeTaxCountry?BackURL=<?php print(ss_URLEncodedFormat($data['BackURL'])); ?>">change country</a>).
	</span>
	<?php } ?>
	<?php if ($data['Type'] == 'basketWithInputs') { ?>
		<select name="CountryThreeCode" onchange="this.form.submit();">
		<?php $tmpl_loop_rows = $data['Q_Countries']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Countries']->fetchRow()) { $tmpl_loop_counter++; ?>
			<option value="<?php print(ss_HTMLEditFormat($row['cn_three_code'])); ?>" <?php if ($data['CurrentTaxCountry'] == $row['cn_three_code']) { ?>selected="selected"<?php } ?>><?php print($row['cn_name']); ?></option>
		<?php } ?>
	</select>~N/A
	<?php } ?>
	<?php if ($data['Type'] == 'basketNoInputs') { ?>
		N/A~N/A
	<?php } ?>
<?php } ?>
