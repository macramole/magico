<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<ul class="wrapper">
	<?php 
		//Cambio aca para que quede como array el post
		foreach ( $fields as $fieldName => $field ) {
			$field->name = $field->name . '[]';
		}
	
		if ( count($arrValues) > 0 ) {
			foreach( $arrValues as $rowNum => $row ) 
				include('multiplefield_field.php');
		}
	?> 
</ul>
<button type="button" id="add"><?= lang('magico_abm_add_another') ?></button>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<script>
	$thisField = $('#field_<?= $name ?>');
	
	$(function() {
		$('#add', $thisField).click( function() {
			$this = $(this);
			$this.text('<?= lang('magico_abm_adding_another') ?>').attr('disabled', true);
			
			$.ajax({
				url : '<?= $ajaxUrl ?>',
				type: 'POST',
				data: { 'cantFields' : $('li', $thisField).length },
				dataType: 'html',
				success: function(data) {
					$('ul', $thisField).append(data);
					$this.attr('disabled', false).text('<?= lang('magico_abm_add_another') ?>');
					$('ul', $thisField).show();
				}
			});
			
			$('ul', $thisField).sortable({
				revert: true,
				handle: '.dragMe'
			});
		});
		
		if ( $('ul li', $thisField).length > 0 )
		{
			$('ul', $thisField).sortable({
				revert: true,
				handle: '.dragMe'
			});
		}
		else
			$('ul', $thisField).hide();
		
		
		$('td.delete', $thisField).live('click', function(){
			$li = $(this).parents('li');
			$li.fadeOut(500, function() {
				$li.remove();
				
				if ( $('ul li', $thisField).length == 0 )
				{
					$('ul', $thisField).hide();
				}
			});
		});
	});
</script>