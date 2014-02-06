<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<input type="hidden" name="<?= $name ?>" value="<?= $id ?>" />

<?php if ( !$noEdit ) : ?>
<button type="button" class="addAnother"><?= lang('magico_abm_add_another') . ' ' . strtolower($model::$name) ?></button>
<?php endif; ?>
<ul class="wrapper <?= $sortable ? 'sortable' : '' ?>">
	<?php include('foreignmodel_items.php');	?> 
</ul>

<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<script>
	<?php $jsField = "$('#field_$name')"; ?>
		
	$(function() {
		
		if ( $('ul.wrapper li', <?= $jsField ?>).length > 0 )
		{
			 $('ul.wrapper', <?= $jsField ?>).css('display','block');
		}
		
		$('.addAnother', <?= $jsField ?>).click( function() {
			if ( <?= $jsField ?>.data('addingNew') != true ) {
				addNewUrl = '<?= $language ? $language . '/' : '' ?>abm/create/<?= $model ?>?<?= "$foreignSelect_id=$id&$foreignSelect_title=$title&forceLanguage=$language"  ?>';
				<?= $jsField ?>.data('addingNew', true);
				$.prettyPhoto.openNewWithState(addNewUrl);
			}
		});
		
		$('#abm').bind('onAbmRefresh', function (event) { 
			if ( <?= $jsField ?>.data('addingNew') )
			{
				$('ul.wrapper', <?= $jsField ?>).load( '<?= $ajaxUrl?>', { id : "<?= $id ?>", action : 'list' }, function() {
					if ( $('ul.wrapper li', <?= $jsField ?>).length > 0 )
					{
						$('ul.wrapper', <?= $jsField ?>).css('display','block');
					}
					<?= $jsField ?>.data('addingNew', false);
				} );
			}
		});
	});
</script>