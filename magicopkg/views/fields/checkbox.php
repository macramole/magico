<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<input class="input-check" type="checkbox" name="<?=$name?>" <?= $value ? 'checked' : '' ?> value="1" />
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>