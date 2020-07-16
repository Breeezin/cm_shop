<?
    //clear the special sessions and reload
    $_SESSION['SpecialProducts'] = array();
    $_SESSION['SpecialCats'] = array();

    $Q_SpecialProducts = query("
        SELECT * FROM shopsystem_products, ShopSystem_SpecialCodes
        WHERE PrSpecialCode IS NOT NULL
        AND PrSpecialCode = SpCoID
    ");

    if ($Q_SpecialProducts->numRows()) {
        while ($row = $Q_SpecialProducts->fetchRow()) {
            $_SESSION['SpecialProducts']{$row['pr_id']}    =   array(
                    'pr_name'        =>  $row['pr_name'],
                    //'SpCoID'        =>  $row['SpCoID'],
                    //'SpCoType'      =>  $row['SpCoType'],
                    //'SpCoProductNo' =>  $row['SpCoProductNo'],
                    //'SpCoValue'     =>  $row['SpCoValue'],
                    'SpCoMessage'   =>  $row['SpCoMessage']
        	);
        }
    }

    $Q_SpecialCats = query("
        SELECT * FROM shopsystem_categories, ShopSystem_SpecialCodes
        WHERE CaSpecialCode IS NOT NULL
        AND CaSpecialCode = SpCoID
    ");

    if ($Q_SpecialCats->numRows()) {
        while ($row = $Q_SpecialCats->fetchRow()) {
            $_SESSION['SpecialCats']{$row['ca_id']}    =   array(
                    'ca_name'        =>  $row['ca_name'],
                    'SpCoID'        =>  $row['SpCoID'],
                    //'SpCoType'      =>  $row['SpCoType'],
                    //'SpCoProductNo' =>  $row['SpCoProductNo'],
                    //'SpCoValue'     =>  $row['SpCoValue'],
                    'SpCoMessage'   =>  $row['SpCoMessage']
        	);

            //we want the special to apply to all subcategories, so get these too
            if (strlen($row['ca_id'])) {
                $whereSQL = " AND ca_parent_ca_id IN ({$row['ca_id']})";
            } else {
                $whereSQL = " AND ca_id IS NULL";
            }
            $Q_SpecialSubCats = query("SELECT ca_id, ca_name FROM shopsystem_categories
                WHERE 1 = 1
                $whereSQL
            ");

            if ($Q_SpecialSubCats->numRows()) {
                while ($subRow = $Q_SpecialSubCats->fetchRow()) {
                    $_SESSION['SpecialCats']{$subRow['ca_id']}    =   array(
                        'ca_name'        =>  $subRow['ca_name'],
                        'ParentCaName'  =>  $row['ca_name'],
                        'SpCoID'        =>  $row['SpCoID'],
                        //'SpCoType'      =>  $row['SpCoType'],
                        //'SpCoProductNo' =>  $row['SpCoProductNo'],
                        //'SpCoValue'     =>  $row['SpCoValue'],
                        'SpCoMessage'   =>  $row['SpCoMessage']
            	    );
                }
            }
        }
    }

    //now update the products in the basket with the category specials
    //had to do this now otherwise it wasn't working for the first product
    if (isset($_SESSION['Shop']['Basket']['Products'])){
        //there are products in the basket
        $entry = $_SESSION['Shop']['Basket']['Products'];
        $productNumber = count($entry);

        for	($index=0;$index<$productNumber;$index++) {
            $ca_id = $entry[$index]['Product']['pr_ca_id'];
            if (array_key_exists($ca_id, $_SESSION['SpecialCats'])) {
                //this product has category special
                $SpCoID = $_SESSION['SpecialCats'][$ca_id]['SpCoID'];
                //give the product a cat special code - incase its a prod special too
                $entry[$index]['Product']['CaSpecialCode'] = $SpCoID;
            }
        }
    }
    $_SESSION['Shop']['Basket']['Products'] = $entry;
?>
