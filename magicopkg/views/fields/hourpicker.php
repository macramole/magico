<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<script>
	function normalizeHora(val)
	{
		if ( val == '' || isNaN(val) )
			return '00';
		
		val = parseInt(val);
		
		if ( val < 10 )
			return '0' + val;
		
		if ( val > 23 )
			return '23';
		
		return val;
	}
	
	function normalizeMinutos(val)
	{
		if ( val == '' || isNaN(val) )
			return '00';
		
		val = parseInt(val);
		
		if ( val < 10 )
			return '0' + val;
		
		if ( val > 59 )
			return '00';
		
		return val;
	}
	
	$(function() {
			$('#<?=$cssId?>Horas, #<?=$cssId?>Minutos').change( function() {
			$('#<?=$cssId?>Horas').val( normalizeHora( $('#<?=$cssId?>Horas').val() ) );
			$('#<?=$cssId?>Minutos').val( normalizeMinutos( $('#<?=$cssId?>Minutos').val() ) );
			
			$('#<?=$cssId?>Alt').val( $('#<?=$cssId?>Horas').val() + ':' + $('#<?=$cssId?>Minutos').val() );
		});
		
		$('#<?=$cssId?>Horas').keyup( function() {
			if ( $(this).val().length == 2 )
				$('#<?=$cssId?>Minutos').focus();
		});
	});
</script>
<input class="input-text input-time" maxlength="2" type="text" id="<?=$cssId?>Horas" value="<?=substr($value,0,2)?>" /> 
<span>:</span>
<input class="input-text input-time" type="text" maxlength="2" id="<?=$cssId?>Minutos" value="<?=substr($value,3,2)?>" />
<input type="hidden" name="<?=$name?>" id="<?=$cssId?>Alt" value="<?= $value ?>" />
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>