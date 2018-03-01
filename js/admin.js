// отображаем окно добавления метки
jQuery( ".oiyamaps__button" ).on( 'click', function () {
	jQuery( ".oiyamaps__form" ).show();
} );

// закрываем окно добавления метки
function close_oi_yamaps() {
	jQuery( ".oiyamaps__form form" )[ 0 ].reset();
	jQuery( ".oiyamaps__form" ).hide();
	return false;
}

function oiyamaps_get_place( obj ) // получение координат по указанному адресу
{
	var place  = jQuery( obj ).val();
	var parent = jQuery( obj ).closest( '.oiyamaps__form-group' );
	var data   = {
		// вызываемая php функция
		'action' : 'oiyamaps_get_place',
		// передаваемый параметр
		'place' : place
	};

	parent.addClass( 'oiyamaps__preloader' );
	jQuery.post( ajaxurl, data, function ( response ) {

		// помещаем полученный результат от функции php в нужное место
		jQuery( '.oiyamaps__form [name=address]' ).val( response[ 0 ] );
		jQuery( '.oiyamaps__form [name=coordinates]' ).val( response[ 1 ] );
		parent.removeClass( 'oiyamaps__preloader' );
	} );
}

// обработка ввода адреса или координат
jQuery( '.oiyamaps__form [name=address]' ).on( 'change', function () {

	oiyamaps_get_place( this );
} );


function oi_yamaps_tabs( obj ) {
	jQuery( ".oiyamaps__form .media-menu-item" ).removeClass( "active" );
	jQuery( obj ).addClass( "active" );
	jQuery( ".media-frame-content-box" ).hide();
	jQuery( ".media-frame-content-box." + jQuery( obj ).data( "block" ) ).show();
}

jQuery( ".oiyamaps__form .media-menu-item" ).on( 'click', function () {
	oi_yamaps_tabs( this );
} );

function insert_oi_yamaps( attr ) {
	var data                 = jQuery( ".oiyamaps__form form" ).serializeArray();
	var obj                  = [];
	var shortcode            = [];
	shortcode[ 'map' ]       = '';
	shortcode[ 'placemark' ] = '';
	// проход по массиву собранному из формы
	for ( var i = 0, l = data.length; i < l; i++ ) {
		// если значение не пустое
		if ( data[ i ].value != '' ) {
			if ( jQuery.inArray( data[ i ].name, [ 'center', 'height', 'width', 'zoom' ] ) >= 0 ) {
				shortcode[ 'map' ] += ' ' + data[ i ].name + '="' + data[ i ].value + '"';
			} else {
				shortcode[ 'placemark' ] += ' ' + data[ i ].name + '="' + data[ i ].value + '"';
			}
			obj[ data[ i ].name ] = data[ i ].value;
		}
	}
	shortcode[ 'map' ]       = '[showyamap' + shortcode[ 'map' ] + ']';
	shortcode[ 'placemark' ] = '[placemark' + shortcode[ 'placemark' ] + '/]';
	shortcode[ 'map' ]       = shortcode[ 'map' ] + "\n" + shortcode[ 'placemark' ] + "\n" + '[/showyamap]';
	//console.log(shortcode);
	window.send_to_editor( shortcode[ attr ] );
	close_oi_yamaps();
}