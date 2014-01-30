<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script>
	$(function() {
		$LAB.script('<?= MAGICO_PATH_JS ?>/jquery.ui.datepicker-es.js').wait(function() {
			$('#abm #<?=$name?>').datepicker({
				dateFormat: 'dd/mm/yy',
				altField: "#abm #<?=$name?>Alt",
				altFormat: 'yy-mm-dd'
			});

			$('#abm #<?=$name?>').datepicker('option', $.datepicker.regional[ "es" ] );
		});
	});
</script>
<input class="input-text" type="text" id="<?=$name?>" value="<?= $value ? date('d/m/Y',strtotime($value)) : ''; ?>" />
<input type="hidden" name="<?=$name?>" id="<?=$name?>Alt" />
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>