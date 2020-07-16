<!DOCTYPE html>
<!--[if IE]><![endif]-->
<!--[if IE 8 ]><html dir="ltr" lang="en" class="ie8"><![endif]-->
<!--[if IE 9 ]><html dir="ltr" lang="en" class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]>-->
<!--- _members_ -->
<html dir="ltr" class="ltr" lang="en">
  <!--<![endif]-->

  <head>
    <meta content="text/html; charset=<?=$GLOBALS['cfg']['Web_Charset']?>" http-equiv="content-type">
    <meta name="keywords" content="{[Keywords]}">
    <meta name="description" content="{[Description]}">
    <title>{[WindowTitle]}</title>
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
    <link href="css/shop.css" rel="stylesheet">
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
        <div class="main-columns container container-full">
          <div class="row">
            <div id="content" class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="product-info">
                <?php
                if( array_key_exists( 'LYT_BANNER', $data['this']->assetLayoutSettings) && strlen( $data['this']->assetLayoutSettings['LYT_BANNER'] ))
                  echo "<img src='{$data['this']->assetLayoutSettings['LYT_BANNER']}' class='layoutBanner' />";
                ?>
              </div>
              <!--div class="heading-detail">
                <h3>{[TitleImage]}</h3>
              </div -->
              <p>
                <h1>
                  <a href='/Members'>{[TitleImage]}<a/><!-- service here?  -->
                </h1>
              </p>
              {[Content]}
            </div>
          </div>
        </div>
      </div>
      <footer id="footer" class="footer footer-v1 nostylingboxs">
        <div class="footer-top" id="acme-footer-top">
          <div class="container">
            <div class="inner">
              <div class="row">
                <?php // include("Custom/ContentStore/Layouts/acmerockets/newsletter.php") ?>
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
  </body>
</html>
