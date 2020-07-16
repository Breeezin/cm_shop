<?
	$this->param("Type");
	switch ($this->ATTRIBUTES['Type']) {
		case "Pages":
		case "Referrals" :	
			$Q_Reset = query("
				TRUNCATE TABLE statistics
			");
			
			$Q_CheckTable = query("SELECT * FROM statistics");
			
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM statistics");
			}
			
			$Q_CheckTable = query("SELECT * FROM statistics");
			
			
			if (!$Q_CheckTable->numRows()) {
				$Q_AlterTable = query("
					ALTER TABLE statistics AUTO_INCREMENT = 1  
				");
			}
			break;
						
		case "RandomImages" :
			$Q_Reset = query("
				TRUNCATE TABLE random_images_statistics
			");
				
			$Q_CheckTable = query("SELECT * FROM random_images_statistics");
				
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM random_images_statistics");
			}
				
			$Q_CheckTable = query("SELECT * FROM random_images_statistics");
				
				
			if (!$Q_CheckTable->numRows()) {
				$Q_AlterTable = query("
					ALTER TABLE random_images_statistics AUTO_INCREMENT = 1  
				");
			}
			break;
		case "RandomImagesDisplay" :
			$Q_Reset = query("
				TRUNCATE TABLE random_images_display_statistics
			");
				
			$Q_CheckTable = query("SELECT * FROM random_images_display_statistics");
				
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM random_images_display_statistics");
			}
						
			break;	
		case "ShopViews" :
			$Q_Reset = query("
				TRUNCATE TABLE shopsystem_statistics
			");
				
			$Q_CheckTable = query("SELECT * FROM shopsystem_statistics");
				
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM shopsystem_statistics");
			}
				
			$Q_CheckTable = query("SELECT * FROM shopsystem_statistics");
				
				
			if (!$Q_CheckTable->numRows()) {
				$Q_AlterTable = query("
					ALTER TABLE shopsystem_statistics AUTO_INCREMENT = 1  
				");
			}
			break;	
		case "ShopOrders" :
			$Q_Reset = query("
				TRUNCATE TABLE shopsystem_order_products
			");
				
			$Q_CheckTable = query("SELECT * FROM shopsystem_order_products");
				
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM shopsystem_order_products");
			}					
			break;	
		case "Members" :	
			$Q_Reset = query("
				TRUNCATE TABLE login_statistics
			");
			
			$Q_CheckTable = query("SELECT * FROM login_statistics");
			
			if ($Q_CheckTable->numRows()) {
				$Q_Delete = query("DELETE FROM login_statistics");
			}
								
			break;
		default:	
			$rootFolder = str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']);
			$customFolder = $rootFolder.'Custom/Classes/statistics';
						
			$name = 'model_'.strtolower($this->ATTRIBUTES['Type']).'Reset.php';
			if (file_exists($customFolder.'/'.$name)) {
				include($customFolder."/".$name);
			}									
			break;						
				
	}
	

	
	locationRelative("index.php?act=statistics.Display");
	
?>