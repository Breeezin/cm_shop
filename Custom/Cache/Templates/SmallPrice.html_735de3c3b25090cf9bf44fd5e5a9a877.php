	<?php if ($data['OnSpecial']) { ?>
		<span>Was:</span>		
		<span id="normalPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>" class="price-old"><?php print($data['NormalPrice']); ?></span>
		<span class="price"><?php print(ss_HTMLEditFormat($data['SpecialDescription'])); ?> Price:</span>
		<span id="specialPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>" class="price"><?php print($data['SpecialPrice']); ?></span>
		&nbsp;<br />
	<?php } else { ?>
		<span class="price">Price:</span>		
		<span id="normalPrice<?php print(ss_HTMLEditFormat($data['pr_id'])); ?>" class="price"><?php print($data['NormalPrice']); ?></span>
		&nbsp;<br />
	<?php } ?>
