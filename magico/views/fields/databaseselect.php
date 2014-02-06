<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php if ( !$forcedValue ) : ?>
	<select class="input-select" name="<?=$name?>" id="<?=$cssId?>">
		<?php if ( $addDefaultOption ) : ?>
		<option value="0" selected="true"><?=$addDefaultOption?></option>
		<?php endif; ?>

		<?php foreach ($arrValues as $option) : ?>
		<option value="<?=$option['id']?>" <?= $option['id'] == $value ? 'selected' : ''?>><?=$option['value'] ? $option['value'] : $option['title']?></option>
		<?php endforeach; ?>

		<?php if ( $addNew ) : ?>
		<option class="SimpleSelect_new" value="new"><?= lang('magico_selectdb_new') ?></option>
		<?php endif; ?>
	</select>
<?php else : ?>
<div class="input-select-forced">
	<span><?= $forcedValue['title'] ?></span>
	<input type="hidden" name="<?=$name?>" value="<?= $forcedValue['id'] ?>" />
</div>
<?php endif; ?>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<?php if ( $addNew ) : ?>
	<script type="text/javascript">
	$( function() {
		$('#abm select#<?= $cssId ?>').click( function () {
			
			if ( !$(this).children(":selected").hasClass('SimpleSelect_new') )
				return;
			
			
			if ( $('#<?=$cssId?>').data('addingNew') != true )
			{
				addNewUrl = "<?= $language ? $language . '/' : '' ?>abm/create/<?= $model ?>?forceLanguage=<?= $language ?>";
				
				<?php if ( is_array($addNew) ) : ?>
					<?php foreach ($addNew as $targetField => $localField) : ?>
						addNewUrl += '&<?= $targetField ?>=' + $('#<?= $localField ?>').val();
					<?php endforeach; ?>
				<?php endif; ?>
				
				$('#<?=$cssId?>').data('addingNew', true);
				$.prettyPhoto.openNewWithState(addNewUrl);
			}
		});

		$('#abm').bind('onAbmRefresh', function (event) { 
			if ( $('#abm #<?=$cssId?>').data('addingNew') )
			{
				$this = $('#abm select#<?=$cssId?>');
				$('option', $this).not('.SimpleSelect_new').remove();
				
				$option = $('<option></option>').addClass('loading');
				$option.text('<?= lang('magico_abm_loading') ?>');
				//$option.attr('selected', true);
				$option.insertBefore($('#abm #<?= $cssId?> .SimpleSelect_new'));
				$this.val($option);
				$this.attr('disabled', true);
				
				<?php if ( is_array($addNew) ) : ?>
					<?php foreach ($addNew as $targetField => $localField) : ?>
						sendData = { 'where' : $('#<?= $localField ?>').val() };	
					<?php endforeach; ?>
				<?php else : ?>
				sendData = {};	
				<?php endif; ?>

				$.ajax({
					url: '<?= $ajaxUrl ?>',
					dataType: 'json',
					type: 'POST',
					data : sendData,
					context: $this,
					success: function(data) {
						$('#abm select#<?=$cssId?> .loading').remove();
						
						for ( i = 0 ; i < data.length ; i++ )
						{	
							$option = $('<option></option>');
							$option.attr('value', data[i].id);
							$option.html( data[i].title );
							$option.insertBefore($('#<?= $cssId?> .SimpleSelect_new'));
						}
						
						$(this).attr('disabled', false);
						$(this).val($option.attr('value'));
						$(this).change();
					},
					error: function() {
						alert('<?= lang('magico_abm_error') ?>');
					}
				});


				$('#abm #<?=$cssId?>').data('addingNew', null);
			}
		} );
	});
	</script>
<?php endif; ?>
