<div class="price">
	<?php if ($data['RRP'] !== null) { ?>
		<strong>RRP:</strong>
		<span id="RRP<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>"><?php print($data['RRP']); ?></span>
		<?php if ($data['CurrencyConverter']) { ?>
			<br /><span id="RRP<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>approx"><?php print($data['RRPApprox']); ?></span>
		<?php } ?>
		<br />
	<?php } ?>
	<?php if ($data['NormalPrice'] !== null) { ?>
		<strong>Price:</strong>
		<span id="normalPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>">
		<?php if ($data['OnSpecial']) { ?>
			<span class="price-old"><?php print($data['NormalPrice']); ?></span>
    <?php } else { ?>
			<?php print($data['NormalPrice']); ?>
		<?php } ?>
  </span><br />
  <?php if ($data['CurrencyConverter']) { ?>
		<span id="normalPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>approx" >
    <?php if ($data['OnSpecial']) { ?>
			<span class="price-old"><?php print($data['NormalPriceApprox']); ?></span>
			<?php } else { ?>
				<?php print($data['NormalPriceApprox']); ?>
			<?php } ?>
			</span>
		<?php } ?>
		<br />
	<?php } ?>
	<?php if ($data['OnSpecial']) { ?>
		<strong><?php print(ss_HTMLEditFormat($data['SpecialDescription'])); ?></strong>&nbsp;<strong>Price:</strong>
		<span id="specialPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>" class="price-new"><?php print($data['SpecialPrice']); ?></span>
		<?php if ($data['CurrencyConverter']) { ?>
			<span id="specialPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>approx"><?php print($data['SpecialPriceApprox']); ?></span>
		<?php } ?>
		<br />
	<?php } ?>
</div>
