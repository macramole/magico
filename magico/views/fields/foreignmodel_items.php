<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php if ( count($arrValues) ) : ?>
	<?php foreach ( $arrValues as $row ) : ?>
		<li rel="<?= $row['id'] ?>" class="<?= $row['translate'] ? 'translate' : 'sortable' ?>">
			<?php if ( $withImage ) : ?>
			<img class="imagen" src="<?= $row['imagen'] ?>" />
			<?php endif; ?>
			<div class="info">
				<?php if ($sortable && !$noEdit) : ?>
				<img class="dragMe" src="images/magico/move_icon_white.gif" />
				<?php endif; ?>
				<div class="title"><?= $row[$titleField] ?> <?= $row['translate'] ? '<strong>' . lang('magico_abm_not_translated') . '</strong>' : ''?></div>
				<div class="actions">
					<?php if ( !$noEdit ) : ?>
						<?php if ( !$row['translate'] ) : ?>
						<img class="edit" src="<?= MAGICO_PATH_IMG ?>edit_23.png" title="<?= lang('magico_abm_edit') ?>" />
						<img class="delete" src="<?= MAGICO_PATH_IMG ?>delete_23.png" title="<?= lang('magico_abm_delete') ?>" />
						<?php else : ?>
						<img class="translate" src="<?= MAGICO_PATH_IMG ?>translate_23.png" title="<?= lang('magico_abm_translate') ?>" />
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="clear"></div>
		</li>
	<?php endforeach; ?>
	<?php 
		if ( $sortable && !$noEdit )
			magico_setData($arrValues, $model, "#field_$name ul.wrapper li", MAGICO_SORTABLE, "{ handle : '', items : 'li.sortable' }", false, null) 
	?>
	<script>
		<?php $jsField = "$('#field_$name')"; ?>
		$( function() {
			$('img.delete', <?= $jsField ?>).click( function(){
				if ( confirm('¿ Estás seguro que querés eliminar este contenido ?') )
				{
					$li = $(this).parents('li');
					id = $li.attr('rel');

					$.post('<?= $ajaxUrl?>', { 'id' : id, action : 'delete' }, function() {
						$li.fadeOut(500, function() {
							$li.remove();

							if ( $('ul.wrapper li', <?= $jsField ?>).length == 0 )
							{
								$('ul.wrapper', <?= $jsField ?>).hide();
							}
						});
					});
				}
			});

			$('img.edit', <?= $jsField ?>).click( function(){
				if ( <?= $jsField ?>.data('addingNew') != true ) {
					id = $(this).parents('li').attr('rel');

					addNewUrl = '<?= $language ? $language . '/' : '' ?>abm/edit/<?= $model ?>/' + id + '?<?= "$foreignSelect_id=$id&$foreignSelect_title=$title"  ?>';
					<?= $jsField ?>.data('addingNew', true);
					$.prettyPhoto.openNewWithState(addNewUrl);
				}
			});

			$('img.translate', <?= $jsField ?>).click( function(){
				if ( <?= $jsField ?>.data('addingNew') != true ) {
					id = $(this).parents('li').attr('rel');

					addNewUrl = '<?= $language ? $language . '/' : '' ?>abm/edit/<?= $model ?>/' + id + '?<?= "$foreignSelect_id=$id&$foreignSelect_title=$title"  ?>';
					<?= $jsField ?>.data('addingNew', true);
					$.prettyPhoto.openNewWithState(addNewUrl);
				}
			});
		});
	</script>
<?php endif; ?>