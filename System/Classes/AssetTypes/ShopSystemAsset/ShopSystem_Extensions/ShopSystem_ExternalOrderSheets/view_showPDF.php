<?php

require('fpdf.php');

define( 'BORDER', 0 );		// debug

class PDF extends FPDF
{
	var $position_x;
	var $max_width;
	var $split;

	function setMaxWidth( $foo )
	{
		ss_log_message( "pdf element width $foo" );
		$this->max_width = $foo;
	}

	function CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text, $nl = 1 )
	{
		$height = $size - 8;
		if( $height <= 0 )
			$height = 1;

		$this->SetFont($font,$attr,$size);
		$this->SetTextColor( $colour_r, $colour_g, $colour_b );

		while( $this->GetStringWidth($text)+6 > $this->max_width )
		{
			$this->split = true;

			$this_text = $text;
			while( $this->GetStringWidth($this_text)+6 > $this->max_width )
			{
				$pos = strrpos( $this_text, ' ' );
				if( $pos > 0 )
					$this_text = substr( $this_text, 0, $pos );
				else
					break;
			}
			$this->SetX( $this->position_x );
			$this->Cell($this->GetStringWidth($this_text)+6,$height,$this_text,BORDER,$nl,$align);		// $size-x line height....
			$text = substr( $text, strlen( $this_text ) );
		}

		ss_log_message ("x:".$this->position_x );
		$this->SetX( $this->position_x );
		$this->Cell($this->GetStringWidth($text)+6,$height,$text,BORDER,$nl,$align);		// $size-x line height....
	}

	function CellHeader( $pos_x, $pos_y, $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text, $nl = 1 )
	{
		$this->split = false;

		ss_log_message ("x:".$pos_x.", y:".$pos_y );
		$this->position_x = $pos_x;
		$this->SetY($pos_y);
		$this->SetX($this->position_x );
		$this->CellNextLine( $font, $attr, $size, $align, $colour_r, $colour_g, $colour_b, $text, $nl );
	}

	function GetSplit( )
	{
		return $this->split;
	}

}

$pdf=new PDF( 'P','mm','A4' );

$pdf->SetLeftMargin(1);
$pdf->SetRightMargin(1);
$pdf->SetTopMargin(1);
$pdf->SetAutoPageBreak( true, 0);

$Q_colours = query("select * from colours order by colour_id asc");	
$Q_fonts = query("select * from fonts order by font_id asc");	

$sql = "select * from pdf_printout_attributes where pa_vendor = $vendor order by label_no asc";
$Q_label = query($sql);
ss_log_message( $sql );
if ($Q_label->numRows() == 0)
{
	echo "No rows defined for label for vendor $vendor";
	die;
}
$label1 = $Q_label->fetchRow();
$label2 = $Q_label->fetchRow();
$label3 = $Q_label->fetchRow();
$Q_label = query($sql);		//????  reset doesn't work

$cellWidth = $label2['addr1_x']-$label1['addr1_x'];
$pdf->setMaxWidth( $cellWidth );

while( true )
{
	$Q_label->reset();
	$pdf->AddPage();
	while ($label = $Q_label->fetchRow() )
	{
		if ($line = $Q_OrderSheetItems->fetchRow())
		{
			$dest = unserialize( $line['or_shipping_details']);

			$state_country = $dest['ShippingDetails']['0_50A4'];
			$pos = stripos( $state_country, "<BR>" );
			if( $pos !== FALSE )
			{
				$state = substr( $state_country, 0, $pos );
				$country = substr( $state_country, $pos + 4 );
			}
			else
			{
				$state = strip_tags($state_country);
				$country = strip_tags($state_country);
			}

			/* key
			0_B4BF		shipping company
			0_50A1		address 1
			0_50A2		city
			0_B4C0		zip
			0_B4C1		phone
			*/


			$line1 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['Name'] );
			$line2 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['0_B4BF'] );
			if( strlen( $line2 ) == 0 )
			{
				$line2 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['0_50A1']);
				$line3 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['0_50A2']." ".$state." ".$dest['ShippingDetails']['0_B4C0'] );
				$line4 = $country;
				if( ( $country == 'China' ) || ( $vendor == 4 && $country != 'Greece' ) )
					$line5 = 'Ph:'.$dest['ShippingDetails']['0_B4C1'];
				else
					$line5 = '';
				$line6 = '';
			}
			else
			{
				$line3 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['0_50A1']);
				$line4 = preg_replace( "/<BR>/i", " ", $dest['ShippingDetails']['0_50A2']." ".$state." ".$dest['ShippingDetails']['0_B4C0'] );
				if( ( $country == 'China' ) || ( $vendor == 4 && $country != 'Greece' ) )
					$line5 = $country.'              Ph:'.$dest['ShippingDetails']['0_B4C1'];
				else
					$line5 = $country;
				$line6 = '';
			}

			$colourRow = $Q_colours->getRow($label['addr1_font_colour']);
			$fontRow = $Q_fonts->getRow($label['addr1_font_id']);
			$pdf->CellHeader( $label['addr1_x'], $label['addr1_y'],
				$fontRow['font_name'], $label['addr1_font_attr'], $label['addr1_font_size'], 
						$label['addr1_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line1, 2 );

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
			$sz = $label['addr6_font_size'];
			if( $pdf->GetSplit() )
			{
				$pdf->SetY( $pdf->GetY() + 2 );
				$sz = $label['addr6_font_size']*2/3;
			}

			$pdf->CellNextLine(
				$fontRow['font_name'], $label['addr6_font_attr'], $sz, 
						$label['addr6_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line6, 2 );

			$pdf->CellHeader( $label['addr1_x'] + $cellWidth - 20, $label['addr1_y'],
				$fontRow['font_name'], $label['addr6_font_attr'], $label['addr6_font_size']/2, 
						$label['addr6_font_align'],
						$colourRow['colour_r'], $colourRow['colour_g'], $colourRow['colour_b'], 
						$line['or_tr_id'], 1, 0 );
		}
		else
		{
			$pdf->Output();
			exit;
		}

	}
}

?>
