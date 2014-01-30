/*
 Mâgico
 http://www.parleboo.com
 Copyright 2012 Leandro Garber <leandrogarber@gmail.com>
 Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
*/

var opacityChangeEnabled = true; //si hace la animación 
var sizeChangeEnabled = true;
var itemsShowEnable = true;
var draggingBackendUI = false;
var opacitySleep = 0.7; //en que opacidad esta normalmente
var minWidth, minHeight, imgWidth, imgMarginBottom;
var imgIsBig = false, navIsAwake = false;
var showingItems = false;

$(function() {
	//Load
	imgWidth = parseInt( $('#adminNav li').css('width') );
	imgMarginBottom = parseInt( $('#adminNav li').css('margin-bottom') );
	
	$('#adminNav ul li img').last().css('margin-bottom',0);
	
	
	minWidth = $('#adminNav').width();
	minHeight = $('#adminNav').height();
	
	adminNavLoadCookie();
	refreshWrapperPosition();

	$('#adminNavItems a').not('[rel="noPretty"]').prettyPhoto({ 
		animation_speed: 'slow',
		show_title: 'false',
		social_tools: ''
	});
	
	$('#adminNav').mousemove( function () {
		if ( opacityChangeEnabled && $(this).queue('fx') == 0 && !navIsAwake )
		{
			$(this).animate( {opacity: 1} );
			navIsAwake = true;
			
			$.cookie('adminNav_visible', '1', {expires: 365, path: '/'});
		}
		
	});
	
	$('#adminNavWrapper').mouseleave( function () {
		if ( opacityChangeEnabled && navIsAwake )
		{
			$('#adminNav').animate( {opacity: opacitySleep} );
			navIsAwake = false;
		}
	});
	
	$('#adminNav').draggable({ 
		handle: '.adminDrag',
		start: function () {
			opacityChangeEnabled = false;
			sizeChangeEnabled = false;
			itemsShowEnable = false;
			$(this).animate( {opacity: 1} );
		},
		stop: function () {
			opacityChangeEnabled = true;
			sizeChangeEnabled = true;
			itemsShowEnable = true;
			//refreshWrapperPosition();
			//refreshTutorialPosition();
			
			//Cookie position
			$.cookie('adminNav_left', $(this).css('left'), {expires: 365, path: '/'} );
			$.cookie('adminNav_top', $(this).css('top'), {expires: 365, path: '/'} );
		}
	});
	
	$('#adminNav .edit').droppable({
		tolerance: 'pointer',
		drop: function(event, ui) {
			language = ui.draggable.data('language') ? ui.draggable.data('language') + '/' : '';
			
			if ( $(ui.draggable).parent().hasClass('ui-sortable') ) 
			{
				$(ui.draggable).parent().addClass('magico_dropped');
			}
			
			
			$.prettyPhoto.open(language + 'abm/edit/' + ui.draggable.data('type') + '/' + ui.draggable.data('id') );
		}
	});
	
	$('#adminNav .edit').click( function() {
		if ( $('#adminNav').data('id') )
		{
			language = $('#adminNav').data('language') ? $('#adminNav').data('language') + '/' : '';
			
			$.prettyPhoto.open(language + 'abm/edit/' + $('#adminNav').data('type') + '/' + $('#adminNav').data('id') );
		}
	});
	
	//Trash droppable y que se muestre cuando se quiere dropear
	$('#adminNav .delete').droppable({
		tolerance: 'pointer',
		drop: function(event, ui) {
			if ( confirm('¿ Estás seguro que querés eliminar este contenido ?') )
			//if ( confirm('Are you sure you want to delete this content ?') )
			{
				language = ui.draggable.data('language') ? ui.draggable.data('language') + '/' : '';
				
				$.ajax({
					url: language + 'abm/delete/' + ui.draggable.data('type') + '/' + ui.draggable.data('id'),
					dataType: 'json',
					success: function (data) {			
						
						if ( !data.need_confirmation )
						{
							//showMessage('Content deleted successfully');
							showMessage('Contenido eliminado');
							ui.draggable.fadeOut('500', function() {
								if(typeof window.onDeleteContent == 'function') {
									onDeleteContent(ui.draggable);
								}
							});
						}
						else
						{
							if ( confirm('ATENCIÓN: Este contenido tiene elementos asociados. ¿ Estás seguro que querés eliminar este contenido y todo su contenido asociado ?') )
							{
								$.ajax({
									url: language + 'abm/delete/' + ui.draggable.data('type') + '/' + ui.draggable.data('id') + '/true',
									success: function () {			
										//showMessage('Content deleted successfully');
										showMessage('Contenido eliminado');
										window.location.reload(true);
									}
								});
							}
						}
						
						
					}
				});
			}
		}
	});
	
	$('#adminNav .delete').click( function() {
		if ( $('#adminNav').data('id') )
		{
			if ( confirm('¿ Estás seguro que querés eliminar este contenido ?') )
			{
				language = $('#adminNav').data('language') ? $('#adminNav').data('language') + '/' : '';
				
				$.ajax({
					url: language + 'abm/delete/' +  $('#adminNav').data('type') + '/' +  $('#adminNav').data('id'),
					dataType: 'json',
					success: function (data) {			
						if ( !data.need_confirmation )
						{
							//showMessage('Content deleted successfully');
							showMessage('Contenido eliminado');
							window.location.reload(true);
						}
						else
						{
							if ( confirm('ATENCIÓN: Este contenido tiene elementos asociados. ¿ Estás seguro que querés eliminar este contenido y todo su contenido asociado ?') )
							{
								$.ajax({
									url: language + 'abm/delete/' +$('#adminNav').data('type') + '/' + $('#adminNav').data('id') + '/true',
									success: function () {			
										//showMessage('Content deleted successfully');
										showMessage('Contenido eliminado');
										window.location.reload(true);
									}
								});
							}
						}
					}
				});
			}
			
		}
	});
	
	$('#adminNav .logout img').click( function () {
		//if ( confirm('Are you sure you want to logout?') )
		if ( confirm('¿Seguro que querés cerrar la sesión?') )
		{
			document.location.href = 'magico_logout';
		}
	});
	
	$('#adminNav ul li img').mousemove( function () {
		
		if (sizeChangeEnabled && $(this).queue('fx') == 0 && !imgIsBig )
		{		
			//Esto es true sólo cuando se esta droppeando algo sobre la barra (se llama a .mousemove() directamente)
			if ( !draggingBackendUI )
				$(this).addClass('big');
			else
				$(this).addClass('bigger');
			
			imgIsBig = true;
		}
	});
	
	$('#adminNav ul li img').mouseleave( function () {
		
		if (sizeChangeEnabled && imgIsBig )//&& !$(this).data('showingItems')  )
		{
			$(this).removeClass('big').removeClass('bigger');
			imgIsBig = false;
		}
	});
	
	//Drag
	$('.drag').parent().css('position', 'relative');
	$('.drag').parent().hover( function() {
		$('.drag',$(this)).stop(true).animate({'opacity': '1'});
	}, function() {
		$('.drag',$(this)).stop(true).animate({'opacity': '0.6'});
	});
	
	//Boton ocultar
	$('#adminNav .invisible').click( function () {
		navIsAwake = false;
		$('#adminNav').animate({'opacity': '0'});
		
		
		$.cookie('adminNav_visible', '0', {expires: 365, path: '/'});
	});
	
	$('#adminNav ul li img').mouseover(showItems);
	$('#adminNavWrapper').mouseleave(hideItems);
	$('#adminNav .drag').mouseover(hideItems);
	
	/*^********* TUTORIAL *********/
	
	/*checkTutorialState();
	refreshTutorialPosition();
	
	$('#magico_tutorial .botones .no').click(function(){
		$('#magico_tutorial_overlay').fadeOut();
		$('#magico_tutorial').fadeOut();
		$.cookie('magico_tutorial_disabled', '1', {expires: 365, path: '/'});
		itemsShowEnable = true;
	});
	
	$('#magico_tutorial .botones .si').click(function(){
		$paso = $(this).parents('.paso');
		$next = $paso.next();
		$paso.fadeOut();
		$next.fadeIn();
	});
	*/
	/*^*****************/
	
	//Touch Enabled
	/*Modernizr.load({
		test: Modernizr.touch,
		yep : 'jquery.ui.touch-punch.min.js'
	});*/
	
	//Messages
	$('.jGrowlMessage').each( function (index, item) {
		showMessage( $(item).text().trim() );
	} );
});


