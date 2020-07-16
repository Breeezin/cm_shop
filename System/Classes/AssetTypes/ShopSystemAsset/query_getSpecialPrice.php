<?
    //implemented for duty free specials

    $entry = $_SESSION['Shop']['Basket']['Products'];

    //if it is a category special the function will have passed the ca_id
    if (isset($ca_id)){
        $SpCoID = $entry[$thisIndex]['Product']['CaSpecialCode'];
    } //else we just do this for both


    $Q_Special = getRow("
        SELECT *
        FROM ShopSystem_SpecialCodes
        WHERE SpCoID = {$SpCoID};
    ");

    //define some nice variables
    $type = $Q_Special['SpCoType'];
    $requiredAmount = $Q_Special['SpCoProductNo'];
    $valueOfSpecial = $Q_Special['SpCoValue'];
    $price = $entry[$thisIndex]['Product']['pro_price'];
    $productNumber = count($entry);

    //loop thru the basket products and count the number with this special
    $count = 0;

    //calculate the subtotal - basket's ['Subtotal'] isn't updated early enough
    $subTotal = 0;

    //get the subtotal of products and count of this special
    for	($index=0;$index<$productNumber;$index++) {
        if (isset($entry[$index]['Product']['Price'])){
            //the product is specialled, add that price
            $subTotal += $entry[$index]['Product']['Price'] * $entry[$index]['Qty'];
        } else {
            //the product doesn't have a special price
            $subTotal += $entry[$index]['Product']['pro_price'] * $entry[$index]['Qty'];
        }
        //count the product special
        if (isset($entry[$index]['Product']['PrSpecialCode']) ){
    		if ($SpCoID == $entry[$index]['Product']['PrSpecialCode']) {
                $count += $entry[$index]['Qty'];
            }
            //and count the cat special if its not the same
            if (isset($entry[$index]['Product']['CaSpecialCode']) and ($entry[$index]['Product']['CaSpecialCode'] != $entry[$index]['Product']['PrSpecialCode'])){
        		if ($SpCoID == $entry[$index]['Product']['CaSpecialCode']) {
                    $count += $entry[$index]['Qty'];
                }
            }
        } else {
        //else count the category special
            if (isset($entry[$index]['Product']['CaSpecialCode'])){
        		if ($SpCoID == $entry[$index]['Product']['CaSpecialCode']) {
                    $count += $entry[$index]['Qty'];
                }
            }
        }
    }

    $extraSpecialPrice = null;
    switch ($type) { //special types
        case 1 :
            //Buy x products or more for $x ea - regardless of number
            if ($count >= $requiredAmount) { //they have purchased enough of the special
                $extraSpecialPrice = $valueOfSpecial;
            }
            break;
		case 2 :
            //Buy x products or more & get y% off
            if ($count >= $requiredAmount){
                $extraSpecialPrice = $price *(100-$valueOfSpecial)/100;
            }
            break;
		case 3 :
            //spend $x and get product for $y
            if ($subTotal >= $requiredAmount){
                $extraSpecialPrice = $valueOfSpecial;
            }
            break;
		case 4 :
            //Spend $x and get y% off
            if ($subTotal >= $requiredAmount){
                $extraSpecialPrice = ($price *(100-$valueOfSpecial)/100);
            }
            //check that specialling the product doesn't make it less than the required amount
            if (($subTotal - $price + $extraSpecialPrice) < $requiredAmount){
                $extraSpecialPrice = null;
            }
            break;
  		case 5 :
            //Buy x products and get product z for $y
            $product = $Q_Special['SpCoProductZ'];
            if (strlen($product)){
                if ($count >= $requiredAmount) {
                //discount z product if it is in the basket
                    if ($product == $entry[$thisIndex]['Product']['pr_id']){
                        if (($count - $entry[$thisIndex]['Qty']) >= $requiredAmount){
                            //don't want to include this product in the count
                            $extraSpecialPrice = $valueOfSpecial;
                        }
                    }
                }
            }
            break;
  		case 6 :
            //Buy x products and get y% off z product
            $product = $Q_Special['SpCoProductZ'];
            if (strlen($product)){
                if ($count >= $requiredAmount) {
                //discount z product if it is in the basket
                    if ($product == $entry[$thisIndex]['Product']['pr_id']){
                        if (($count - $entry[$thisIndex]['Qty']) >= $requiredAmount){
                            //don't want to include this product in the count
                            $extraSpecialPrice = ($price *(100-$valueOfSpecial)/100);
                        }
                    }
                }
            }
            break;
    }

    //make the session remember the changes..
    $_SESSION['Shop']['Basket']['Products'][$thisIndex] = $entry[$thisIndex];

    return $extraSpecialPrice;

?>
