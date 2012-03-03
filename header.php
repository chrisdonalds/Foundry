<div id="wrapper" class="clearfix">
	<div id="header">
		<div id="logo"><a href="" title=""><img src="<?= WEB_URL.IMG_UPLOAD_FOLDER; ?>interface/" width="203" height="153" alt="" /></a></div>
		<div id="header-picture"></div>
	</div><!-- /header -->

	<ul id="nav">
		<li class="nav-home"><a href="<?= WEB_URL; ?>" title=""><span>Home</span></a></li>
		<li class="nav-about"><a href="<?= WEB_URL; ?>" title=""><span>About</span></a></li>
		<li class="nav-gallery"><a href="<?= WEB_URL; ?>gallery/" title=""><span>Gallery</span></a></li>
		<li class="nav-products"><a href="<?= WEB_URL; ?>" title=""><span>Products</span></a>
			<ul>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 01</a>
					<ul>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 01</a></li>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 02</a></li>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 03</a></li>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 04</a></li>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 05</a></li>
						<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 06</a></li>
						<li class="bottom-nav"></li>
					</ul>
				</li>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 02</a></li>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 03</a></li>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 04</a></li>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 05</a></li>
				<li><a href="<?= WEB_URL; ?>" title="">&#8226; Product 06</a></li>
				<li class="bottom-nav"></li>
			</ul>
		</li>
		<li class="nav-news"><a href="<?= WEB_URL; ?>news.php" title=""><span>Whats New</span></a></li>
		<li class="nav-contact"><a href="<?= WEB_URL; ?>contactus.php" title=""><span>Contact Us</span></a></li>
	</ul><!-- /nav -->

	<div class="aside floatLeft">
	<? include(SITE_PATH."aside.php"); ?>
	</div><!-- /aside-->

	<div id="container" class="floatRight">