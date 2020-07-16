                <div class="col-lg-3 col-md-3 col-sm-9 col-xs-9 column">
                  <div class="pull-right">
										<span class="product-block">
											<span class="cart">
												<button class="btn btn-cart" title="Checkout" type="button" onclick="<?php if( true /* array_key_exists( 'CountryAcknowledged', $_SESSION ) */ ) { ?>window.location='/Shop_System/Service/Login'<?php } else { ?>changeCountry()<?php }?>;">Checkout</button>
											</span>
										</span>
                  </div>
									<div class="pull-right">
									<div id="cart" class="btn-group btn-block">
<?php
// see also System/Classes/AssetTypes/ShopSystemAsset/Services/query_updatebasket.php:/Async
$show = '
											<button type="button" data-toggle="dropdown" data-loading-text="Loading..." class="dropdown-toggle">
												<span class="pull-right"><i class="fa fa-shopping-cart"></i>&nbsp;';

$pop = 'Your shopping cart is empty!';

if( array_key_exists( 'Shop', $_SESSION )
 && array_key_exists( 'Basket', $_SESSION['Shop'] )
 && array_key_exists( 'Products', $_SESSION['Shop']['Basket'] ) )
{
	if( count($_SESSION['Shop']['Basket']['Products']) == 0 )
	{
		$show .= '<span id="cart-number">0</span>';
		$show .= '<span id="cart-total">EUR 0.00</span>';
	}
	else
	{
		$pop = '';
		foreach( $_SESSION['Shop']['Basket']['Products'] as $product )
		{
			if( $product['Product']['pr_is_service'] == 'true' )
				;
			else
			{
				$pop .= '<p class="something">';
				if( array_key_exists( 'Qty', $product ) )
					$pop .= $product['Qty'].' x ';
				if( array_key_exists( 'Product', $product )
					&& array_key_exists( 'pr_name', $product[ 'Product' ] ) )
					$pop .= $product['Product']['pr_name'];
				$pop .= '</p>';
			}
		}
		$show .= '<span id="cart-number">'.$_SESSION['Shop']['Basket']['CartNumber'].'</span>';
		$show .= '<span id="cart-total">'.$_SESSION['Shop']['Basket']['CartTotal'].'</span>';
	}
}
$show .= '</span>
											</button>
                      <ul class="dropdown-menu pull-right table-responsive">
                        <li>
						  <div id="cartContent">
                          <p class="text-center">'.$pop."</p>
						  </div>
                        </li>
                        <li>
							<span class=\"product-block\">
								<span class=\"cart\">
									<button class=\"btn btn-cart\" title=\"Edit\" type=\"button\" onclick=\"window.location='/Shop_System/Service/Basket';\">Edit</button>
								</span>
							</span>
                        </li>
                      </ul>
";
echo $show;
?>
										</div>
                  </div>


                </div>
              <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 column">
		  <div id='google_translate_element' class="pull-right">
	      </div></div></div>
