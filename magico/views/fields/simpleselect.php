<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php if ( !$forcedValue ) : ?>
<select class="input-select" name="<?=$name?>">
	<?php if ( $addDefaultOption ) : ?>
	<option value="0"><?=$addDefaultOption?></option>
	<?php endif; ?>
	
	<?php foreach ($arrValues as $option) : ?>
	<option value="<?=$option['id']?>" <?= $option['id'] == $value ? 'selected' : ''?>><?=$option['value'] ? $option['value'] : $option['title']?></option>
	<?php endforeach; ?>
</select>
<?php else : ?>
<div class="input-select input-select-forced">
	<span><?= $forcedValue['title'] ?></span>
	<input type="hidden" name="<?=$name?>" value="<?= $forcedValue['id'] ?>" />
</div>
<?php endif; ?>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
