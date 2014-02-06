<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="<?= $name ?>">
    <?php if ( $addNew ) : ?>
    <button class="database-checklist-new" type="button"><?= lang('magico_abm_create_new') ?></button>
    <?php endif; ?>
	<ul class="database-checklist">
        <?php foreach ($arrValues as $value) : ?>
        <li>
            <input type="checkbox" name="<?=$name?>[]" value="<?= $value['id'] ?>" <?= $value['selected'] ? 'checked="checked"' : '' ?> />
            <label><?= $value['title'] ?></label>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($helptext) : ?>
    <div class="helptext"><?= $helptext ?></div>
    <?php endif; ?>
</div>
<?php if ( $addNew ) : ?>
	<script type="text/javascript">
	$( function() {
		$('#<?= $name ?> button').click( function () {
			if ( $('#<?=$name?>').data('addingNew') != true )
			{
				addNewUrl = '<?= $language ? $language . '/' : '' ?>abm/create/<?= $model ?>?forceLanguage=<?= $language ?>';
				$('#<?=$name?>').data('addingNew', true);
				$.prettyPhoto.openNewWithState(addNewUrl);
			}
		});
		
		$('#<?= $name ?> label').click( function () {
			$input = $(this).siblings('input');
			$input.attr('checked', !$input.attr('checked') );
		});
		
		$('#abm').bind('onAbmRefresh', function (event) { 
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
	});
	</script>
<?php endif; ?>
