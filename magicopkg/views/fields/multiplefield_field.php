<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<li>
	<table>
		<tr>
		<?php foreach ( $fields as $fieldName => $field ) : ?>
			<?php $field->value = $row[$fieldName] ? $row[$fieldName] : $field->value ; ?>
			<?php $field->cssId = "{$fieldName}_{$rowNum}"; ?>
			<th><?php renderLabel($field->label); ?></th>
			<td id="field_<?= $field->cssId ?>"><?php $field->render(); ?></td>
		<?php endforeach; ?>
			<td class="dragMe"><img src="<?= site_url('magicopkg/images/move_icon_white.gif') ?>" /></td>
			<td class="delete"><img src="<?= site_url('magicopkg/images/delete_16.png') ?>" /></td>
		</tr>
	</table>
</li>				