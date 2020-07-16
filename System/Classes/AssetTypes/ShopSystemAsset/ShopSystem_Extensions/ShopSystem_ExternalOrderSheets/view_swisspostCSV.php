<?php

$filename = 'Swisspost-'.safe($this->ATTRIBUTES['ors_id']).'.csv';

header( "Content-type: application/csv;" );
header("Content-Disposition: attachment; filename=$filename");


echo "ADDITIONALSERVICE;PRINT PRIORITY LOGO;BARCODE;SENDER NAME 1;SENDER NAME 2;SENDER NAME 3;SENDER ADDRESS 1;SENDER ADDRESS 2;SENDER ADDRESS 3;SENDER POSTCODE;SENDER CITY;SENDER COUNTRY;SENDER CONTACT PERSON;SENDER TELEPHONE;SENDER EMAIL;SENDER VAT NO.;SENDER TAX NO.;RECEIVER NAME 1;RECEIVER NAME 2;RECEIVER NAME 3;RECEIVER ADDRESS 1;RECEIVER ADDRESS 2;RECEIVER ADDRESS 3;RECEIVER POSTCODE;RECEIVER CITY;RECEIVER COUNTRY;RECEIVER CONTACT PERSON;RECEIVER TELEPHONE;RECEIVER EMAIL;RECEIVER VAT NO.;RECEIVER TAX NO.;NATURE OF CONTENT;CUSTOMER TESTIMONIALS;GOODS CURRENCY;ARTICLE 1 DESCRIPTION;ARTICLE 1 QTY;ARTICLE 1 VALUE;ARTICLE 1 ORIGIN;ARTICLE 1 WEIGHT;ARTICLE 1 CUSTOMS TARIFF NO.;ARTICLE 1 INVOICE DESCRIPTION;ARTICLE 1 KEY;ARTICLE 1 EXPORT LICENCE NO.;ARTICLE 1 EXPORT LICENCE DATE;ARTICLE 1 MOVEMENT CERTIFICATE;ARTICLE 1 MOVEMENT CERTIFICATE NO.\n";

//0;0;;1.2;Hans Muster;;;Musterstrasse 12;;;3000;Bern;CH;;41218085617;info@mycompany.com;;;Franz Sonderm�ller;;;Hardstrasse 12;Block 12a;;20095;Hamburg;DE;;49123999745;mueller@yahoo.de;987456;654897;2;customs;3;1;20;1;1;CHF;Chocolate;5;10;CH;0.5;2204.1111;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
//0;0;;2;Hans Muster;;;Musterstrasse 12;;;3000;Bern;CH;;4121808617;info@mycompany.com;;;Hiroko Katsuno;;;4-6-25, shinishikawa;aoba-ku;;225-0003;Yokohama-shi, kanagawa-ken ;JP;;459159661;receiver@customer.com;955678;633123;3;customs;3;1;30;1;1;EUR;Pen;4;8;CH;0.2;2204.2129;;;;;;;Calculator;1;24;CH;0.6;2204.2139;;;;;;;;;;;;;;;;;;
//0;1;;1.2;Hans Muster;;;Musterstrasse 12;;;3000;Bern;CH;;41218085617;info@mycompany.com;;;Franz Sonderm�ller;;;Hardstrasse 12;Block 12a;;20095;Hamburg;DE;;49123999745;mueller@yahoo.de;987456;654897;2;customs;3;1;20;1;1;CHF;Chocolate;5;10;CH;0.5;2204.1111;;208;;15.05.2011;certificate;115;;;;;;;;;;;;;;;;;;;;;;;;
//0;1;;2;Hans Muster;;;Musterstrasse 12;;;3000;Bern;CH;;4121808617;info@mycompany.com;;;Hiroko Katsuno;;;4-6-25, shinishikawa;aoba-ku;;225-0003;Yokohama-shi, kanagawa-ken ;JP;;459159661;receiver@customer.com;955678;633123;3;customs;3;1;30;1;1;EUR;Pen;4;8;CH;0.2;2204.2129;;;;;;;Calculator;1;24;CH;0.6;2204.2139;;;;;;;;;;;;;;;;;;

/*
When i print my label every day, i need it like that with in : detail description "llamas" ,weight empty,value empty and custums tarif number 2402.1000.
P.P CH-1211 Genève 5 Priority (in the right corner)


A0271
A0272
A0273
*/

function cell( $str = '' )
{
	$out = '';
	$str = trim( $str );
	for( $i = 0; $i < strlen( $str ); $i++ )
		if( $str[$i] == ';' )
			$out .= ',';
		else
			$out .= $str[$i];

	//echo '"'.$string.'";';
	echo $out.';';
}


