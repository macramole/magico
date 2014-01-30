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
<div id="abm" class="<?= $model->getOperation() == MY_Model::OPERATION_CREATE ? 'new' : 'edit' ?> abm">
	<script type="text/javascript">
		$( function() {
			
			$('#abm.new ul.languages li').not('.active').click( function() {
				$.prettyPhoto.replaceOpen( $(this).attr('rel') + '/abm/create/<?= get_class($model) ?>' );
			});
			
			$('#abm.edit ul.languages li').not('.active').click( function() {
				$.prettyPhoto.replaceOpen( $(this).attr('rel') + '/abm/edit/<?= get_class($model) ?>/<?=$model->id?>' );
			});
			
			$('#btnDelete').click( function() {
				if ( confirm('<?= lang('magico_abm_delete_confirmation'); ?>') )
				{
					$('#btnDelete').val('<?= lang('magico_abm_deleting'); ?>');
					$('#btnDelete').attr('disabled','disabled');
							
					lang = $('#abm ul.languages li.active').attr('rel');
					lang = lang ? lang + '/' : '';
					
					$.ajax({
						url: lang + 'abm/delete/<?= get_class($model)?>/<?=$model->id?>',
						dataType: 'json',
						success: function (data) {
							if ( !data.need_confirmation )
							{
								$('#btnDelete').val('<?= lang('magico_abm_done'); ?>');

								if ( pp_images.length == 1 )
								{
									$.prettyPhoto.close();
									window.location.reload(true);
								}
								else
								{
									showMessage('<?= lang('magico_abm_delete_successful'); ?>');
									$('body').data('needReload', true);
									$.prettyPhoto.changePage('previous');
									pp_images.pop();
								}
							}
							else
							{
								if ( confirm('ATENCIÓN: Este contenido tiene elementos asociados. ¿ Estás seguro que querés eliminar este contenido y todo su contenido asociado ?') )
								{
									$.ajax({
										url: lang + 'abm/delete/<?= get_class($model)?>/<?=$model->id?>' + '/true',
										success: function () {			
											$('#btnDelete').val('<?= lang('magico_abm_done'); ?>');

											if ( pp_images.length == 1 )
											{
												$.prettyPhoto.close();
												window.location.reload(true);
											}
											else
											{
												showMessage('<?= lang('magico_abm_delete_successful'); ?>');
												$('body').data('needReload', true);
												$.prettyPhoto.changePage('previous');
												pp_images.pop();
											}
										},
										error: function() {
											alert('<?= lang('magico_abm_error'); ?>');
											$('#btnDelete').removeAttr('disabled');
											$('#btnDelete').val('Delete');
										}
									});
								}
							}
						},
						error: function() {
							alert('<?= lang('magico_abm_error'); ?>');
							$('#btnDelete').removeAttr('disabled');
							$('#btnDelete').val('Delete');
						}
					});
				}
			});
			
			$('#createContent, #saveContent, #addAnother, #createFacebook').click( function (e) {
				
				//e.preventDefault();
				
				$('#abmForm').data('submittedBy', $(this));
				$('#abmForm').data('submittedBy').data('lastName', $('#abmForm').data('submittedBy').val());
				$('#abmForm').data('submittedBy').attr('disabled','disabled');
				$('#abmForm').data('submittedBy').val('<?= lang('magico_abm_saving'); ?>');
				
				$.ajax({
					url: $('#abmForm').attr('action'),
					type: 'POST',
					dataType: 'json',
					data: $('#abmForm').serialize(),
					success: function(responseText) {

						if ( !responseText.errors )
						{
							//Si fue llamado directo o si viene de una lista
							if ( pp_images.length == 1 )
							{
								switch($('#abmForm').data('submittedBy').attr('name'))
								{
									case 'saveContent' :
										$('#abmForm').data('submittedBy').val('<?= lang('magico_abm_redirecting'); ?>');
										
										$.ajax('abm/addMessage/<?= lang('magico_abm_edit_successful'); ?>', 
											{ success: function() { window.location.reload(true); } });
									break;
									
									case 'createContent' :
									
										$('#abmForm').data('submittedBy').val('<?= lang('magico_abm_redirecting'); ?>');
										$.ajax('abm/addMessage/<?= lang('magico_abm_new_successful'); ?>', 
											   { success: function() { window.location = responseText.returnUrl; } });
										
										//window.location.reload(true);
										
									break;
									
									case 'addAnother' :
										
										showMessage('<?= lang('magico_abm_new_successful'); ?>');
										$.prettyPhoto.open();
										
									break;
									
									case 'createFacebook' :
										
										$('#abmForm').data('submittedBy').val('<?= lang('magico_abm_publishing_fb'); ?>');
										
										FB.login(function(response) {
											if (response.authResponse) {
												
												FB.api('/me/accounts', function(response) {
													var access_token = '';
													var app_id = '';
													
													for ( i = 0 ; i < response.data.length ; i++ )
													{
														if ( response.data[i].id == '<?php global $CFG; echo $CFG->item('magico_facebook_page') ?>' )
														{
															access_token = response.data[i].access_token;
															app_id = response.data[i].id;
															break;
														}
													}
													
													if ( access_token != '' && app_id != '' )
													{
														FB.api('/' + app_id + '/feed', 'post', { 'link' : responseText.returnUrl , 'access_token' : access_token }, function(response){
															if (!response || response.error) {
																alert('Hubo un error:' + response.error.code);
																
																$.ajax('abm/addMessage/<?= lang('magico_abm_new_successful') . '. ' . lang('magico_abm_fb_error') ; ?>', 
																	{ success: function() { window.location = responseText.returnUrl; } });
															} else {

																$('#abmForm').data('submittedBy').val('<?= lang('magico_abm_redirecting'); ?>');
																$.ajax('abm/addMessage/<?= lang('magico_abm_new_fb_successful'); ?>', 
																	{ success: function() { window.location = responseText.returnUrl; } });

															}
														});
													}
													else
													{
														alert('Tenés que ser administrador de la página en Facebook para poder postear en ella');
														
														$.ajax('abm/addMessage/<?= lang('magico_abm_new_successful') . '. ' . lang('magico_abm_fb_error') ; ?>', 
															{ success: function() { window.location = responseText.returnUrl; } });
													}
												});
											}
										}, {scope: 'publish_actions,manage_pages,publish_stream,offline_access'});
										
									break;
								}
							}
							else
							{	
								switch($('#abmForm').data('submittedBy').attr('name'))
								{
									case 'saveContent' :
										
										<?php if ( !$model->isTranslating() ) : ?>
											showMessage('<?= lang('magico_abm_edit_successful'); ?>');
										<?php else : ?>
											showMessage('<?= lang('magico_abm_translate_successful'); ?>');
										<?php endif; ?>
											
										$('body').data('needReload', true);
										$.prettyPhoto.changePage('previous');
										pp_images.pop();
										
									break;
									
									case 'createContent' :
										
										showMessage('<?= lang('magico_abm_new_successful'); ?>');
										$('body').data('needReload', true);
										
										$.prettyPhoto.changePage('previous');
										pp_images.pop();
										
									break;
									
									case 'addAnother' :
									
										showMessage('<?= lang('magico_abm_new_successful'); ?>');
										$.prettyPhoto.open();
										
									break;
								}
							}
						}
						else
						{
							$('#abmForm').data('submittedBy').removeAttr('disabled');
							$('#abmForm').data('submittedBy').val($('#abmForm').data('submittedBy').data('lastName'));
							
							$('html, body').animate({scrollTop: 0});
							
							$.each( responseText.errors, function(index, value) {							
								inputError = $('#abm [name="' + index + '"], #' + index);
								tdError = $('#abm #field_' + index);

								if ( inputError.is('input:visible') )
									inputError.animate({'box-shadow' : '0 0 3px 2px #FF3838'});

								$('<div></div>').addClass('error').text(value).css('top', tdError.position().top + 4).hide().appendTo(tdError).fadeIn();
								//alert(index + ': ' + value);

								if ( inputError.is('input:visible') )
								{
									inputError.focus( function () {
										$(this).animate({'box-shadow' : '0 0 0 0 #FF3838'});
										$(this).siblings('.error').fadeOut('normal', function() {
											$(this).detach();
										});
										$(this).unbind('focus');
									});
								}
								else
								{
									tdError.mouseover( function () {
										$(this).children('.error').fadeOut('normal', function() {
											$(this).detach();
										});
										$(this).unbind('focus');
									});
								}
							});
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						alert('<?= lang('magico_abm_error'); ?>');
						$('#abmForm').data('submittedBy').removeAttr('disabled');
						$('#abmForm').data('submittedBy').val($('#abmForm').data('submittedBy').data('lastName'));
					}
				});
				
				return false;
			});
			
			
			$('#btnCancel').click( function() {
				if ( pp_images.length == 1 )
					$.prettyPhoto.close();
				else
				{
					$.prettyPhoto.changePage('previous');
					pp_images.pop();
				}
			});
		});
	</script>
	<h1>
		<?= $model->getOperation() == MY_Model::OPERATION_CREATE ? lang('magico_abm_new') : ( $model->isTranslating() ? lang('magico_abm_translating') : lang('magico_abm_edit') ); ?>
		<?= $model::$name; ?>
		
		<?php if ( $model::$i18n ) : ?>
			<?php $contentLanguage = $this->uri->segment(1); ?>
			<ul class="languages">
				<?php foreach( array_keys($this->lang->languages) as $lang ) : ?>
					<?php $langActive = ( $lang == $contentLanguage ) ? 'active' : '' ?>
					<li rel="<?= $lang?>" class="<?= $langActive ?>">
						<img src="<?= MAGICO_PATH_IMG ?>languages/<?= $lang?>.png" />
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</h1>
	<?= form_open( site_url( ( $contentLanguage ? $contentLanguage . '/' : '' ) . 'abm/update/' . get_class($model) . ( $model->getOperation() == MY_Model::OPERATION_EDIT ? "/{$model->id}" : '' )), 
				   array('id' => 'abmForm') )  ?>
		<div class="fieldsWrapper">
			<table class="fields">
				<?php foreach( $model->fields as $field ) : ?>
					<?php if ( !$field->disabled ) : ?>
					<tr>
						<td class="title"><?= $field->label ?>:</td>
						<td class="field field_<?= get_class($field) ?>" id="field_<?= $field->name ?>" >
							<?php $field->render() ?>
							<?php if ( $model::$i18n === true || ( is_array($model::$i18n) && in_array( $field->name, $model::$i18n ) ) ) : ?>
							<abbr class="noTranslate" title="<?= lang('magico_field_not_i18n_desc') ?>" >* <?= lang('magico_field_not_i18n') ?></abbr>
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
		<div class="buttons">
		<?php if ($model->getOperation() == MY_Model::OPERATION_CREATE ) : ?>
			<input type="button" value="<?= lang('magico_abm_create'); ?>" name="createContent" id="createContent">
			<input type="button" value="<?= lang('magico_abm_create_another'); ?>" name="addAnother" id="addAnother">
			<?php if ( $CFG->item('magico_enable_facebook') && $model->hayPaginaIndividual ) : ?>
			<input type="button" value="<?= lang('magico_abm_create_facebook'); ?>" name="createFacebook" id="createFacebook">
			<?php endif; ?>
		<?php else : ?>
			<input type="button" value="<?= !$model->isTranslating() ? lang('magico_abm_edit') : lang('magico_abm_translate'); ?>" name="saveContent" id="saveContent">
			
			<?php if ( !$model->isTranslating() ) : ?>
				<input type="button" value="<?= lang('magico_abm_delete'); ?>" id="btnDelete">
			<?php endif; ?>
		<?php endif; ?>
			<input type="button" value="<?= lang('magico_abm_cancel'); ?>" id="btnCancel" />
		</div>
	</form>
	<div class="clear"></div>
</div>
<?php endif; ?>
