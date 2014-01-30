<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
?>
<?php if ( AdminUser::isLogged() ) : ?>
<style>	div.pp_details { display: none !important; } </style>
<div id="abm" class="list customList">
	<h1>
		<?= lang('magico_abm_list') ?><?= $listadoConfig['title'] ?>
	</h1>
	<input type="text" id="listSearch" />
	<div class="fieldsWrapper">
		<table>
			<thead>
				<tr>
					<?php foreach ( $fields as $field ) : ?>
					<?php if ( array_search($field, $listadoConfig['hiddenFields']) === false) : ?>
						<th><?= $field ?></th>
					<?php endif; ?>
					<?php endforeach; ?>
					<?php if ( $listadoConfig['sqlAction'] ) : ?>
					<th><?= lang('magico_list_actions') ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $listado as $row ) : ?>
				<tr>
					<?php foreach ( $row as $fieldName => $value ) : ?>
						<?php if ( array_search($fieldName, $listadoConfig['hiddenFields']) === false) : ?>
							<td><?=$value?></td>
						<?php endif; ?>
					<?php endforeach; ?>
					<td class="actions">
						<?php if ( $listadoConfig['sqlAction'] ) : ?>
						<a href="<?= site_url("abm/customListAction/$listKey/" . $row[ $listadoConfig['actionParamField'] ]) ?>">
							<img src="<?= base_url() ?>images/magico/customList_32.png" title="<?= $listadoConfig['actionName'] ? $listadoConfig['actionName'] : '' ?>" />
						</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="buttons">
		<input class='input-submit' type="button" value="<?= lang('magico_abm_close') ?>" id="btnClose" />
	</div>
	<script type="text/javascript">
		$( function() {
			$('#btnClose').click( function() {
				if ( $('body').data('needReload') )
				{
					$('body').data('needReload', false);
					$(this).val('<?= lang('magico_abm_closing') ?>');
					window.location.reload(true);
				}
				else
					$.prettyPhoto.close();
			});
			
			//Búsqueda
			$('#listSearch').keyup( function() {
				$(".fieldsWrapper tbody tr").hide();

				$(".fieldsWrapper tbody tr").each( function( index, item ) {
					if ( $(item).text().toLowerCase().indexOf( $('#listSearch').val() ) != -1 )
					{
						$(item).show();
					}
				});
			});
		} );
	</script>
</div>
<?php endif; ?>