while( $lr = $Q_Orders->fetchRow())
	{
		$alloc = getRow( "select * from post_label_allocation where pl_sheet_id = ".safe($this->ATTRIBUTES['ors_id'])." and pl_or_id = ".$lr['orsi_or_id'] );
		$line = getRow( "select * from shopsystem_orders where or_id = ".$lr['orsi_or_id'] );
		$dest = unserialize( $line['or_shipping_details']);

		$state_country = $dest['ShippingDetails']['0_50A4'];
		$pos = strpos( $state_country, "<BR>" );
		if( $pos !== FALSE )
		{
			$state = substr( $state_country, 0, $pos );
			$country = substr( $state_country, $pos + 4 );
		}
		else
		{
			$state = $state_country;
			$country = $state_country;
		}
		if( !($cn_two_code = getField( "select cn_two_code from countries where cn_name = '$country'") ) )
			ss_log_message( "Unable to select country $country" );

		// convert BTC to USD

		if( $alloc['pl_currency'] == 'BTC' )
		{
			$alloc['pl_currency'] = 'USD';
			$alloc['pl_declared_value'] *= ss_getExchangeRate( 'BTC', 'USD' );
		}

		for( $i = 0; $i < $alloc['pl_num_packages']; $i++ )
		{
			//ADDITIONALSERVICE Value-added service 0 = Without tracking 1 = Registered mail 2 = PRIORITY Plus/E-Tracking Plus 3 = RFID 1 R
			cell( '0' );
			//PRINT PRIORITY LOGO 0 = No PRIORITY overprint 1 = PRIORITY overprint 1 R
			cell( '0' );
			//BARCODE Enter a barcode (only for RFID consignments) 13 O
			cell( );
			//SENDER NAME 1 Sender name 1 30 R
			cell( 'Lyonnel Consulting AG' );
			//SENDER NAME 2 Sender name 2 30 O
			cell( );
			//SENDER NAME 3 Sender name 3 30 O
			cell( );
			//SENDER ADDRESS 1 Sender name 3 (is transmitted electronically but not visible on the label) 30 O
			cell( 'Neuhofstrasse 5A' );
			//SENDER ADDRESS 2 Sender name 3 (is transmitted electronically but not visible on the label) 30 O
			cell( );
			//SENDER ADDRESS 3 Sender name 3 (is transmitted electronically but not visible on the label) 30 O
			cell( );
			//SENDER POSTCODE Sender postcode 12 R
			cell( '6340' );;
			//SENDER CITY Sender city 30 R
			cell( 'Baar' );
			//SENDER COUNTRY ISO code for sender country = CH 2 R
			cell( 'CH' );
			//SENDER CONTACT PERSON Sender contact person 30 O
			cell( 'Fred Blauth' );
			//SENDER TELEPHONE Sender telephone number 20 R
			cell( '+41 22 534 97 35' );
			//SENDER EMAIL Sender e-mail address 50 R
			cell( 'acmecustomerservice@gmail.com' );
			//SENDER VAT NO. Sender VAT number 20 O
			cell( );
			//SENDER TAX NO. Sender VAT number 20 O
			cell( );
			//RECEIVER NAME 1 Recipient name 1 30 R
			cell( $dest['ShippingDetails']['first_name'] );
			//RECEIVER NAME 2 Recipient name 2 30 O
			cell( $dest['ShippingDetails']['last_name'] );
			//RECEIVER NAME 3 Recipient name 3 (is transmitted electronically but not visible on the label) 30 O
			cell( );
			//RECEIVER ADDRESS 1 Recipient address 1 30 R
			cell( substr( $dest['ShippingDetails']['0_50A1'], 0, 30 ) );
			//RECEIVER ADDRESS 2 Recipient address 2 30 O
			cell( $state );
			//RECEIVER ADDRESS 3 Recipient address 3 (is transmitted electronically but not visible on the label) 30 O
			cell( );
			//RECEIVER POSTCODE Recipient postcode 12 O
			cell( $dest['ShippingDetails']['0_B4C0'] );
			//RECEIVER CITY Recipient city 30 R
			cell( $dest['ShippingDetails']['0_50A2'] );
			//RECEIVER COUNTRY ISO code for the recipient country 2 R
			cell( $cn_two_code );
			//RECEIVER CONTACT PERSON Recipient contact person 30 O
			cell( );
			//RECEIVER TELEPHONE Recipient telephone number (numeric characters only, no special characters or spaces) 20 R/O
			cell( $dest['ShippingDetails']['0_B4C1'] );
			//RECEIVER EMAIL Recipient e-mail address 50 R/O 24
			cell( $line['or_purchaser_email'] );
			//RECEIVER VAT NO. Recipient VAT number 20 O
			cell( );
			//RECEIVER TAX NO. Recipient tax reference number 1 R
			cell( '1' );
			//NATURE OF CONTENT Goods content 1 R Goods content 1 = Documents 2 = Goods 3 = Gift 4 = Sample 5 = Returned goods 6 = Other
			cell( '2' );
			//CUSTOMER TESTIMONIALS Customer testimonials 25 O
			cell( );
			//GOODS CURRENCY Goods currency (all items must be declared in the same currency) CHF = Swiss franc EUR = Euro USD = US dollar GBP = British pound 3 R
			cell( $alloc['pl_currency'] );
			//ARTICLE 1 DESCRIPTION Article description Article 1 30 R
			cell( 'llamas' );
			//ARTICLE 1 QTY Article 1 quantity 5 R
			cell( '1' );
			//ARTICLE 1 VALUE Article 1 value 8 (max.  99999.99) R
			cell( $alloc['pl_declared_value'] );
			//ARTICLE 1 ORIGIN Article 1 origin (ISO code) 2 R
			cell( 'CH' );
			//ARTICLE 1 WEIGHT Article 1 weight 4 (max. 2.00) R
			cell( '1' );
			//ARTICLE 1 CUSTOMS TARIFF NO. Customs tariff number 9 O
			cell( '2402.1000' );
			//ARTICLE 1 INVOICE DESCRIPTION Article description for commercial invoice 100 O
			cell( );
			//ARTICLE 1 KEY Key 3 O
			cell( );
			//ARTICLE 1 EXPORT LICENCE NO. Export licence 22 O
			cell( $alloc['pl_license'] );
			//ARTICLE 1 EXPORT LICENCE DATE Export licence date 10 (dd.mm.yyyy) O
			cell( '25.07.2019' );
			//ARTICLE 1 MOVEMENT CERTIFICATE Goods certificate 20 O
			cell( );
			//ARTICLE 1 MOVEMENT CERTIFICATE NO. Goods certificate number
			cell( );


	//		cell( "\"".$state."\",";
			//echo $dest['ShippingDetails']['Email'];
			echo "\n";
		}

	}


die;

?>
