<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="<?= $name ?>">
	<ul class="database-checklist">
        <?php foreach ($arrValues as $value) : ?>
        <li>
            <input type="checkbox" name="<?=$name?>[]" value="<?= $value['id'] ?>" <?= $value['selected'] ? 'checked="checked"' : '' ?> />
            <label><?= $value['value'] ?></label>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($helptext) : ?>
    <div class="helptext"><?= $helptext ?></div>
    <?php endif; ?>
</div>
<script>
	$(function() {
		$('#<?= $name ?> .database-checklist label').click( function() {
			$(this).prev().click();
		});
	});
</script>