function showMessage( message )
{
	/*($('<div></div>').html(message).dialog({
		title: title,
		modal: true,
		buttons : {
			Ok : function() {
				$(this).dialog('close');
			}
		},
		show: 'fade',
		hide: 'fade'
	});*/
	
	$.jGrowl(message, {animateOpen: {opacity: 'show'}});
}

function showItems()
{
	if ( itemsShowEnable )
	{
		which = $(this).attr('rel');
	
		if ( which )
		{
			itemsLeft = parseInt($('#adminNav').css('left')) + parseInt($('#adminNav').outerWidth(true));

			imgOffset = $(this).offset();
			itemsTop = parseInt(imgOffset.top - $(window).scrollTop() );

			$('#adminNavItems ul').hide();
			$('#adminNavItems ul.' + which).show();
			$('#adminNavItems').css('left', itemsLeft + 20 );
			$('#adminNavItems').css('top', itemsTop );
			$('#adminNavItems').css('opacity',0).show().animate({opacity: 0.95});
			
			$('#adminNav ul li img').data('showingItems', false);
			$(this).data('showingItems', true);
			showingItems = true;
			
			refreshWrapperPosition();
		}
		else
		{
			hideItems();
		}
	}
}

function hideItems()
{
	$('#adminNavItems').hide('fade');
	$('#adminNav ul li img').data('showingItems', false);
	showingItems = false;
	refreshWrapperPosition();
}

