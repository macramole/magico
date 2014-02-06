<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php if ( !$forcedValue ) : ?>
	<link rel="stylesheet" href="<?= MAGICO_PATH_CSS ?>/chosen.css" type="text/css" charset="utf-8" />

	<div id="<?= $name ?>">
		<?php //if ( $addNew ) : ?>
		<!--<button class="database-checklist-new" type="button"><?= lang('magico_abm_create_new') ?></button>-->
		<?php //endif; ?>
		<select class="database-multiselect chzn-select" multiple="multiple" name="<?=$name?>[]" id="<?=$name?>" data-placeholder="Seleccionar" >
			<?php foreach ($arrValues as $value) : ?>
			<option value="<?= $value['id'] ?>" <?= $value['selected'] ? 'selected="selected"' : '' ?> ><?= $value['title'] ?></option>
			<?php endforeach; ?>
		</select>
		<?php if ($helptext) : ?>
		<div class="helptext"><?= $helptext ?></div>
		<?php endif; ?>
	</div>
	<script type="text/javascript">
		$( function() {
			$LAB.script('<?= MAGICO_PATH_JS ?>/chosen.jquery.min').wait( function() {
				$('.database-multiselect').chosen({no_results_text: "No hay resultados para"});
			});
		});
	</script>
<?php else : ?>
<div class="input-select-forced">
	<span><?= $forcedValue['title'] ?></span>
	<input type="hidden" name="<?=$name?>[]" value="<?= $forcedValue['id'] ?>" />
</div>
<?php endif; ?>

<?php if ( $addNew ) : ?>
	<script type="text/javascript">
	/*
	$( function() {
		$('#<?= $name ?> button').click( function () {
			if ( $('#<?=$name?>').data('addingNew') != true )
			{
				addNewUrl = 'abm/create/<?= $model ?>';
				$('#<?=$name?>').data('addingNew', true);
				$.prettyPhoto.openNewWithState(addNewUrl);
			}
		});
		
		$('#<?= $name ?> label').click( function () {
			$input = $(this).siblings('input');
			$input.attr('checked', !$input.attr('checked') );
		});
		
		
		$('body').bind('onAbmRefresh', function (event) { 
			if ( $('#abm #<?=$name?>').data('addingNew') )
			{
				$this = $('#abm #<?=$name?> ul');
				
				$liLoading = $('<li></li>').addClass('loading');
				$liLoading.text('<?= lang('magico_abm_loading') ?>');
				$liLoading.appendTo($this);
				
				$.ajax({
					url: '<?= $ajaxUrl ?>',
					dataType: 'json',
					success: function(data) {
						$('#abm #<?=$name?> .loading').remove();
						
						for ( i = 0 ; i < data.length ; i++ )
						{	
							if ( $('li input[value="' + data[i].id + '"]', $this).length == 0 )
                            {
                                $li = $('<li></li>');
                                $label = $('<label></label>').html( ' ' + data[i].title );
                                $check = $('<input type="checkbox" name="<?=$name ?>[]" />').val(data[i].id);

                                $li.append($check).append($label).appendTo($this);
                            }
						}
					},
					error: function() {
						alert('<?= lang('magico_abm_error') ?>');
					}
				});

				$('#abm #<?=$name?>').data('addingNew', null);
			}
		} );
	});*/
	</script>
<?php endif; ?>
