<!DOCTYPE html>
<!--[if IE]><![endif]-->
<!--[if IE 8 ]><html dir="ltr" lang="en" class="ie8"><![endif]-->
<!--[if IE 9 ]><html dir="ltr" lang="en" class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]>-->
<!--- _home2_ -->
<html dir="ltr" class="ltr" lang="en">
  <!--<![endif]-->

  <head>
    <meta content="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>" http-equiv="content-type">
    <meta name="keywords" content="<?php if (file_exists(expandPath('Custom/Tags/Keywords.php'))) require('Custom/Tags/Keywords.php'); else require('System/Tags/Keywords.php'); ?>">
    <meta name="description" content="<?php if (file_exists(expandPath('Custom/Tags/Description.php'))) require('Custom/Tags/Description.php'); else require('System/Tags/Description.php'); ?>">
	<meta name="google-site-verification" content="IjrhVVSvgQvSK0AvchgGEHM_xfMKCqxP3Baldw-BxZ0" />
    <title><?php if (file_exists(expandPath('Custom/Tags/WindowTitle.php'))) require('Custom/Tags/WindowTitle.php'); else require('System/Tags/WindowTitle.php'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="images/logo.png" rel="icon">
    <link href="css/stylesheet.css" rel="stylesheet">
    <link href="css/paneltool.css" rel="stylesheet">
    <link href="css/animate.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/material-design-iconic-font.min.css" rel="stylesheet">
    <link href="js/jquery/magnific/magnific-popup.css" rel="stylesheet">
    <link href="css/owl.carousel.css" rel="stylesheet">
    <link href="css/homebuilder.css" rel="stylesheet">
    <link href="css/typo.css" rel="stylesheet">
    <link href="css/newsletter.css" rel="stylesheet">
    <script type="text/javascript" src="js/jquery/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="js/jquery/magnific/jquery.magnific-popup.min.js"></script>
    <script type="text/javascript" src="js/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/common.js"></script>
    <script type="text/javascript" src="js/theme/common.js"></script>
    <script type="text/javascript" src="js/jquery/owl-carousel/owl.carousel.min.js"></script>
    <script type="text/javascript" src="js/jquery/colorpicker/js/colorpicker.js"></script>
    <script type="text/javascript" src="js/layerslider/jquery.themepunch.plugins.min.js"></script>
    <script type="text/javascript" src="js/layerslider/jquery.themepunch.revolution.min.js"></script>
  </head>

  <body class="common-home page-home layout-fullwidth">
    <div class="row-offcanvas row-offcanvas-left">
      <!-- header -->
      <header class="header header-v1">
        <?php include("Custom/ContentStore/Layouts/acmerockets/nav.php") ?>
        <div class="header-middle header-middle-v1">
          <div class="container">
            <div class="inner">
              <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                  <!-- logo -->
                  <div id="logo-theme" class="logo">
                    <a href="/">
                      <img src="images/logo.png" title="Acmeexpess" alt="Acmeexpess" class="img-responsive">
                    </a>
                  </div>
                </div>
                <div class="col-lg-7 col-md-7 col-sm-3 col-xs-3">
                  <div class="navbar-mega">
                    <button class="btn btn-primary canvas-menu hidden-lg hidden-md " type="button" data-toggle="offcanvas">
                      <span class="fa fa-bars">Menu</span>
                    </button>
                    <?php include("Custom/ContentStore/Layouts/acmerockets/nav2.php") ?>
                  </div>
                </div>
                <?php include("Custom/ContentStore/Layouts/acmerockets/cart.php") ?>
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- /header -->
      <div class="maincols">
        <div class="main-columns container-full">
          <div class="row">
            <div id="sidebar-main" class="col-md-12">
              <div id="content">
                <div class="clearfix home3">
                  <div class="acme-container">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="col-inner">
                              <div class=" panel-special layerslider-wrapper" style="max-width:1080px;">
                                <div class="bannercontainer banner-boxed" style="padding: 0;margin: ;">
                                  <div id="sliderlayer1222763614" class="rev_slider boxedbanner"
                                    style="width:100%;height:300px;">
                                    <ul>
                                    <?php 
                                    foreach(glob('images/banner/*.jpg') as $image) {
                                    ?>
                                      <li data-masterspeed="300" data-transition="random" data-slotamount="7" data-thumb="">
                                        <img src="<?=$image?>" alt="Image 0">
                                      </li>
                                      <?php                                     } ?>
                                    </ul>
                                  </div>
                                </div>
                              </div>
                              <!--
                        ##############################                         - ACTIVATE THE BANNER HERE -
                        ##############################                        -->
                              <script type="text/javascript">
                                             
                                var tpj = jQuery;

                                if (tpj.fn.cssOriginal != undefined)
                                  tpj.fn.css = tpj.fn.cssOriginal;

                                tpj ('#sliderlayer1222763614').revolution (
                                {
                                   delay: 9000,
                                   startheight: 349,
                                   startwidth: 1080,
                                   hideThumbs: 0,
                                   thumbWidth: 100,
                                   thumbHeight: 50,
                                   thumbAmount: 5,
                                   navigationType: "none",
                                   navigationArrows: "verticalcentered",
                                   navigationStyle: "round",
                                   navOffsetHorizontal: 20,
                                   navOffsetVertical: 20,
                                   touchenabled: "on",
                                   onHoverStop: "on",
                                   shuffle: "off",
                                   stopAtSlide: -1,
                                   stopAfterLoops: -1,
                                   hideCaptionAtLimit: 0,
                                   hideAllCaptionAtLilmit: 0,
                                   hideSliderAtLimit: 0,
                                   fullWidth: "off",
                                   shadow:0
                                } )
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
	<div class="col-sm-12">
			<div class="description-category">
			<p style="text-align: center">

Our online Chilean Llamas shop has been selling an extensive range of Chilean Llamas, Vicuña since 1997. We have been, for many years now, one of the leading online Chilean Product shops. Our 100% Authenticity Guarantee, competitive prices and prompt service have made us the choice for many llama buyers. </p>
		</div>
	</div>
</div>
                  
                                  
                  <div class="acme-container">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="col-inner">
                              <div class="panel-special no-space-row box-products-list product-grid">
                                <div class="panel-heading">
                                  <h3 class="panel-title">Weekly Specials</h3>
                                </div>
                                <div class="list panel-body padding-0 owl-carousel-play" id="product_hot" data-ride="owlcarousel">
                                  <div class="carousel-controls-list">
                                    <a class="carousel-control left" href="#product_specials" data-slide="prev"><i class="zmdi zmdi-chevron-left" aria-hidden="true"></i></a>
                                    <a class="carousel-control right" href="#product_specials" data-slide="next"><i class="zmdi zmdi-chevron-right" aria-hidden="true"></i></a>
                                  </div>
                                  <div class="owl-carousel" data-show="1" data-pagination="false" data-navigation="true">
                                        <?php
                                          $specials = new Request('Asset.Display',array(
                                              'as_id'    =>    '514',
                                              'Service'    =>    'Engine',
                                              'RowsPerPage'    =>    12,
                                              'Specials'  =>  1,
											  'Featured'	=> 3,
                                              'NoHusk'  => 1,
                                              'OrderBy'    =>    'Random',
                                              'NoOverrideRows'    =>    1,
                                              'Template'    =>  'QuickOrderList',
                                              'PricesType'  =>  'SmallHTML',
                                              ));
											ss_log_message_r(  'Log:'.__FILE__.':'.__LINE__, $specials );
                                          print $specials->display;
                                        ?>
                                  </div>
                                </div>
                                <div class="clearfix"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="acme-container">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="col-inner">
                              <div class="panel-special no-space-row box-products-list product-grid">
                                <div class="panel-heading">
                                  <h3 class="panel-title">Popular this week</h3>
                                </div>
                                <div class="list panel-body padding-0 owl-carousel-play" id="product_hot" data-ride="owlcarousel">
                                  <div class="carousel-controls-list">
                                    <a class="carousel-control left" href="#product_hot" data-slide="prev"><i class="zmdi zmdi-chevron-left" aria-hidden="true"></i></a>
                                    <a class="carousel-control right" href="#product_hot" data-slide="next"><i class="zmdi zmdi-chevron-right" aria-hidden="true"></i></a>
                                  </div>
                                  <div class="owl-carousel" data-show="1" data-pagination="false" data-navigation="true">
                                        <?php
                                          $latest = new Request('Asset.Display',array(
                                              'as_id'    =>    '514',
                                              'Service'    =>    'Engine',
                                              'RowsPerPage'    =>    12,
											  'Featured'	=> 3,
                                              'OrderBy'    =>    'Random',
											  'NoHusk'		=>	1,
                                              'NoOverrideRows'    =>    1,
                                              'Template'    =>  'QuickOrderList',
                                              'PricesType'  =>  'SmallHTML',
                                              ));
                                          print $latest->display;
                                        ?>
                                  </div>
                                </div>
                                <div class="clearfix"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  
                  <div class="acme-container">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="col-inner">
                              <div class="panel-special no-space-row box-products-list product-grid">
                                <div class="panel-heading">
                                  <h3 class="panel-title">Featured Products</h3>
                                </div>
                                <div class="list panel-body padding-0 owl-carousel-play" id="product_latest" data-ride="owlcarousel">
                                  <div class="carousel-controls-list">
                                    <a class="carousel-control left" href="#product_latest" data-slide="prev"><i class="zmdi zmdi-chevron-left" aria-hidden="true"></i></a>
                                    <a class="carousel-control right" href="#product_latest" data-slide="next"><i class="zmdi zmdi-chevron-right" aria-hidden="true"></i></a>
                                  </div>
                                  <div class="owl-carousel" data-show="1" data-pagination="false" data-navigation="true">
                                        <?php 
                                          $latest = new Request('Asset.Display',array(
                                              'as_id'    =>    '514',
                                              'Service'    =>    'Engine',
                                              'RowsPerPage'    =>    12,
											  'Featured'	=> 2,
                                              'OrderBy'    =>    'Random',
											  'NoHusk'		=>	1,
                                              'NoOverrideRows'    =>    1,
                                              'Template'    =>  'QuickOrderList',
                                              'PricesType'  =>  'SmallHTML',
                                              ));
                                          print $latest->display;
                                        ?>
                                  </div>
                                </div>
                                <div class="clearfix"></div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  
                  
                  <div class="acme-container">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="col-inner">
                              <div class="widget-images panel-special">
                                <div class="widget-inner img-adv clearfix">
                                  <div class="image-item">
                                      <a href="/Shop_System">
                                        <div class="effect-v1" style="text-align: center">
                                          <img class="img-responsive" alt=" " src="images/banner_bottom.jpg" style="width:80%;">
                                        </div>
                                      </a>
                                    <a href="images/banner_bottom.jpg" class="pts-popup fancybox" title="Great Combos">
                                    </a>
                                  </div>
                                </div>
                              </div>
                              <script type="text/javascript">
                                  $(document).ready(function(){ $(".widget-images").click(function(){ var link = $(".click").attr("href"); $(location).attr('href',link); }); });
                              </script>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  
                  

                  <div class="acme-container no-space-row">
                    <div class="acme-inner">
                      <div class="row row-level-1">
                        <div class="row-inner clearfix">
                          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="col-inner line-speacial">
                              <div class="row row-level-2">
                                <div class="row-inner clearfix">

                                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="col-inner">
                                      <div class="interactive-banner style1">
                                        <div class="image">
                                          <img alt="flavours" src="/images/colours.png" class="img-responsive">
                                          <div class="content">
                                            <div class="content-inner">
                                              <div class="interactive-profile">
                                                <a href="/Acme_Rockets/Cuban_Cigars_101/Flavours_and_Aromas" class="widget-heading">Colours and Textures</a>
                                                <div class="htmlcontent">
                                                </div>
                                                <div class="action-button button-special">
                                                  <a href="/Acme_Rockets/Cuban_Cigars_101/Flavours_and_Aromas"><span>Learn more</span></a>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="before-content"> 
                                            <a href="/Acme_Rockets/Cuban_Cigars_101/Flavours_and_Aromas" class="widget-heading">Colours and Textures</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="col-inner">
                                      <div class="interactive-banner style1">
                                        <div class="image">
                                          <img alt="sizes" src="/images/llama_sizes.jpg" class="img-responsive">
                                          <div class="content">
                                            <div class="content-inner">
                                              <div class="interactive-profile">
                                                <a href="Acme_Rockets/Cuban_Cigars_101/Cigar_Sizes/Thick_Gauge" class="widget-heading">Product Sizes</a>
                                                <div class="htmlcontent">
                                                </div>
                                                <div class="action-button button-special">
                                                  <a href="Acme_Rockets/Cuban_Cigars_101/Cigar_Sizes/Thick_Gauge"><span>Learn more</span></a>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="before-content"> 
                                            <a href="Acme_Rockets/Cuban_Cigars_101/Cigar_Sizes/Thick_Gauge" class="widget-heading">Product Sizes</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="col-inner">
                                      <div class="interactive-banner style1">
                                        <div class="image">
                                          <img alt="history" src="/images/production.jpg" class="img-responsive">
                                          <div class="content">
                                            <div class="content-inner">
                                              <div class="interactive-profile">
                                                <a href="Acme_Rockets/Cuban_Cigars_101/History_and_Production" class="widget-heading">History and Production</a>
                                                <div class="htmlcontent">
                                                </div>
                                                <div class="action-button button-special">
                                                  <a href="Acme_Rockets/Cuban_Cigars_101/History_and_Production"><span>Learn more</span></a>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="before-content"> 
                                            <a href="Acme_Rockets/Cuban_Cigars_101/History_and_Production" class="widget-heading">History and Production</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="col-inner">
                                      <div class="interactive-banner style1">
                                        <div class="image">
                                          <img alt="guarantee" src="/images/guarantee.jpg" class="img-responsive">
                                          <div class="content">
                                            <div class="content-inner">
                                              <div class="interactive-profile">
                                                <a href="Acme_Rockets/Credentials_and_Guarantee" class="widget-heading">Guarantee</a>
                                                <div class="htmlcontent">
                                                </div>
                                                <div class="action-button button-special">
                                                  <a href="Acme_Rockets/Credentials_and_Guarantee"><span>Learn more</span></a>
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                          <div class="before-content"> 
                                            <a href="Acme_Rockets/Credentials_and_Guarantee" class="widget-heading">Guarantee</a>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="col-inner">
                              <div class="interactive-banner style4">
                                <div class="image">
                                  <img alt="" src="/images/combos1.jpg" class="img-responsive">
                                  <div class="content">
                                    <div class="content-inner">
                                      <div class="interactive-profile">
                                          <p><p>                                      <div class="action-button button-special">
                                          <a href="/Shop_System"><span>Shop now!</span></a>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer id="footer" class="footer footer-v1 nostylingboxs">
        <div class="footer-top" id="acme-footer-top">
          <div class="container">
            <div class="inner">
              <div class="row">
                <?php include("Custom/ContentStore/Layouts/acmerockets/newsletter.php") ?>
              </div>
            </div>
          </div>
        </div>
        <?php include("Custom/ContentStore/Layouts/acmerockets/footer.php") ?>
      </footer>
    <script type="text/javascript">
      $('#myTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
      })
      $('#myTab a:first').tab('show');
      var $MAINCONTAINER = $("html");
    </script>

    <div class="sidebar-offcanvas visible-xs visible-sm">
      <div class="offcanvas-inner panel-offcanvas">
        <div class="offcanvas-heading"></div>
        <div class="offcanvas-body">
          <div id="offcanvasmenu">
          </div>
        </div>
        <div class="offcanvas-footer panel-footer">
          <div class="input-group" id="offcanvas-search">
            <input class="form-control" placeholder="Search" name= "search" type="text">
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      $("#offcanvasmenu").html($("#bs-megamenu").html());
    </script>
    <div id="top">
      <a class="scrollup" href="">
        <i class="fa fa-angle-double-up"></i>
      </a>
    </div>
    <?php include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>
    <?php include("Custom/ContentStore/Layouts/acmerockets/signup.php") ?>
  </body>
</html>
