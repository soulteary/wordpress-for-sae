<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>
	<link rel="alternate" type="application/rss+xml" title="<?php _e('RSS 2.0 - all posts', 'inove'); ?>" href="<?php echo $feed; ?>" />
	<link rel="alternate" type="application/rss+xml" title="<?php _e('RSS 2.0 - all comments', 'inove'); ?>" href="<?php bloginfo('comments_rss2_url'); ?>" />

	<!-- style START -->
	<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/404.css" type="text/css" media="screen" />
	<!-- style END -->

	<?php wp_head(); ?>
</head>

<body>

<div id="container">
	<div id="talker">
		<a href="http://www.neoease.com/"><img src="<?php bloginfo('template_url'); ?>/img/lovelace.gif" alt="<?php _e('Talker', 'inove'); ?>" /></a>
	</div>
	<div id="notice">
		<h1><?php _e('Welcome to 404 error page!', 'inove'); ?></h1>
		<p><?php _e("Welcome to this customized error page. You've reached this page because you've clicked on a link that does not exist. This is probably our fault... but instead of showing you the basic '404 Error' page that is confusing and doesn't really explain anything, we've created this page to explain what went wrong.", 'inove'); ?></p>
		<p><?php _e("You can either (a) click on the 'back' button in your browser and try to navigate through our site in a different direction, or (b) click on the following link to go to homepage.", 'inove'); ?></p>
		<div class="back">
			<a href="<?php bloginfo('url'); ?>/"><?php _e('Back to homepage &raquo;', 'inove'); ?></a>
		</div>
		<div class="fixed"></div>
	</div>
	<div class="fixed"></div>
</div>

</body>
</html>
