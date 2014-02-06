<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<textarea name="<?=$name?>"><?=$value?></textarea>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>