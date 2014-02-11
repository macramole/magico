<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= MAGICO_PATH_CSS ?>/jquery.tagit.css" type="text/css" charset="utf-8" />
<ul id="<?= $name ?>">
	<?php if ( is_array($value) ) : ?>
		<?php foreach ( $value as $tag ) : ?>
		<li><?= $tag ?></li>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<script>
	$( function() {
		
		$LAB.script('<?= site_url(MAGICO_PATH_JS . '/jquery.tag-it.min.js') ?>').wait( function() {
			$('#<?= $name ?>').tagit({
				availableTags : <?= magico_arrPHP2JS($tags) ?>,
				itemName: 'item',
				fieldName: '<?= $name?>[]',
				allowSpaces: true,
				removeConfirmation: true
			});
		});
	});
</script>