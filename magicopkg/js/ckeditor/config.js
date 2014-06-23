/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.extraPlugins = 'justify';
};


//Custom linker ! : D
CKEDITOR.on('dialogDefinition', function(e) {
    var
        dd = e.data.definition, // NOTE: this is an instance of CKEDITOR.dialog.definitionObject, not CKEDITOR.dialog.definition
        tabInfo;
 
    if (e.data.name === 'link')
    {
        dd.removeContents('advanced');
        dd.removeContents('target');
 
		
		tabInfo = dd.getContents('info');
        tabInfo.remove('url');
        tabInfo.remove('linkType');
        tabInfo.remove('browse');
        tabInfo.remove('protocol');
 
        
 
        tabInfo.add({
            type : 'text',
            id : 'urlNew',
            label : 'URL',
            setup : function(data)
            {	
				if (typeof(data.url) !== 'undefined')
                {
					if (typeof(data.url) !== 'undefined')
					{
						theProtocol = data.url.protocol ? data.url.protocol : '';
                        this.setValue(theProtocol + data.url.url);
					}
                }
            },
            commit : function(data)
            {	
				data.url = { url: this.getValue() };
				
				if ( data.url.url.match("^(.*http).*" ) )
				{
					data.url.protocol = data.url.url.substr( 0, data.url.url.indexOf('//') + 2 );
                    data.url.url = data.url.url.substr( data.url.url.indexOf('//') + 2 );
					data.target = { name: '_blank', type: 'magico' };
				} else {
                    data.url.protocol = '';
                }
                
                data.type = 'url';
            }
        });
		
		tabInfo.add({
			type : 'html',
			id: 'protocol',
			html: 'Para <strong>links externos</strong> escribir la url completa (ej: <strong>http://</strong>www.google.com.)<br /> Estos links se abriran en una ventana nueva<br /><br />\n\
				   Para <strong>links internos</strong> escribir <strong>s&oacute;lo</strong> la ruta (ej: novedades/titulo-de-la-novedad)<br />Estos links se abriran en la misma ventana.'
			
		});
		
		/*
        tabInfo.add({
            type : 'checkbox',
            id : 'newPage',
            label : 'Abrir en una nueva ventana',
            commit : function(data)
            {
                if (this.getValue())
                {
                    data.target = '_blank';
                }
                return data;
            }
        });*/
    }
});