function refreshWrapperPosition()
{
	$adminNavWrapper = $('#adminNavWrapper');
	
	adminNavWrapperWidth = $('#adminNav').outerWidth(true);
	adminNavWrapperHeight = $('#adminNav').outerHeight(true);
	
	if ( showingItems )
	{
		adminNavWrapperWidth += $('#adminNavItems').outerWidth(true);
	}
	
	$adminNavWrapper.offset( $('#adminNav').offset() );
	$adminNavWrapper.width( adminNavWrapperWidth );
	$adminNavWrapper.height( adminNavWrapperHeight );
}

function checkTutorialState()
{
	if ( $.cookie('magico_tutorial_disabled') )
		$('#magico_tutorial').css('display','none');
	else
		itemsShowEnable = false;
}

function refreshTutorialPosition()
{
	$magicoTutorial = $('#magico_tutorial');
	
	if ( $magicoTutorial.is(':visible') )
	{
		$adminNavWrapper = $('#adminNavWrapper');
		$magicoTutorial.css({'left' : parseInt ( $adminNavWrapper.css('left') ) + 70, 'top' : parseInt ( $adminNavWrapper.css('top') ) - 150});
		
		windowWidth = $(window).width();
		windowHeight = $(window).height();
		bodyHeight = $(document).height();
		finalHeight = bodyHeight > windowHeight ? bodyHeight : windowHeight;	
		
		$('#magico_tutorial_overlay').css('width', windowWidth).css('height', finalHeight);
	}
}

function disableWrapper()
{
	$('#adminNavWrapper').css('width', 'auto' );
	$('#adminNavWrapper').css('height', 'auto' );
}

//Se agranda lapiz y muestra y agranda tacho de basura
function showDroppableZones()
{
	$('#adminNav ul li.delete, #adminNav ul li.edit').show('fade');
	$('#adminNav ul li.delete img, #adminNav ul li.edit img').addClass('big');
	$('#adminNav ul li img').not('#adminNav ul li.edit img, #adminNav ul li.delete img').addClass('blured');
	
	opacityChangeEnabled = false;
	sizeChangeEnabled = false;
	itemsShowEnable = false;
}

//Se achica lapiz y se oculta el tacho
function hideDroppableZones()
{
	opacityChangeEnabled = true;
	sizeChangeEnabled = true;
	itemsShowEnable = true;
	
	$('#adminNav ul li img').not('#adminNav ul li.edit img, #adminNav ul li.delete img').removeClass('blured');
	$('#adminNav ul li.delete img, #adminNav ul li.edit img').removeClass('big bigger');
	
	if ( !$('#adminNav').data('id') )
	{
		$('#adminNav ul li.delete img, #adminNav ul li.edit img').removeClass('big bigger');
		$('#adminNav ul li.delete, #adminNav ul li.edit').hide();
	}
}

//Restorea la configuración del adminNav
function adminNavLoadCookie()
{
	if ( $.cookie('adminNav_left') )
		$('#adminNav').css('left', $.cookie('adminNav_left'));
	
	if ( $.cookie('adminNav_top') )
		$('#adminNav').css('top', $.cookie('adminNav_top'));
	
	if ( $.cookie('adminNav_visible') == null || $.cookie('adminNav_visible') == '1' )
		$('#adminNav').css('opacity', opacitySleep);
}

function magico_setMainData(id, content_type, language)
{
	$('#adminNav').data('id', id);
	$('#adminNav').data('type', content_type);
	
	if ( language != null )
			$('#adminNav').data('language', language);
	
	$('#adminNav .edit, #adminNav .delete').show();
}

/**
 * Le asigna el content_type, el id y el language (puede ser null) a los elementos que respondan al selector. Sirve para hacer la magia del drag and drop
 */
