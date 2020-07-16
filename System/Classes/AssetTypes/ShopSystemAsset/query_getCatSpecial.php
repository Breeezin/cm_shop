<?
//delete this when Briar is sure she is not using it
    //implemented for duty free specials

    $Q_CategorySpecial = getRow("
        SELECT *
        FROM shopsystem_categories
        WHERE ca_id = {$ca_id};
    ");

    $Q_Special = getRow("
        SELECT *
        FROM ShopSystem_SpecialCodes
        WHERE SpCoID = {$Q_CategorySpecial['CaSpecialCode']};
    ");

    //define some nice variables
    $type = $Q_Special['SpCoType'];
    $productNumber = $Q_Special['SpCoProductNo'];
    $valueOfSpecial = $Q_Special['SpCoValue'];

    $extraSpecialPrice = '';
    switch ($type) {
		case 1 :
        //percent discount
        $extraSpecialPrice = 24;
        break;
		case 2 :
        //dollars off
        $extraSpecialPrice = 23;
        break;
		case 3 :
        //new price
        $extraSpecialPrice = 22;
        break;
		case 4 :
        //% off Lowest Item
        $extraSpecialPrice = 21;
        break;
    }

    return $extraSpecialPrice;

?>
