<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<?php $fieldBuscar->render(); ?><?php $fieldCiudad->render(); ?><?php $fieldBarrio->render(); ?><button id="<?= $name ?>_buscar" type="button">Buscar</button>
<input type="hidden" name="<?= $name ?>_latitud" id="<?= $name ?>_latitud" value="<?= $latitud?>" />
<input type="hidden" name="<?= $name ?>_longitud" id="<?= $name ?>_longitud" value="<?= $longitud ?>" />
<div id="<?= $name ?>_map" class="map"></div>
<?php if ($helptext) : ?>
<div class="helptext"><?= $helptext ?></div>
<?php endif; ?>
<script>
var divMap = $('#<?= $name ?>_map'); 
var gMap = null;
var marker = null;
		
function buscarDireccion()
{
	var direccion = $('input[name="<?= $fieldBuscar->name ?>"]').val() + ', ' + 
					$('select[name="<?= $fieldBarrio->name ?>"] option:selected').text() + ', ' + 
					$('select[name="<?= $fieldCiudad->name ?>"] option:selected').text();
					
	geoCoder = new google.maps.Geocoder();
	geoResult = geoCoder.geocode({address: direccion}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			lugar = results[0].geometry.location;
			gMap.setCenter(lugar);
			
			if (marker)
				marker.setPosition(lugar);
			else
			{
				marker = new google.maps.Marker({
					map: gMap,
					position: lugar
				});
			}
			
			$('#<?= $name ?>_latitud').val(lugar.lat())
			$('#<?= $name ?>_longitud').val(lugar.lng())
			
			divMap.animate({'opacity' : 1});
		} else {
			if ( status == 'ZERO_RESULTS' )
				alert("No se puede encontrar la dirección");
			else
				alert("Hubo un problema intentando buscar la dirección: " + status);
		}
	});
}
	
$( function() {	
	$LAB.script('https://maps.googleapis.com/maps/api/js?sensor=false').wait(function() {
		
		$('#<?= $name ?>_buscar').click( function() {
		
			if ( !divMap.is(':visible') )
			{
				divMap.css('opacity', 0);
				divMap.css('display', 'block');

				gMap = new google.maps.Map(document.getElementById('<?= $name ?>_map'), {
					zoom: 13,
					center: new google.maps.LatLng(-34.397, 150.644),
					mapTypeId: google.maps.MapTypeId.ROADMAP
				});
			}

			buscarDireccion();
		});

		$('#<?= $fieldCiudad->name ?>').change( function( ) {
			$this = $(this);
			if ( $this.val() > 0 )
			{
				$('#<?= $fieldBarrio->name ?> option').not('.SimpleSelect_new').remove();

				$.ajax({
					url: '<?= $ajaxUrl ?>',
					dataType: 'json',
					type: 'POST',
					data: { 'where' : $this.val() },
					success: function(data) {
						for ( i = 0 ; i < data.length ; i++ )
						{	
							$option = $('<option></option>');
							$option.attr('value', data[i].id);
							$option.html( data[i].title );
							$option.insertBefore($('#<?= $fieldBarrio->name ?> .SimpleSelect_new'));
						}

						$('#<?= $fieldBarrio->name ?>').val( $('#<?= $fieldBarrio->name ?> option').first() );
					},
					error: function() {
						alert('<?= lang('magico_abm_error') ?>');
					}
				});
			}
		});
	});
});
</script>