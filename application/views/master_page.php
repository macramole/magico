<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="description" content="Description of the site" />

		<meta property="og:title" content="<?= $title ?> <?= $sectionTitle ? "| $sectionTitle" : '' ?>"/>
		<meta property="og:url" content="<?= current_url() ?>"/>
		<meta property="og:description" content="<?= $og_description ?>"/>
		<meta property="og:image" content="<?= $og_image?>" />
		
		<link rel="icon" type="image/png" href="favicon.ico">
		
		<title><?= $title ?> <?= $sectionTitle ? "| $sectionTitle" : '' ?> </title>

		<base href="<?= base_url() ?>" />
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="js/modernizr.min.js" type="text/javascript" charset="utf-8"></script>		
		
		<link rel="stylesheet" href="css/core.css" type="text/css" charset="utf-8" />
		<link rel="stylesheet" href="css/newWebsite.css" type="text/css" charset="utf-8" />
		
		<?= $head ?>
    </head>
    <body>
		<mp:Content />
		
		<mp:Adminnav />
		
		<?php foreach ( $messages as $message ) : ?>
		<div class="jGrowlMessage" style="display: none">
			<?= $message ?>
		</div>
		<?php endforeach; ?>		
    </body>
</html>
