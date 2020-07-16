<?php
	$error = null;

	if (array_key_exists('DoAction',$this->ATTRIBUTES)) {
		
		$this->param('Email');
		$this->param('Rating');

		// First find the user
		$User = getRow("
			SELECT * FROM users 
			WHERE us_email LIKE '".escape($this->ATTRIBUTES['Email'])."'
		");
		
		if ($User !== null) {
			// Now look to see if they purchased the product
			$Q_CheckOrdered = query("
				SELECT * FROM ordered_products, shopsystem_orders
				WHERE or_us_id = ".safe($User['us_id'])."
					AND op_or_id = or_id
					AND op_pr_id = ".safe($this->ATTRIBUTES['Product'])."
			");
			
			if ($Q_CheckOrdered->numRows()) {
				// yep they've purchased.. now check if they rated it before
				$Q_OldRating = query("
					SELECT * FROM shopsystem_product_customer_ratings
					WHERE UserLink = ".safe($User['us_id'])."
						AND ProductLink = ".safe($this->ATTRIBUTES['Product'])."
				");
				if ($Q_OldRating->numRows()) {
					$error = 'Sorry, you may only rate each product once.';
				} else {
				
					if ($this->ATTRIBUTES['Rating'] > 100) $this->ATTRIBUTES['Rating'] = 100;
					if ($this->ATTRIBUTES['Rating'] < 0) $this->ATTRIBUTES['Rating'] = 0;
					
					// now insert the rating
					$Q_InsertRating = query("
						INSERT INTO shopsystem_product_customer_ratings
							(ProductLink, UserLink, Rating)
						VALUES
							(".safe($this->ATTRIBUTES['Product']).", ".safe($User['us_id']).", ".safe($this->ATTRIBUTES['Rating']).")
					");
	
					// get the product ratings from the product
					/*$Average = getRow("
						SELECT AVG(Rating) AS AverageRating, COUNT(ProductLink) AS TheCount 
						FROM shopsystem_product_customer_ratings
						WHERE ProductLink = ".safe($this->ATTRIBUTES['Product'])."
					");*/
					$prod = getRow("
						SELECT pr_customer_rating, pr_customer_rating_count FROM shopsystem_products
						WHERE pr_id = ".safe($this->ATTRIBUTES['Product'])."
					");
	
					$count = 1;
					$totalRating = $this->ATTRIBUTES['Rating'];
					if ($prod['pr_customer_rating'] !== null and $prod['pr_customer_rating_count'] !== null) {
						$count += $prod['pr_customer_rating_count'];
						$totalRating += $prod['pr_customer_rating']*$prod['pr_customer_rating_count'];
					}
					$averageRating = $totalRating / $count;
					
					$Q_InsertRating = query("
						UPDATE shopsystem_products
						SET pr_customer_rating = {$averageRating},
							pr_customer_rating_count = {$count}
						WHERE pr_id = ".safe($this->ATTRIBUTES['Product'])."
					");
				}
								
			} else {
				$error = 'Sorry, only customers	who have purchased this llama may submit a rating.';
			}
			
		} else {
			$error = 'The email address entered could not be found in our customer database';
		}
			
	}

?>
