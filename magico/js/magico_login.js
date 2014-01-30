$( function() {
	var default_name = $('section#parlebooLogin input[name="user"]').val();
	var default_pass = $('section#parlebooLogin input[name="password_foo"]').val();
	var default_btnlogin = $('section#parlebooLogin button').text();
	
	$('section#parlebooLogin input[name="user"]').focus( function() {
		$(this).val('');
		$(this).animate({'box-shadow' : '0 0 0 0 #FF3838'});
	});
	
	$('section#parlebooLogin input[name="user"]').blur( function() {
		if ( $(this).val() == '' )
			$(this).val(default_name)
	});
	
	$('section#parlebooLogin input[name="password_foo"]').focus( function() {
		$(this).hide();
		$('section#parlebooLogin input.password').show().focus();
		$(this).css({'box-shadow' : '0 0 0 0 #FF3838'});
	});
	
	$('section#parlebooLogin input.password').focus( function() {
		$(this).animate({'box-shadow' : '0 0 0 0 #FF3838'});
	});
	
	$('section#parlebooLogin input.password').blur( function() {
		if ( $(this).val() == '' )
		{
			$(this).hide();
			$('section#parlebooLogin input[name="password_foo"]').show();
		}
	});
	
	$('section#parlebooLogin input.password').keydown(function(e){
		if ( e.which == 13 )
		{
			$('section#parlebooLogin fieldset button').focus().click();
		}
			
	});
	
	$('section#parlebooLogin form').submit(function(e) {
		e.preventDefault();
		
		
		$this = $('button', $(this));
		$this.text( $this.attr('data-magico-sending') );
		$this.attr('disabled', true);
		
		$.ajax({
			url: 'magico_login',
			type: 'POST',
			data: $('section#parlebooLogin form').serialize(),
			dataType: 'json',
			success: function(data) {
				if ( data.error == true )
				{
					$('section#parlebooLogin input').not('[type="checkbox"]').animate({'box-shadow' : '0 0 3px 2px #FF3838'});
					$('section#parlebooLogin .loginWrapper').effect('shake', 500);
					
					$this.text(default_btnlogin);
					$this.attr('disabled', false);
				}
				
				if ( data.success )
				{
					$this.text( $this.attr('data-magico-redirecting') );
					document.location = $('base').attr('href');
				}
				
				
			}
		});
	});
	
	//Al clickear afuera se va
	$('section#parlebooLogin > .overlay').click(function(){
		$('section#parlebooLogin > .overlay, section#parlebooLogin > .recuadroBlanco').fadeOut({duration: 1500});
		location.href = '';
	});
	
	$(window).resize(function() {
		var loginWrapperPos = {};
	
		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		var bodyHeight = $('body').outerHeight();
		var finalHeight = bodyHeight > windowHeight ? bodyHeight : windowHeight;	
		
		$recuadro = $('section#parlebooLogin .recuadroBlanco');

		loginWrapperPos.left = windowWidth / 2 - $recuadro.outerWidth() / 2;
		loginWrapperPos.top = (windowHeight / 2 - $recuadro.outerHeight() / 2) * 0.7;

		$recuadro.css('left', loginWrapperPos.left);
		$recuadro.css('top', -1000);
		
		$('section#parlebooLogin, section#parlebooLogin .overlay').css('width', windowWidth).css('height', finalHeight);
		
		$recuadro.show().animate({'top' : loginWrapperPos.top});
	});
	
	$(window).load( function() {$(window).resize()} );
	
});
