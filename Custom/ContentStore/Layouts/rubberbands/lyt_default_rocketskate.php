<?php header( "Cache-Control: max-age=60, public" ); ?>
<html>
    <head>
		<title>{[WindowTitle]}</title>
		<meta name="keywords" 	     content="{[Keywords]}"/>
		<meta name="description" 	 content="{[Description]}"/>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<link href="sty_reset.css" rel="stylesheet" type="text/css" />
		<link href="sty_grid.css" rel="stylesheet" type="text/css" />
		<link href="sty_layout.css" rel="stylesheet" type="text/css" />
		<link href="sty_main.css" rel="stylesheet" type="text/css" />
		<link href="sty_superfish.css" rel="stylesheet" type="text/css" />
	</head>
	<body id="defaultlayout">
		<div class="body">
			<header id="header">
				<div class="container">
					<h1 class="logo">
						<a href="/">
							<img alt="Product Export" src="Custom/ContentStore/Layouts/acmerockets/Images/logo.png">
						</a>
					</h1>
					<div class="unit size4of5 top"><? include("Custom/ContentStore/Layouts/acmerockets/country.php") ?></div>
					<div class="unit size1of5 topRight"><? include("Custom/ContentStore/Layouts/acmerockets/login.php") ?></div>
					<div class="cart border"><? include("Custom/ContentStore/Layouts/acmerockets/cart.php") ?></div>
					<ul class="social-icons">
						<li class="facebook">
							<a href="https://www.facebook.com/cmsmadesimple" onclick="_gaq.push(['_trackEvent', 'Outgoing_Links', 'Clicked', 'Facebook']);" target="_blank" title="Facebook">Facebook</a>
						</li>
						<li class="googleplus">
							<a href="https://plus.google.com/+cmsmadesimple" onclick="_gaq.push(['_trackEvent', 'Outgoing_Links', 'Clicked', 'Google+']);" target="_blank" title="Google+">Google+</a>
						</li>
						<li class="linkedin">
							<a href="https://www.linkedin.com/groups?gid=1139537" onclick="_gaq.push(['_trackEvent', 'Outgoing_Links', 'Clicked', 'LinkedIn']);" target="_blank" title="Linkedin">Linkedin</a>
						</li>
						<li class="twitter">
							<a href="https://twitter.com/cmsms" onclick="_gaq.push(['_trackEvent', 'Outgoing_Links', 'Clicked', 'Twitter']);" target="_blank" title="Twitter">Twitter</a>
						</li>
						<li class="youtube">
							<a href="https://www.youtube.com/user/cmsmsofficial" onclick="_gaq.push(['_trackEvent', 'Outgoing_Links', 'Clicked', 'Youtube']);" target="_blank" title="YouTube">YouTube</a>
						</li>
					</ul>
					<button class="btn btn-responsive-nav btn-inverse" data-toggle="collapse" data-target=".nav-main-collapse">
						<i class="icon icon-bars"></i>
					</button>
				</div>
				<div class="navbar-collapse nav-main-collapse collapse">
					<div class="container">
						<nav class="nav-main mega-menu">
							<? include("Custom/ContentStore/Layouts/rubberbands/menu.php") ?>
						</nav>
					</div>
				</div>
			</header>
			<div role="main" class="main">
				<section class="page-top">
					<div class="container">
						<div class="row">
							<div class="col-md-12">
								<h2>{[TitleImage]}</h2>
							</div>
						</div>
					</div>
				</section>
				<div style="clear:both; height:20px;"></div>
					<a href="https://docs.cmsmadesimple.org/tags/microtiny-wysiwyg-editor?showform=true" class="btn_feedback" rel="nofollow">
						<span title="Give us your feedback!" class="icon icon-reply"></span>
					</a>
					<div class="container">
						<div class="row">
							<div class="pad col-md-3">
								<div class="hide_on_mobile">
									<ul class="menu">
										<? include("Custom/ContentStore/Layouts/acmerockets/categories.php") ?>
									</ul>
								</div>
							</div>
							<div class="pad col-md-9">
								{[Content]}
							</div>
						</div>
					</div>
				</div>
			</div>
			<? include("Custom/ContentStore/Layouts/acmerockets/footer.php") ?>
			<? include("Custom/ContentStore/Layouts/acmerockets/tracker.php") ?>
		</div>
	</body>
</html>
