<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script>
	$( function() {
		<?php if ( count($styles) ) : ?>
		if ( !CKEDITOR.stylesSet.get('<?= $name ?>') )
			CKEDITOR.stylesSet.add('<?= $name ?>', <?= $styles ?>);		
		<?php endif;?>

		$('#abm #<?=$name?>').ckeditor(<?=$config?>);
	});
</script>
<textarea name="<?=$name?>" id="<?=$name?>"><?=$value?></textarea>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?><br />
