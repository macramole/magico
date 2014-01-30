<?php
/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

global $CFG;

$magico_nav = $CFG->item('magico_nav');
$enableFacebook = $CFG->item('magico_enable_facebook');
$magico_customList = $CFG->item('magico_customList');
?>
<?php if ( AdminUser::isLogged() ) : ?>
	<div id="adminNavWrapper">
		<div id="adminNav">
			<div class="adminDrag"></div>
			<ul>
				<li><img src="<?= MAGICO_PATH_IMG ?>add.png" rel="add" /></li>
				<li class="edit"><img src="<?= MAGICO_PATH_IMG ?>/edit.png" title="<?= lang('magico_nav_edit') ?>" /></li>
				<li class="delete"><img src="<?= MAGICO_PATH_IMG ?>/trash.png" title="<?= lang('magico_nav_delete') ?>" /></li>
				<li><img src="<?= MAGICO_PATH_IMG ?>/process.png" rel="settings" /></li>
				<li class="logout"><img src="<?= MAGICO_PATH_IMG ?>/logout.png" title="<?= lang('magico_nav_logout') ?>" /></li>
			</ul>
			<div class="invisible" title="Hide"></div>
		</div>
		<div id="adminNavItems">
			<ul class="add">
				<li class="title"><?= lang('magico_nav_new') ?></li>
				<?php foreach ( $magico_nav as $model_name => $item ) : ?>
					<?php if ( !$item['noAdd'] && $this->adminuser->tienePermiso($model_name) ) : ?>
						<li class="item">
							<a href="<?= site_url('abm/create/' . $model_name) ?>" title=""><?= $item['title'] ? $item['title'] : $model_name ?></a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<ul class="settings">
				<li class="title"><?= lang('magico_nav_settings') ?></li>
				<li class="item">
					<a href="<?= site_url('abm/edit/Admin/' . $this->adminuser->getId() ) ?>" title=""><?= lang('magico_nav_edit_admin') ?></a>
				</li>
				
				<?php if ( $this->adminuser->tienePermiso('Admin') ) : ?>
				<li class="item">
					<a href="<?= site_url('abm/listContent/Admin') ?>" title=""><?= lang('magico_nav_admins') ?></a>
				</li>
				<?php endif; ?>
				
				<li class="title"><?= lang('magico_nav_list') ?></li>
				<?php foreach ( $magico_customList as $key => $listado ) : ?>
				<li class="item">
					<a href="<?= site_url("abm/customList/$key") ?>" title=""><?= $listado['title'] ?></a>
				</li>
				<?php endforeach; ?>
				<?php foreach ( $magico_nav as $model_name => $item ) : ?>
					<?php if ( $this->adminuser->tienePermiso($model_name) ) : ?>
					<li class="item">
						<a href="<?= site_url('abm/listContent/' . $model_name) ?>" title=""><?= $item['title'] ? $item['title'] : $model_name ?></a>
					</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php if ($enableFacebook) : ?>
		<div id="fb-root"></div>
		<script>
		window.fbAsyncInit = function() {
			FB.init({
			appId      : '<?= $enableFacebook ?>', // App ID
			status     : true, 
			cookie     : true, 
			xfbml      : true
			});
		};

		// Load the SDK Asynchronously
		(function(d){
			var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//connect.facebook.net/en_US/all.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
		</script>
	<?php endif; ?>
		
	<?php foreach ( $messages as $message ) : ?>
	<div class="jGrowlMessage" style="display: none">
		<?= $message ?>
	</div>
	<?php endforeach; ?>
<?php endif; ?>
