<?php
	requireClass('ShopSystem_ProductsAdministration');	
	$temp = new Request("Security.Sudo",array('Action'=>'start'));			
	$productAdmin = new ShopSystem_ProductsAdministration($data['this']->asset->getID());		
	$temp = new Request("Security.Sudo",array('Action'=>'stop'));	
?>

<?php print($data['CurrencyConverterHTML']); ?>
<?php
$cloc = $GLOBALS['cfg']['FullURI'];
if( $pos = strpos( $cloc, '?BackStructure' ) )	
{
	$cloc = ($_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://");
	$cloc .= $_SERVER['HTTP_HOST'];
	$cloc .= ($_SERVER['SERVER_PORT'] != 80?':'.$_SERVER['SERVER_PORT']:'');
	$cloc .= $_SESSION['BackStack']->currentAttributeSet['REQUEST_URI'];
}
?>
<div class="row">
	<div class="col-lg-3 col-md-3 col-sm-12">
		<h1> <?php print(ss_HTMLEditFormat($data['SearchCategory'])); ?></h1>
		<?php if ($data['show_categories']) { ?>
			<a href='<?php echo "$cloc/ShowProductsInSubCategories/1";?>'> Show products in all the following subcategories </a>
		<?php } ?>
	</div>
	<div class="col-lg-9 col-md-9">
		<div class="tagcloud">
			<?php $tmpl_loop_rows = $data['Q_Taglist']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Taglist']->fetchRow()) { $tmpl_loop_counter++; ?>
			<?php 
				$loc = $cloc;
				if( $pos = strpos( $loc, '/Tag/' ) )	
					$loc = substr( $loc, 0, $pos );
				$loc .= "/Tag/".addslashes($row['ta_id']);
				ss_log_message( $loc );
				?>
			<button style="word-wrap:break-word;-moz-hyphens:auto;vertical-align:top;text-align:left;background:#c59d5f;line-height:130%;font-family:Avenir,Century Gothic,Roboto,Arial,sans-serif;color:#fefefe;font-size:18px;font-weight:700;text-decoration:none;display:inline-block;padding:5px 10px;border-radius:30px;border:1 solid #c59d5f" title="<?php print(ss_HTMLEditFormat($row['tag'])); ?>" type="button" onclick="window.location='<?=$loc?>'"><?php print(ss_HTMLEditFormat($row['tag'])); ?></button>
			<?php } ?>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-sm-12">
		<div class="description-category">
			<p><?= $data['SearchCategoryAll']['ca_description_html']?></p>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div id="content" class="col-sm-12">
				<h2 style="display:none;"><?php print(ss_HTMLEditFormat($data['SearchCategory'])); ?></h2>
				<div class="refine-search hidden"></div>
				<div class="filter clearfix">
					<div class="order-sort clearfix pull-left">
						<div class="group-text pull-left">
							<label class="control-label" for="input-sort">Sort By:</label>
						</div>
						<div class="select-wrap pull-left">
							<select id="input-sort" class="form-control input-sm" onchange="location = this.value;">
								<option value="Shop_System/Service/Engine/OrderBy/Avail.Price/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'Avail.Price' ) echo "selected=\"selected\""; ?>>
									Default
								</option>
								<option value="Shop_System/Service/Engine/OrderBy/ProductName/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'ProductName' ) echo "selected=\"selected\""; ?>>
									Name
								</option>
								<option value="Shop_System/Service/Engine/OrderBy/Price/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'Price' ) echo "selected=\"selected\""; ?>>
									Price
								</option>
								<option value="Shop_System/Service/Engine/OrderBy/BoxSize/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'BoxSize' ) echo "selected=\"selected\""; ?>>
									Box Size
								</option>
								<option value="Shop_System/Service/Engine/OrderBy/Updates/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'Updates' ) echo "selected=\"selected\""; ?>>
									Recent Updates
								</option>
								<option value="Shop_System/Service/Engine/OrderBy/Category/pr_ca_id/<?php print(ss_HTMLEditFormat($data['SearchCategoryID'])); ?>" <?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'Category' ) echo "selected=\"selected\""; ?>>
									Product Category
								</option>
							</select>
						</div>
					</div>
					<div class="btn-group display pull-right group-switch hidden-xs">
					  <button type="button" id="grid-view" class="btn-switch active" data-toggle="tooltip" title="" data-original-title="Grid"><i class="fa fa-th-large"></i></button>
					  <button type="button" id="list-view" class="btn-switch" data-toggle="tooltip" title="" data-original-title="List"><i class="fa fa-th-list"></i></button>
					</div>
					<div class= "btn-group display pull-right group-switch hidden-xs">
						<button type="button" id="grid-view" class= "btn-switch active" data-toggle="tooltip" title="" data-original-title="Grid"></button>
				<button type= "button" id="list-view" class="btn-switch" data-toggle= "tooltip" title="" data-original-title="List"></button>
					</div>
				</div>
	<?php
	// 3 across unless small desktop
	$col = -1;
	?>
				<div id="products" class="product-grid">
					<div class="products-block">
						<div class="row products-row">
						<?php $tmpl_loop_rows = $data['Q_Products']->numRows(); $tmpl_loop_counter = 0; while ($row = $data['Q_Products']->fetchRow()) { $tmpl_loop_counter++; ?>
							<?php if ($data['LastCategory'] !== $row['pr_ca_id']) { ?>
								<?php $data['LastCategory'] = $row['pr_ca_id']; $categoryRow = $data['Q_Categories']->getRow($data['Q_Categories']->getRowWithValue('ca_id',$data['LastCategory'])); $data['CategoryName']=$categoryRow['ca_name']; ?>		        	
							<?php } ?>
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


	if( ++$col >= 3 )
	{
	?>
						</div>
						<div class="row products-row">
	<?php $col = 0; } ?>
							<div class= "col-lg-4 col-md-4 col-sm-4 col-xs-12 product-col border">
								<div class="product-block">
<?php
									if( $data['this']->ATTRIBUTES['OrderBy'] == 'Category' )
									{
?>
									<div class="product-meta">
										<h6 class='category'>
											<a href="Shop_System/Service/Engine/OrderBy/Price.Avail/pr_ca_id/<?php print(ss_HTMLEditFormat($row['pr_ca_id'])); ?>"> <?php print(ss_HTMLEditFormat($row['ca_name'])); ?> </a>
										</h6>
									</div>
<?php
									}
?>
									<div class="image">
										<!-- Sale lable -->
										<?php if( $row['pro_special_price'] > 0 ) { ?>
										<div class="product-label bts">
											<div class="product-label-special">
												Sale
											</div>
										</div>
										<?php } ?>
										<!-- / Sale lable -->
										<a class="img" href="<?php print(ss_HTMLEditFormat($row['ProductDetailLink'])); ?>">
										<?php if (strlen($row['Image'])) { ?>
											<img border="0" src="<?php print(ss_HTMLEditFormat($row['Image'])); ?>" alt="<?php print(ss_HTMLEditFormat($row['pr_name'])); ?>" class="img-responsive" />
										<?php } ?>
										<div class="action">
											<a class="quickview iframe-link btn-product" data-placement="top" href="<?php print(ss_HTMLEditFormat($row['ProductPopupLink'])); ?>" title="Quick View">
												<i class="zmdi zmdi-eye"></i>
											</a>
											<a style= "display:none;" data-placement="top" class="zoom info-view btn-product" title="<?php print(ss_HTMLEditFormat($row['pr_name'])); ?>" href="<?php print(ss_HTMLEditFormat($row['FullImage'])); ?>">
												<i class="zmdi zmdi-zoom-in"></i>
											</a>
										</div>
									</div>
									<div class="rating clearfix">
										<div class="bg-rating clearfix">
										<?php if ($row['pr_customer_rating'] !== null and $row['pr_customer_rating_count'] !== null) { ?>
											<strong> Product Rating:</strong>
											<?php 
												$data['Star1'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-full.gif">';
												$data['Star2'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-empty.gif">';
												$data['Star3'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-empty.gif">';
												$data['Star4'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-empty.gif">';
												$data['Star5'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-empty.gif">';
												if ($row['pr_customer_rating'] >= 70) $data['Star2'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-full.gif">';
												if ($row['pr_customer_rating'] >= 80) $data['Star3'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-full.gif" >';
												if ($row['pr_customer_rating'] >= 90) $data['Star4'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-full.gif" >';
												if ($row['pr_customer_rating'] >= 95) $data['Star5'] = '<img title="'.$row['pr_customer_rating'].'" src="Images/star-full.gif">';
											?><?php print($data['Star1']); ?><?php print($data['Star2']); ?><?php print($data['Star3']); ?><?php print($data['Star4']); ?><?php print($data['Star5']); ?><br />
										<?php } ?>
										</div>
									</div>
									<div class="product-meta clearfix">
										<h6 class="name">
											<a href="<?php print(ss_HTMLEditFormat($row['ProductDetailLink'])); ?>"> <?php print(ss_HTMLEditFormat($row['pr_name'])); ?> </a>
										</h6>
										<!--p class="description"><?php print(ss_ParseText($row['pr_short'])); ?></p-->
										<div class="bottom clearfix">
											<?php if ($row['pr_id'] > 0) { ?>
												<?php print($row['PricesHTML']); ?>
												<?php print($data['this']->getOptions($row,$data['OptionFieldsArray'])) ?>
												<?php if( array_key_exists( 'pro_stock_available', $data ) ) $row['pro_stock_available'] = $data['pro_stock_available']; ?>
											<?php } ?>
										</div>
										<div class="cart">
											<?php if ($row['pro_stock_available'] > 10) { ?>
												<p class="text-center">In Stock</p>
												<button class="btn btn-cart" title= "Add to Cart" type="button" onclick="cart.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">Add to Cart</button>
												<button class="wishlist btn-product" type="button" title="Add to Wish List" onclick="wishlist.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">
													<i class="zmdi zmdi-favorite-outline"></i>
												</button>
											<?php } else { ?>
												<?php if ($row['pro_stock_available'] > 0) { ?>
													<p class="text-center">Low Stock</p>
													<button class="btn btn-cart" title= "Add to Cart" type="button" onclick="cart.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">Add to Cart</button>
													<button class="wishlist btn-product" type="button" title="Add to Wish List" onclick="wishlist.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">
														<i class="zmdi zmdi-favorite-outline"></i>
													</button>
												<?php } else { ?>
													<?php if ($row['pr_id'] > 0) { ?>
														<p class="text-center">Out of Stock</p>
														<button class="btn btn-cart" title= "Add to Cart" type="button" onclick="alert('Sorry, out of stock');">Add to Cart</button>
														<button class="wishlist btn-product" type="button" title="Add to Wish List" onclick="wishlist.add(<?php print(ss_HTMLEditFormat($row['pr_id'])); ?>);">
															<i class="zmdi zmdi-favorite-outline"></i>
														</button>
													<?php } ?>
												<?php } ?>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						</div>
					</div>
				</div>
				<?php print($data['PageThru']); ?>
			</div>
		</div>
	</div>
</div>
