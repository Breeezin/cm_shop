<?php 
	if (array_key_exists('DoAction', $this->ATTRIBUTES)) {
		// validate
		if (!strlen($this->ATTRIBUTES['pr_ca_id'])) {
			array_push($errors, "Minimum Order Total is required field.");
		}
		if (!strlen($this->ATTRIBUTES['Key'])) {
			array_push($errors, "Product is required field.");
		}
		if (!strlen($this->ATTRIBUTES['MinTotal'])) {
			array_push($errors, "Minimum Order Total is required field.");
		} else {						
			$temp = $minTotalField->validate();
			if (strlen($temp))
				array_push($errors,$temp);	
		}
		if (!strlen($this->ATTRIBUTES['DateFrom'])) {
			array_push($errors, "Date From is required field.");
		} else {						
			$temp = $dateFromField->validate();
			if (strlen($temp))
				array_push($errors,$temp);	
		}
		if (!strlen($this->ATTRIBUTES['DateTo'])) {
			array_push($errors, "Date To is required field.");
		} else {			
			$temp = $dateToField->validate();
			if (strlen($temp))
				array_push($errors,$temp);		
		}
		
		// no error?
		if (!count($errors)) {
			
			// cofirmed the winner
			if ($this->ATTRIBUTES['DoAction'] == 'Confirm') {					
				// record new winner for this draw
				$newLottoID = newPrimaryKey('lottery_winners', 'lotw_id');
				$prID = ListFirst($this->ATTRIBUTES['Key'], '_');
				$prOpID = ListLast($this->ATTRIBUTES['Key'], '_');
				$Q_InsertWinner = query("
						INSERT INTO lottery_winners 
						(lotw_id, lotw_or_id, lotw_pr_id, lotw_pro_id, lotw_draw_date)
						VALUES
						($newLottoID, {$this->ATTRIBUTES['Winner']}, $prID, $prOpID, Now())
				");
				
				
				// prepare for recording a news item
				$data = array();
				$data['DrawNumber'] = $newLottoID;
				$data['tr_id'] = $prID;
				// get selected Category Name and product												
				$theProduct = getRow("
					SELECT pr_name, pro_stock_code, ca_name FROM shopsystem_products, shopsystem_product_extended_options, shopsystem_categories
					WHERE pr_id = $prID
						AND pr_id = pro_pr_id	
						AND ca_id = pr_ca_id	
						AND pro_id = $prOpID
																		
				");	
				$data['ProductName'] = $theProduct['pr_name'].' - '.$theProduct['pro_stock_code'];
				$data['ProductLink'] = "Shop_System/Service/Detail/Product/$prID";
				$data['CategoryName'] = $theProduct['ca_name'];
				
				/*
				$headLine = "New Winner For Draw No. $newLottoID";
				
				$body = escape($this->processTemplate('LotteryWinner_NewsBody', $data));
				$plainbody = escape($this->processTemplate('LotteryWinner_NewsPlainBody', $data));
				
				// add news item
				$newNewsID = newPrimaryKey('newsletters', 'na_id');							
				$Q_InsertNewsItem = query("
					Insert INTO newsletters 
					(na_id, na_subject, nl_template,  nl_textmessage, nl_html_message, nl_last_modified)
					VALUES
					($newNewsID, '$headLine', 'Default', '$plainbody', '$body', Now())
				");
				*/
			// action is new draw or redraw	
			} else if ($this->ATTRIBUTES['DoAction'] == 'Draw' or $this->ATTRIBUTES['DoAction'] == 'ReDraw') {
				$whereSQL = '';
				
				if ($this->ATTRIBUTES['DoAction'] == 'ReDraw' and strlen($this->ATTRIBUTES['Winner'])) {
					//exclude the previous order picked up
					$whereSQL = " AND or_id != {$this->ATTRIBUTES['Winner']}";
				}
				
				$Q_RandOrder = getRow("
						SELECT * FROM transactions, shopsystem_orders
			   			WHERE 	1
						AND or_tr_id = tr_id
						AND tr_completed = 1 
						AND tr_total >= {$this->ATTRIBUTES['MinTotal']}
						AND or_deleted = 0
						AND or_paid IS NOT NULL
				 		AND (or_recorded BETWEEN '{$this->ATTRIBUTES['DateFrom']}' AND '{$this->ATTRIBUTES['DateTo']}')
						$whereSQL
			    		ORDER BY RAND(NOW())
						LIMIT 1
				");
				
				locationRelative("index.php?act={$this->ATTRIBUTES['act']}&pr_ca_id={$this->ATTRIBUTES['pr_ca_id']}&Key={$this->ATTRIBUTES['Key']}&Winner={$Q_RandOrder['or_id']}&DoAction=1&DateFrom={$this->ATTRIBUTES['DateFrom']}&DateTo={$this->ATTRIBUTES['DateTo']}&MinTotal={$this->ATTRIBUTES['MinTotal']}");
			}
		}
	}
?>