<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>
<link href="<?= MAGICO_PATH_CSS ?>/fileuploader.css" rel="stylesheet" type="text/css">	
<div id="<?= $name ?>"></div>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<script>        
	$(function() {
		
		<?php
			$ci =& get_instance();
			$lang = $ci->lang->getAdminLanguage();
		?>
		
		$LAB.script('<?= base_url(MAGICO_PATH_JS) ?>/fileuploader-<?= $lang ?>.js').wait().script('<?= base_url(MAGICO_PATH_JS) ?>/fileuploader.js').wait( function() {
			
			var arrAllowedExtensions = new Array();
			<?php if ( count($allowedExtensions) ) : ?>
				<?php foreach ( $allowedExtensions as $ext ) : ?>
				arrAllowedExtensions.push("<?=$ext?>");
				<?php endforeach;?>
			<?php endif; ?>


			var arrPreUploadedFiles = {};
			<?php if ( count($preUploadedFiles) ) : ?>
				<?php foreach ( $preUploadedFiles as $key => $file ) : ?>
				arrPreUploadedFiles[<?=$key?>] = <?=$file?>;
				<?php endforeach;?>
			<?php endif; ?>

			var uploader = new qq.FileUploader({
				element: $('#<?= $name ?>')[0],
				action: '<?= site_url("abm/ajaxFieldCallBack/$type/$name") ?>',
				debug: true,
				maxFilesAllowed: <?= $maxFilesAllowed ?>,
				hasDescription: <?= $hasDescription ? "'$hasDescription :'" : 'false' ?>,
				preUploadedFiles: arrPreUploadedFiles,
				allowedExtensions: arrAllowedExtensions
			});


			if ($('body').data('fileUploadEvent'))
			{
				e = $('body').data('fileUploadEvent');
				uploader._uploadFileList(e.dataTransfer.files);
				$('body').data('fileUploadEvent',null);
			}

			$('div#<?=$name?> ul.qq-upload-list').sortable({
				update: function (event, ui) {
					inst = $(this);

					ajaxData = { 'order' : true, 'ids' : [] };

					$('li', inst).each( function () {
						ajaxData.ids.push( $('input[name="files[]"]', $(this)).val() );
					});

					$.ajax({
						data: ajaxData,
						url: '<?= site_url("abm/ajaxFieldCallBack/$type/$name") ?>'
					})
				}
			});			
		});
	});
</script>