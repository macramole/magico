<section id="parlebooLogin">
	<script src="<?= MAGICO_PATH_JS ?>/magico_login.js" type="text/javascript" charset="utf-8"></script>
	<script src="<?= MAGICO_PATH_JS ?>/jquery.animate-shadow-min.js" type="text/javascript" charset="utf-8"></script>
	<link rel="stylesheet" href="<?= MAGICO_PATH_CSS ?>/magico.css" type="text/css" charset="utf-8" />
	
	<div class="overlay"></div>
	
	<div class="recuadroBlanco">
		<div class="loginWrapper">
			<img src="<?= MAGICO_PATH_IMG ?>/logo_parleboo.png" class="logo" />
			<form>
				<fieldset>
					<input name="user" type="text" value="<?= lang('magico_login_username') ?>" />
					<input name="password" type="password" class="password" />
					<input name="password_foo" type="text" value="<?= lang('magico_login_password') ?>" />
					<div class="remember">
						<input type="checkbox" name="remember" id="remember" />
						<label for="remember"><?= lang('magico_login_remember') ?></label>
					</div>
					<!--<p>
						Usuario: demo </br>
						Contraseña: demo
					</p>-->
					<button type="submit" data-magico-sending="<?= lang('magico_login_sending') ?>" data-magico-redirecting="<?= lang('magico_abm_redirecting') ?>"><?= lang('magico_login_send') ?></button>
				</fieldset>
			</form>			
		</div>
	</div>
</section>
