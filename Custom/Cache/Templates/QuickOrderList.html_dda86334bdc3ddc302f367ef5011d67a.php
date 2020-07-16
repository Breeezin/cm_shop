                                    <div class="item active products-block">
                                      <div class="row products-row last">
<?php
  requireClass('ShopSystem_ProductsAdministration');  
  $temp = new Request("Security.Sudo",array('Action'=>'start'));      
  $productAdmin = new ShopSystem_ProductsAdministration($data['this']->asset->getID());    
  $temp = new Request("Security.Sudo",array('Action'=>'stop'));  

/*
<?php print($data['CurrencyConverterHTML']); ?>
*/
$n = 0;
?>
<?php $tmpl_loop_rows = $data['Q_Products']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Products']->fetchRow()) { $tmpl_loop_counter++; ?>
<?php

	if( $row['pr_combo'] >= 1 )
	{
		$Q_Combos = query("SELECT pr_id, pro_stock_available, cpr_qty FROM shopsystem_products, shopsystem_product_extended_options, shopsystem_combo_products
				WHERE cpr_element_pr_id = {$row['pr_id']}
					AND cpr_pr_id = pr_id
					AND pr_id = pro_pr_id");

		$instock = 999;
		while ($comrow=$Q_Combos->fetchRow())
			if( ($comrow['pro_stock_available'] != null) && ($comrow['cpr_qty'] >= 1 ) )
				if( $instock > $comrow['pro_stock_available']/$comrow['cpr_qty'] )
					$instock = $comrow['pro_stock_available']/$comrow['cpr_qty'];
		$row['pro_stock_available'] = (int)$instock;
	}

	if( ++$n >= 7 ) { $n = -1000;

?>
                                      </div>
                                    </div>
                                    <div class="item products-block">
                                      <div class="row products-row">
<?php } ?>
                                        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 product-col border">
                                          <div class="product-block">
                                            <div class="image">
                                              <a class="img" href="<?php print(ss_HTMLEditFormat($row['ProductDetailLink'])); ?>">
                                                <img border="0" src="<?php print(ss_HTMLEditFormat($row['WideImage'])); ?>" alt="<?php print(ss_HTMLEditFormat($row['pr_name'])); ?>" class="img-responsive" />
                                              </a>
                                            </div>
                                            <div class="product-meta clearfix">
                                              <h6 class="name">
                                                  <a href="<?php print(ss_HTMLEditFormat($row['ProductDetailLink'])); ?>"> <?php print(ss_HTMLEditFormat($row['pr_name'])); ?> </a>
                                              </h6>
                                              <div class="action">
                                                <a class="quickview iframe-link btn-product" data-placement="top" 
                                                  href="<?php print(ss_HTMLEditFormat($row['ProductPopupLink'])); ?>" title="Quick View">
                                                  <i class="zmdi zmdi-eye"></i>
                                                </a>
                                                <a style="display:none;" data-placement="top" class="zoom info-view btn-product"
                                                  title="<?php print(ss_HTMLEditFormat($row['pr_name'])); ?>" href="<?php print(ss_HTMLEditFormat($row['WideImage'])); ?>">
                                                  <i class="zmdi zmdi-zoom-in"></i>
                                                </a>
                                                <button class="wishlist btn-product" type="button" title="Add to Wish List"
                                                  onclick="wishlist.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">
                                                  <i class="zmdi zmdi-favorite-outline"></i>
                                                </button>
                                              </div>
                                              <div class="bottom clearfix">
                                                <div class="price">
                                                  <span class="price-new"><?php print($row['PricesHTML']); ?></span>
													<?php print($data['this']->getOptions($row,$data['OptionFieldsArray'])) ?>
													<?php if( array_key_exists( 'pro_stock_available', $data ) ) $row['pro_stock_available'] = $data['pro_stock_available']; ?>
                                                </div>
                                              </div>
                                              <div class="cart">
																								<?php if ($row['pro_stock_available'] > 10) { ?>
																									<p>In Stock</p>
																									<button class="btn btn-cart" title="Add to Cart" type="button" onclick="cart.add('<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>');"> Add to Cart </button>
																								<?php } else { ?>
																									<?php if ($row['pro_stock_available'] > 0) { ?>
																										<p>Low Stock</p>
																										<button class="btn btn-cart" title="Add to Cart" type="button" onclick="cart.add('<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>');"> Add to Cart </button>
																									<?php } else { ?>
																										<p>Out of Stock</p>
																										<button class="btn btn-cart" title="Add to Cart" type="button" onclick="alert('Sorry, out of stock');"> Add to Cart </button>
																									<?php } ?>
																								<?php } ?>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
<?php } ?>
                                      </div>
                                    </div>