function magico_setData(ids, content_type, selector, language)
{
	$(selector).each( function ( index, item ) {
		$(item).data('type', content_type);
		$(item).data('id', ids[index]);
		
		if ( language != null )
			$(item).data('language', language);
		
	});
}

/**
 * USO INTERNO
 *
*/
function magico_prepareEditable(id, content_type, field, selector, config, language)
{
	$item = $(selector);
	
	$item.data('type', content_type);
	$item.data('id', id);
	$item.data('field', field);
	
	$divEditable = $('<div class="magicoEditable">Edición instantánea</div>');
	$divEditable.insertAfter($item);
	$item.data('magico_edicion_instantanea', $divEditable);
	
	//Mostrar los aloha editables
	$item.hover(function() {
		if ( !$(this).hasClass('cke_focus') )
		{
			offset = $(this).offset();
			offset.top -= 40;
			offset.left -= 10;
			$divEditable = $(this).data('magico_edicion_instantanea');
			$divEditable.stop(true).show().offset( offset ).animate({'opacity' : 0.9});
		}
	}, function() {
		if ( !$(this).hasClass('cke_focus') )
			$(this).data('magico_edicion_instantanea').stop(true).animate({'opacity' : 0},{'complete' : function() {$(this).hide()}});
			
	});
	
	$item.focus(function() {
		$(this).data('magico_edicion_instantanea').stop(true).css('opacity', 0).hide();
		$('body').trigger('magico-editable-activated', [ $(this) ]);
	});

	$item.blur(function() {
		$this = $(this);
		magico_saveFieldData( $this );
		$('body').trigger('magico-editable-saved', [ $this ]);
	});
	
	if ( language != null )
		$item.data('language', language);
	
	if ( !$item.attr('id') )
	{
		$item.attr('id', (new Date).getTime() + Math.floor(Math.random() * 10000));
	}
	
	$item.attr('contenteditable', true);
	
	CKEDITOR.inline($item.attr('id'), config);
}

/**
 * Para editar fields con ALOHA
 *
**/
function magico_setFieldEditable(id, content_type, field, selector, config, language)
{
	magico_prepareEditable(id, content_type, field, selector, config, language);
}

function magico_setFieldsEditables(arrIds, content_type, field, selector, config, language)
{	
	$(selector).each(function(index, item) {
		magico_prepareEditable(arrIds[index], content_type, field, item, config, language);
	});
}

/**
 * Esto es llamado cuando se editó un fieldEditable
 *
*/
function magico_saveFieldData( obj )
{
	obj = obj.clone(true);
	obj.children('.magicoEditable').remove();
	language = obj.data('language') ? obj.data('language') + '/' : '';
	
	$.ajax({
		url: language + 'abm/updateField/' +  obj.data('id') + '/' + obj.data('type') + '/' + obj.data('field'),
		type: 'POST',
		data: {data : obj.html()},
		success: function(data) {
			showMessage('Contenido guardado');
		}
	});
}

/*** Wraps de JqueryUI ***/

$.widget('ui.magico_sortable', $.ui.sortable, {
	_init: function() {
		this.element.children().css({position: 'relative', cursor: 'move'});
	},
	options : {
		addNoClick : false, //agrega clase noclick
		handle: '.drag',
		start: function (event, ui) {	
				if ( $(this).magico_sortable('option','addNoClick') )
					ui.item.addClass('noclick');
				
				
				showDroppableZones();
		},
		stop: function (event, ui) {
			hideDroppableZones();
		},
		update: function (event, ui) {
			liItems = '';
			inst = $(this);
			
			if ( inst.hasClass('magico_dropped') )
			{
				inst.removeClass('magico_dropped');
				inst.magico_sortable('cancel');
			}
			else
			{
				$(inst.magico_sortable('option','items'), inst).each( function (index, item) {
					liItems += $(item).data('id').trim() + '_';
				});

				$.ajax({
					url: 'abm/updateOrder/' + ui.item.data('type') + '/' + liItems,
					success: function() {
						showMessage('Orden actualizado');
						inst.trigger('magico_sortable_success');
					}
				});
			}
		}
	}
});

$.fn.magico_draggable = function() {
	$(this).children().draggable({
		start: function (event, ui) {
			showDroppableZones();
		},
		stop: function (event, ui) {
			hideDroppableZones();
		},
		handle: '.drag',
		revert: true
	});
};

$.fn.magico_add_drag = function() {
	$(this).prepend("<img src='magico/images/move_icon_white.gif' class='drag' />");
	
	$(this).hover( function() {
		$('.drag', $(this)).stop(true).animate({'opacity': '1'});
	}, function() {
		$('.drag', $(this)).animate({'opacity': '0.6'});
	});
}
