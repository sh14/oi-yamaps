(function ( $ ) {
	'use strict';


	// global variable for storing template blocks
	let cache = {};

	/**
	 * Function, that put the data to template block, and return complete HTML.
	 *
	 * @param str
	 * @param data
	 * @returns {Function}
	 */
	function tmpl( str, data ) {
		// Figure out if we're getting a template, or if we need to
		// load the template - and be sure to cache the result.
		let fn = !/\W/.test( str ) ?
			cache[ str ] = cache[ str ] ||
				tmpl( document.getElementById( str ).innerHTML ) :

			// Generate a reusable function that will serve as a template
			// generator (and which will be cached).
			new Function( "obj",
				"var p=[],print=function(){p.push.apply(p,arguments);};" +

				// Introduce the data as local variables using with(){}
				"with(obj){p.push('" +

				// Convert the template into pure JavaScript
				str
					.replace( /[\r\t\n]/g, " " )
					.split( "<%" ).join( "\t" )
					.replace( /((^|%>)[^\t]*)'/g, "$1\r" )
					.replace( /\t=(.*?)%>/g, "',$1,'" )
					.split( "\t" ).join( "');" )
					.split( "%>" ).join( "p.push('" )
					.split( "\r" ).join( "\\'" )
				+ "');}return p.join('');" );
		// Provide some basic currying to the user
		return data ? fn( data ) : fn;
	};

	/**
	 * Finds an element that has property and value that were given.
	 *
	 * @returns {jQuery}
	 */
	$.fn.closestProperty = function ( property, value ) {
		let closest = $( this );

		// look for needed element
		do {
			closest = closest.parent();
		} while ( closest.css( property ) !== value && closest.prop( 'tagName' ) !== 'BODY' );

		return closest;
	};


	/**
	 * scroll to an element, use it like:
	 * $('.element_selector').goTo(); OR $('.element_selector').goTo( 'overlay-y', 'scroll' );
	 *
	 * @returns {jQuery}
	 */
	$.fn.goTo = function ( property, value ) {
		let destination_element;

		// if property and value are given
		if ( property !== undefined && value !== undefined ) {

			// find an element that corresponds for request
			destination_element = $( this ).closestProperty( property, value );
		} else {
			destination_element = 'html, body';
		}

		// scroll to an element
		$( destination_element ).animate( {

			scrollTop : $( this ).offset().top + 'px'
		}, 'fast' );

		return this;
	};


	/**
	 * Serialize form to an object including empty fields.
	 *
	 * @param format
	 * @returns {*}
	 */
	$.fn.serializeObject = function ( format ) {
		let obj  = {};
		let arr2 = [];
		let val;

		$.each( $( this ).find( '[name]' ), function ( i, el ) {
			let that = $( el );
			val      = that.val();

			if ( that.attr( 'type' ) !== undefined && that.attr( 'type' ) === 'checkbox' ) {
				if ( that.prop( 'checked' ) ) {
					arr2.push( { name : that.attr( 'name' ), value : val } );
				} else {
					arr2.push( { name : that.attr( 'name' ), value : '' } );
				}
			} else {
				if ( that.attr( 'multiple' ) !== undefined ) {

					if ( val === null ) {
						arr2.push( { name : that.attr( 'name' ), value : '' } );
					} else {
						$.each( val, function ( j, v ) {
							arr2.push( { name : that.attr( 'name' ), value : v } );
						} );
					}
				} else {
					arr2.push( { name : that.attr( 'name' ), value : val } );
				}
			}
		} );

		let n = [];
		let s = [];
		let a = [];
		let out;

		// turn to associative array
		$.each( arr2, function () {
			if ( obj[ this.name ] ) {
				if ( !obj[ this.name ].push ) {
					obj[ this.name ] = [ obj[ this.name ] ];
				}
				obj[ this.name ].push( this.value || '' );
			} else {
				obj[ this.name ] = this.value || '';
			}
		} );


		if ( format === 'string' || format === 'array' || format === 'attributes' ) {

			// build query
			$.each( obj, function ( key, value ) {
				if ( Array.isArray( value ) === true ) {
					value = value.join( ',' );
				}
				n.push( { name : key, value : value } );
				s.push( key + '=' + value );
				a.push( key + '="' + value + '"' );
			} );
		}

		// choose output format
		switch ( format ) {
			case 'string':
				out = encodeURI( s.join( '&' ) );
				break;
			case 'array':
				out = n;
				break;
			case 'attributes':
				out = a.join( ' ' );
				break;
			default:
				out = obj;
		}

		return out;
	};

	/**
	 * Active tabs slider.
	 *
	 * @param that
	 */
	function oiyamaps_set_slide( that ) {
		let index  = parseInt( $( that ).attr( 'data-index' ) );
		let parent = $( that ).closest( '.js-slider-box' );

		let slides = parent.find( '.js-slider-item' );

		parent.find( '.js-tab' ).removeClass( 'active' );
		$( that ).addClass( 'active' );

		$.each( slides, function ( i, element ) {

			$( element ).removeClass( 'active' );
			if ( index === i ) {
				$( element ).addClass( 'active' );
			}
		} );

	}

	/**
	 * Reset forms and close the modal.
	 */
	function oiyamaps_hide_modal() {
		$.each( $( '.js-modal_oiyamaps form' ), function ( index, element ) {

			$( element )[ 0 ].reset();
		} );
		$( '.js-modal_oiyamaps' ).removeClass( 'active' );
	}

	/**
	 * Get coordinates by given address.
	 *
	 * @param obj
	 */
	function oiyamaps_get_place( obj ) {
		let address_selector     = $( obj ).attr( 'data-address' );
		let coordinates_selector = $( obj ).attr( 'data-coordinates' );
		let place                = $( obj ).val();
		let container            = $( obj ).closest( 'div' );
		let data                 = {
			'action' : 'oiyamaps_ajax_get_place',
			'place' : place
		};

		container.addClass( 'oiyamaps-preloader' );
		$.post( ajaxurl, data, function ( response ) {

			// set the result
			if ( address_selector !== undefined ) {
				$( address_selector ).val( response[ 0 ] );
			}
			$( coordinates_selector ).val( response[ 1 ] );
			container.removeClass( 'oiyamaps-preloader' );
		} );
	}

	/**
	 * Building attributes query for shortcode whith ignoring default data.
	 *
	 * @param data
	 * @param default_data
	 * @param ignore - list of keys that should be replaced if they empty
	 * @param replace - list of keys that should be replaced with given if they empty
	 * @returns {string}
	 */
	function make_query( data, default_data, ignore, replace ) {
		let query = [];
		if ( ignore === undefined ) {
			ignore = [];
		}
		if ( replace === undefined ) {
			replace = [];
		}

		for ( let key in data ) {
			if ( data.hasOwnProperty( key ) ) {
				if ( Array.isArray( data[ key ] ) ) {
					data[ key ] = data[ key ].join( ',' );
				}

				if ( data[ key ] !== default_data[ key ] ) {

					if ( data[ key ] === [] || data[ key ] === '' || data[ key ] === undefined ) {

						if ( ignore.indexOf( key ) < 0 ) {
							if ( replace[ key ] !== undefined ) {
								data[ key ] = replace[ key ];
							}
							query.push( key + '="' + data[ key ] + '"' );
						}
					} else {
						query.push( key + '="' + data[ key ] + '"' );
					}


				}
			}
		}

		return query.join( ' ' );
	}

	/**
	 * Show choosen slide.
	 */
	$( document.body ).on( 'click', '.js-tab', function () {
		oiyamaps_set_slide( this );
	} );

	/**
	 * Show the shortcode maker modal.
	 */
	$( '.js-modal_show_oiyamaps' ).on( 'click', function () {
		$( '.js-modal_oiyamaps' ).addClass( 'active' );
	} );

	/**
	 * Shortcode maker modal close button.
	 */
	$( document.body ).on( 'click', '.js-modal_close_oiyamaps', function () {
		placemark_buttons_toggle( 'placemark-form__hide' );
		oiyamaps_hide_modal();
	} );

	$( document.body ).on( 'click', '.js-cancel_oiyamaps', function () {
		$( this ).closest( 'form' )[ 0 ].reset();
		placemark_buttons_toggle( 'placemark-form__hide' );
	} );

	// filter given place
	$( document.body ).on( 'change', '.js-address_oiyamaps, .js-center_oiyamaps', function () {

		oiyamaps_get_place( this );
	} );

	/**
	 * Prepare form data for shortcode making.
	 *
	 * @param obj
	 * @returns {{}}
	 */
	function get_form_data( obj ) {
		let form       = $( obj );
		let gist       = form.attr( 'data-gist' );
		let shortcode  = {};
		let data       = form.serializeObject();
		let attributes = {};

		// loop getted data and remove empty attribus
		for ( let key in data ) {
			// if attribute not empty
			if ( data.hasOwnProperty( key ) && data[ key ] !== '' ) {
				attributes[ key ] = data[ key ];
			}
		}

		shortcode = {
			'gist' : gist,
			'attributes' : attributes,
		};

		return shortcode;
	}

	/**
	 * Adding placemark or map shortcode
	 *
	 * @param obj
	 */
	function gist_add( obj ) {
		let form      = $( obj );
		let data      = get_form_data( obj );
		let address   = data[ 'attributes' ][ 'address' ];
		let id        = 0;
		let shortcode = JSON.stringify( data );
		shortcode     = encodeURI( shortcode );

		$( '.oiyamaps-error' ).remove();

		// if placemark address has been seted
		if ( address !== '' && address !== undefined ) {

			// if gist id doesn't setted
			if ( data[ 'id' ] === 0 ) {
				if ( oiyamaps.id[ data[ 'gist' ] ] === undefined ) {
					oiyamaps.id[ data[ 'gist' ] ] = 0;
				}
				id = oiyamaps.id[ data[ 'gist' ] ];

				// increment gist id
				oiyamaps.id[ data[ 'gist' ] ]++;
			} else {
				id = data[ 'id' ];
			}

			// add new record to gist list
			$( '.js-' + data[ 'gist' ] + '_list_oiyamaps' ).append( tmpl( $( '#js-template_' + data[ 'gist' ] ).html(), {
				shortcode : shortcode,
				address : address,
				id : id,
			} ) );


			// if adding something but not a map
			if ( data[ 'gist' ] !== 'map' ) {

				// hide adding form
				form.addClass( 'oiyamaps-hidden' );

				// show adding button
				$( '.js-' + data[ 'gist' ] + '_form_show_oiyamaps' ).removeClass( 'oiyamaps-hidden' );
			}

			form[ 0 ].reset();
		} else {
			let address = $( '.js-address_oiyamaps' );
			address.after( '<span class="oiyamaps-error">' + oiyamaps[ 'localization' ][ 'have_to_fillin' ] + '</span>' );
			address.goTo( 'overflow-y', 'scroll' );
		}
	}

	function insert_oi_yamaps( obj ) {
		let that            = $( obj );
		let insert_gist     = that.attr( 'data-gist' );
		let shortcode       = [];
		let shortcode_inner = [];

		// getting data from each form
		$.each( $( '.js-form_oiyamaps' ), function ( index, element ) {
			let form          = $( element );
			let gist          = form.attr( 'data-gist' );
			shortcode[ gist ] = form.serializeObject();
			let attributes    = [];

			// loop getted data and remove empty attribus
			$.each( shortcode[ gist ], function ( key, data ) {
				if ( shortcode[ gist ][ key ] !== '' ) {
					attributes[ key ] = shortcode[ gist ][ key ];
				}
			} );
			shortcode[ gist ] = attributes;
		} );


		let query = '';
		if ( insert_gist === 'map' ) {
			for ( let key in shortcode ) {
				if ( key !== 'map' ) {

					query = make_query( shortcode[ key ], oiyamaps[ 'options' ] );
					if ( query !== '' ) {
						shortcode_inner.push( [ '[' + key, query + '/]' ].join( ' ' ) );
					}
				}
			}


			query = make_query( shortcode[ insert_gist ], oiyamaps[ 'options' ], [ 'center' ], { 'controls' : 'none', } );
			let inner;
			if ( shortcode_inner.join( '' ).trim() !== '' ) {
				inner = "\n" + shortcode_inner.join( "\n" );
			} else {
				inner = '';
			}
			shortcode = '[showyamap ' + query + ']' + inner + '[/showyamap]';
		} else {
			query     = make_query( shortcode[ insert_gist ], oiyamaps[ 'options' ] );
			shortcode = '[' + insert_gist + ' ' + query + '/]';
		}

		//window.send_to_editor( shortcode[ attr ] );
		//oiyamaps_hide_modal();
	}

	$( document.body ).on( 'submit', '.js-form_oiyamaps', function ( event ) {
		event.preventDefault();

		let gist = $( this ).attr( 'data-gist' );
		if ( gist === 'placemark' ) {
			gist_add( this );
			placemark_buttons_toggle( 'placemark-form__hide' );
		} else {
			insert_oi_yamaps( this );
		}
	} );

	function placemark_buttons_toggle( action ) {

		let form_show       = $( '.js-placemark_form_show_oiyamaps' );
		let placemark_form  = $( '.js-placemark_form_oiyamaps' );
		let placemarks_list = $( '.js-placemark_list_oiyamaps' );
		let map_add_block   = $( '.js-map-add-block' );

		switch ( action ) {
			case 'placemark-form__show':

				// show form button
				form_show.addClass( 'oiyamaps-hidden' );

				// hide placemarks list block
				placemarks_list.addClass( 'oiyamaps-hidden' );

				// show placemark form
				placemark_form.removeClass( 'oiyamaps-hidden' );

				// show placemark form
				map_add_block.addClass( 'oiyamaps-hidden' );
				break;
			case 'placemark-form__hide':

				// show form button
				form_show.removeClass( 'oiyamaps-hidden' );

				// hide placemarks list block
				placemarks_list.removeClass( 'oiyamaps-hidden' );

				// show placemark form
				placemark_form.addClass( 'oiyamaps-hidden' );

				// show placemark form
				map_add_block.removeClass( 'oiyamaps-hidden' );
				break;
		}
	}

	/**
	 * Add placemark pushed.
	 */
	$( document.body ).on( 'click', '.js-placemark_form_show_oiyamaps', function () {
		placemark_buttons_toggle( 'placemark-form__show' );
	} );

	$( document.body ).on( 'click', '.js-remove', function () {
		$( this ).closest( '.js-item' ).remove();
	} );

	/**
	 * Fill the gist form with given data.
	 *
	 * @param json
	 */
	function fill_gist_form( json ) {
		let value = '';

		let data = get_shortcode_json_as_array( json );

		let form = '.js-' + data[ 'gist' ] + '_form_oiyamaps';

		for ( let name in data[ 'attributes' ] ) {
			if ( data[ 'attributes' ].hasOwnProperty( name ) ) {
				value = data[ 'attributes' ][ name ];
			} else {
				value = '';
			}
			$( form + ' [name="' + name + '"]' ).val( value );
		}
		placemark_buttons_toggle( data[ 'gist' ] + '-form__show' );

	}

	/**
	 * Get JSON data from an element and fill specific form with that data.
	 */
	$( document.body ).on( 'click', '.js-edit', function () {
		let json = $( obj ).closest( '.js-item' ).attr( 'data-shortcode' );
		fill_gist_form( json );
	} );

	$( document.body ).on( 'click', '.js-submit_oiyamaps', function () {
		let placemarks = get_placemarks_shortcodes();
		let map        = get_map_shortcode();
		let shortcode  = '[showyamap ' + map + ']' + "\n" + placemarks + "\n" + '[/showyamap]';

		window.send_to_editor( shortcode );
		oiyamaps_hide_modal();
	} );


	/**
	 * Getting placemarks shortcodes from JSON in list.
	 */
	function get_placemarks_shortcodes() {
		let json;
		let data;
		let shortcode = [];
		$.each( $( '.js-placemark_list_oiyamaps .js-item' ), function ( i, item ) {
			json = $( item ).attr( 'data-shortcode' );
			data = get_shortcode_json_as_array( json );
			shortcode.push( shortcode_stringify( data ) );
		} );
		shortcode = shortcode.join( "\n" );

		return shortcode;
	}


	function get_map_shortcode() {
		let data = $( '.js-form_map_oiyamaps' ).serializeObject();
		return make_query( data, oiyamaps[ 'options' ], [ 'center' ], { 'controls' : 'none', } );
	}


	function shortcode_stringify( data ) {
		let shortcode = [];
		shortcode.push( data[ 'gist' ] );
		for ( let name in data[ 'attributes' ] ) {
			if ( data[ 'attributes' ].hasOwnProperty( name ) ) {
				shortcode.push( name + '="' + data[ 'attributes' ][ name ] + '"' );
			}
		}
		shortcode = '[' + shortcode.join( ' ' ) + '/]';

		return shortcode;
	}

	/**
	 * Convert stringyfied shortcode form JSON to an object.
	 *
	 * @param json
	 * @returns {any | *}
	 */
	function get_shortcode_json_as_array( json ) {
		json = decodeURI( json );
		json = JSON.parse( json );

		return json;
	}

	function parseShortcodes(){

		var media = wp.media, shortcode_string = 'showyamap';
		wp.mce = wp.mce || {};
		wp.mce.oiyamaps = {
			shortcode_data: {},
			template: media.template( 'oiyamaps' ),
			getContent: function() {
				var options = this.shortcode.attrs.named;
				options.text = this.text;
				options.plugin = 'karta';
				options.innercontent = this.shortcode.content;
				return this.template(options);
			},
			View: { // before WP 4.2:
				template: media.template( 'oiyamaps' ),
				postID: $('#post_ID').val(),
				initialize: function( options ) {
					this.shortcode = options.shortcode;
					wp.mce.oiyamaps.shortcode_data = this.shortcode;
				},
				getHtml: function() {
					var options = this.shortcode.attrs.named;
					options.innercontent = this.shortcode.content;
					return this.template(options);
				}
			},
			edit: function( data ) {
				var shortcode_data = wp.shortcode.next(shortcode_string, data);
				var values = shortcode_data.shortcode.attrs.named;
				values.innercontent = shortcode_data.shortcode.content;
				wp.mce.oiyamaps.popupwindow(tinyMCE.activeEditor, values);
			},
			// this is called from our tinymce plugin, also can call from our "edit" function above
			// wp.mce.oiyamaps.popupwindow(tinyMCE.activeEditor, "bird");
			popupwindow: function(editor, values, onsubmit_callback){

				values = values || [];
				editMapAction=true;
				ym['map0']={};
				ym['map0'].places={};
				for(var key in values) {
					ym['map0'][key]=values[key];
					delete ym['map0'].innercontent;
					findPlaceMarks(values.innercontent);

				}
				mapcenter=ym.map0.center;
				mapzoom=ym.map0.zoom;
				//tinymce.activeEditor.execCommand("yamap_command");

			}
		};
		wp.mce.views.register( shortcode_string, wp.mce.oiyamaps );
	}
	parseShortcodes();

})( jQuery );


// -------------------------------------- //



