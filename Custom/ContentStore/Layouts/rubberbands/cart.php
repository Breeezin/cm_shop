<?php
if( array_key_exists( 'Shop', $_SESSION )
 && array_key_exists( 'Basket', $_SESSION['Shop'] )
 && array_key_exists( 'Products', $_SESSION['Shop']['Basket'] ) )
 	{
	echo "<span align='center'><p style='color:white; font: normal normal 180% Helvetica'>Shopping Cart</p><div class='basketCase'></span>";
	if( count($_SESSION['Shop']['Basket']['Products']) == 0 )
		{
		echo "Sadly Empty";
		}
	else
		{
		foreach( $_SESSION['Shop']['Basket']['Products'] as $product )
			{
			echo '<p style="color:white; font: normal normal 80% Helvetica" align="left">';
			if( array_key_exists( 'Qty', $product ) )
				echo $product['Qty'].' x ';
			if( array_key_exists( 'Product', $product )
			  && array_key_exists( 'pr_name', $product[ 'Product' ] ) )
				echo $product['Product']['pr_name'];
			echo '</p>';
			}
		echo '';
		}
	echo "</div>";
	}
else
	echo "Basket is Empty";
?>
<a class="red-button" href="https://<?=$_SERVER['HTTP_HOST']?>/Shop_System/Service/Basket"> Edit </a>
<a class="red-button-cart" href="https://<?=$_SERVER['HTTP_HOST']?>/Shop_System/Service/Login"> Checkout </a>
