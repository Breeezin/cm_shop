<?php

header( "Content-type: application/csv;" );

echo 'Order ID,Name First,Name Last,Ship to Address,Ship to City,Ship to State,Ship to Country,Ship to Zip,Phone,Email Address'."\n";

while( $lr = $Q_Orders->fetchRow())
	{
		$line = getRow( "select * from shopsystem_orders where or_id = ".$lr['orsi_or_id'] );
		echo $line['or_id'].",";
		$dest = unserialize( $line['or_shipping_details']);

		$state_country = $dest['ShippingDetails']['0_50A4'];
		$pos = strpos( $state_country, "<BR>" );
		if( $pos )
		{
			$state = substr( $state_country, 0, $pos );
			$country = substr( $state_country, $pos + 4 );
		}
		else
		{
			$state = $state_country;
			$country = $state_country;
		}


		echo "\"".$dest['ShippingDetails']['first_name']."\",";
		echo "\"".$dest['ShippingDetails']['last_name']."\",";
		echo "\"".$dest['ShippingDetails']['0_50A1']."\",";
		echo "\"".$dest['ShippingDetails']['0_50A2']."\",";
		echo "\"".$state."\",";
		echo "\"".$country."\",";
		echo "\"".$dest['ShippingDetails']['0_B4C0']."\",";
		echo "\"".$dest['ShippingDetails']['0_B4C1']."\",";
		//echo $dest['ShippingDetails']['Email'];
		echo "\"".$line['or_purchaser_email']."\"";
		echo "\n";

	}


die;

class PDF extends FPDF
{
	var $position_x;

	function CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text )
	{
		$this->SetFont($font,$attr,$size);
		$this->SetTextColor( $colour_r, $colour_g, $colour_b );
		$w=$this->GetStringWidth($text)+6;
//		ss_log_message ("x:".$this->position_x );
		$this->SetX( $this->position_x );
		$this->Cell($w,$size-2,$text,0,1,$align);
	}

	function CellHeader( $pos_x, $pos_y, $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text )
	{
//		ss_log_message ("x:".$x.", y:".$y );
		$this->position_x = $pos_x;
		$this->SetY($pos_y);
		$this->SetX($this->position_x );
		$this->CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text );
	}

}

$pdf=new PDF( 'P','mm','A4' );

$pdf->SetLeftMargin(1);
$pdf->SetRightMargin(1);
$pdf->SetTopMargin(1);
$pdf->SetAutoPageBreak( true, 1);

$Q_colours = query("select * from colours order by colour_id asc");	
$Q_fonts = query("select * from fonts order by font_id asc");	

$Q_label = query("select * from pdf_printout_attributes order by label_no asc");	
if ($Q_label->numRows() == 0)
{
	echo "No rows defined for label";
	die;
}

while( true )
{
	$Q_label->reset();
	$pdf->AddPage();
	while ($label = $Q_label->fetchRow() )
	{
		if ($lr = $Q_Orders->fetchRow())
		{
			$line = getRow( "select * from shopsystem_orders where or_id = ".$lr['orsi_or_id'] );
			$dest = unserialize( $line['or_shipping_details']);

			$state_country = $dest['ShippingDetails']['0_50A4'];
			$pos = strpos( $state_country, "<BR>" );
			if( $pos )
			{
				$state = substr( $state_country, 0, $pos );
				$country = substr( $state_country, $pos + 4 );
			}
			else
			{
				$state = $state_country;
				$country = $state_country;
			}

			/* key
			0_B4BF		shipping company
			0_50A1		address 1
			0_50A2		city
			0_B4C0		zip
			*/


			$line1 = str_replace( "<BR>", " ", $dest['ShippingDetails']['Name'] );
			$line2 = str_replace( "<BR>", " ", $dest['ShippingDetails']['0_B4BF'] );
			if( strlen( $line2 ) == 0 )
			{
				$line2 = str_replace( "<BR>", " ", $dest['ShippingDetails']['0_50A1'].", ".$dest['ShippingDetails']['0_50A2'] );
				$line3 = str_replace( "<BR>", " ", $state." ".$dest['ShippingDetails']['0_B4C0'] );
				$line4 = $country;
				$line5 = "";
				$line6 = "";

			}
			else
			{
				$line3 = str_replace( "<BR>", " ", $dest['ShippingDetails']['0_50A1'].", ".$dest['ShippingDetails']['0_50A2'] );
				$line4 = str_replace( "<BR>", " ", $state." ".$dest['ShippingDetails']['0_B4C0'] );
				$line5 = $country;
				$line6 = "";
			}

			$colourRow = $Q_colours->getRow($label['addr1_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr1_font_id']);
			$pdf->CellHeader( $label['addr1_x'], $label['addr1_y'],
				$fontRow['font_name'], $label['addr1_font_attr'], $label['addr1_font_size'], 
						$label['addr1_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line1 );

			$colourRow = $Q_colours->getRow($label['addr2_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr2_font_id']);
			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr2_font_attr'], $label['addr2_font_size'], 
						$label['addr2_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line2 );

			$colourRow = $Q_colours->getRow($label['addr3_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr3_font_id']);
			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr3_font_attr'], $label['addr3_font_size'], 
						$label['addr3_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line3 );

			$colourRow = $Q_colours->getRow($label['addr4_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr4_font_id']);
			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr4_font_attr'], $label['addr4_font_size'], 
						$label['addr4_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line4 );


			$colourRow = $Q_colours->getRow($label['addr5_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr5_font_id']);
			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr5_font_attr'], $label['addr5_font_size'], 
						$label['addr5_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line5 );

			$colourRow = $Q_colours->getRow($label['addr6_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr6_font_id']);
			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr2_font_attr'], $label['addr2_font_size'], 
						$label['addr6_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line6 );
		}
		else
		{
			$pdf->Output();
			exit;
		}

	}
}

?>
