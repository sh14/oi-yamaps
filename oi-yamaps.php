<?php
/*
Plugin Name: Oi Yandex.Maps for WordPress
Plugin URI: https://oiplug.com/plugin/oi-yandex-maps-for-wordpress/
Description: The plugin allows you to use Yandex.Maps on your site pages and put the placemarks on the map. Without an API key.
Author: Alexei Isaenko
Version: 3.0
Author URI: https://oiplug.com/members/isaenkoalexei
*/


namespace oiyamaps;

/**
 * Function returns path to the current plugin: `/htdocs/wp-content/plugins/oi-frontend/`
 *
 * @return string
 */
function plugin_path() {
	return plugin_dir_path( __FILE__ );
}

/**
 * Function returns url to the current plugin
 *
 * @return string
 */
function plugin_url() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Function returns name of current plugin directory: `oi-frontend`
 *
 * @return string
 */
function plugin_name() {
	return plugin_basename( plugin_path() );
}

require_once plugin_path() . '/include/init.php';
if ( ! function_exists( 'oinput_form' ) ) {
	require_once plugin_path() . 'include/oi-nput.php';
}
if ( function_exists( 'oinput_form' ) ) {
	require_once plugin_path() . '/include/templates.php';
	require_once plugin_path() . '/include/console.php';
	require_once plugin_path() . '/include/options.php';
}
//require_once "include/tinymce/shortcode.php";


/**
 * set default variables on plugin activation
 */
function activation() {
	$options = get_option( prefix() . 'options' );
	// if we don't have any settengs
	if ( empty( $options ) ) {
		update_option( prefix() . 'options', oi_yamaps_defaults() );
	}

	deactivate_plugins( array( '/oi-yamaps/oi-ya-maps.php', ) );
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\activation' );


// localization
function oi_yamaps() {
	load_plugin_textdomain( 'oi-yamaps', false, plugin_basename( dirname( __FILE__ ) ) . '/language' );
}

add_action( 'init', __NAMESPACE__ . '\oi_yamaps' );

/**
 * List of some names of API gists
 *
 * @param $key
 *
 * @return mixed
 */
function get_api_names( $key ) {
	$names = array(
		'controls'  => array(
			//'mapTools', // depricated
			//'smallZoomControl', // depricated
			//'scaleLine', // depricated
			//'miniMap', // depricated
			'fullscreenControl'  => array(
				'caption' => __( 'Fullscreen control button', 'oi-yamaps' ),
				'hint'    => __( 'Stretches map to fullscreen mode.', 'oi-yamaps' ),
				'default' => true,
			),
			'geolocationControl' => array(
				'caption' => __( 'Geolocation control button', 'oi-yamaps' ),
				'hint'    => __( "Gives an ability to get user's position", 'oi-yamaps' ),
				'default' => true,
			),
			'rulerControl'       => array(
				'caption' => __( 'Ruler control button', 'oi-yamaps' ),
				'hint'    => __( 'Shows the map scale.', 'oi-yamaps' ),
				'default' => true,
			),
			'routeEditor'        => array(
				'caption' => __( 'Route editor control button', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to build a route.', 'oi-yamaps' ),
				'default' => true,
			),
			'typeSelector'       => array(
				'caption' => __( 'Type select controler', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to choose map mode.', 'oi-yamaps' ),
				'default' => true,
			),
			'zoomControl'        => array(
				'caption' => __( 'Zoom control button', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to change map scale.', 'oi-yamaps' ),
				'default' => true,
			),
			'searchControl'      => array(
				'caption' => __( 'Search field', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to search for locations.', 'oi-yamaps' ),
				'default' => true,
			),
			'trafficControl'     => array(
				'caption' => __( 'Traffic control button', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to show or hide traffic lines.', 'oi-yamaps' ),
				'default' => true,
			),
		),
		'behaviors' => array(
			'dblClickZoom'              => array(
				'caption' => __( 'Double Click Zoom', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to zoom in by left mouse button and zoom out by right.', 'oi-yamaps' ),
				'default' => true,
			),
			'drag'                      => array(
				'caption' => __( 'Map draging', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to drag the map.', 'oi-yamaps' ),
				'default' => true,
			),
			'multiTouch'                => array(
				'caption' => __( 'Multitouch', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to change map scale with multitouch.', 'oi-yamaps' ),
				'default' => true,
			),
			'leftMouseButtonMagnifier'  => array(
				'caption' => __( 'Left mouse button magnifier', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to change map scale by choose zooming area with left mouse button.', 'oi-yamaps' ),
				'default' => false,
			),
			'rightMouseButtonMagnifier' => array(
				'caption' => __( 'Right mouse button magnifier', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to change map scale by choose zooming area with right mouse button.', 'oi-yamaps' ),
				'default' => true,
			),
			'scrollZoom'                => array(
				'caption' => __( 'Scroll zoom', 'oi-yamaps' ),
				'hint'    => __( 'Gives an ability to change map scale by scrolling.', 'oi-yamaps' ),
				'default' => true,
			),
		),
	);

	return $names[ $key ];
}


/**
 * Список значений настроек по умолчанию
 *
 * @return array
 */
function oi_yamaps_defaults() {
	$defaults = array(
		'lang'           => get_locale(),
		'height'         => '400px',
		'width'          => '100%',
		'zoom'           => '16',
		'placemark'      => 'islands#blueDotIcon',
		'author_link'    => '1',
		'show_by_click'  => 0,
		'address'        => '',
		'center'         => '',
		'header'         => '',
		'body'           => '',
		'footer'         => '',
		'hint'           => '',
		'coordinates'    => '',
		'iconimage'      => '',
		'button_caption' => __( 'Show the map', 'oi-yamaps' ),
		'iconcontent'    => '',
		'iconcaption'    => '',
		'iconsize'       => '',
		'iconoffset'     => '',
		'iconrect'       => '',
		'controls'       => implode( ',', get_match_list( get_api_names( 'controls' ) ) ),
		'behaviors'      => implode( ',', get_match_list( get_api_names( 'behaviors' ) ) ),
	);

	return $defaults;
}


// check, if maps packege is loaded
class Ya_map_connected {
	// default value - packege not loaded yet
	public static $id = 0;
	// default value - packege not loaded yet
	public static $pid = 0;

	public function staticValue() {
		// return actual value
		return self::$id;
	}

	public function staticValue1() {
		// return actual value
		return self::$pid;
	}
}

/**
 * Check if cURL module is on.
 *
 * @return bool
 */
function _isCurl() {
	return function_exists( 'curl_version' );
}

/**
 * Get content via cURL.
 *
 * @param $url
 *
 * @return mixed
 */
function curl_get_contents( $url ) {
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
	$data = curl_exec( $curl );
	curl_close( $curl );

	return $data;
}

/**
 * Getting data from Yandex
 *
 * @param $place
 *
 * @return string
 */
function oiyamap_geocode( $place ) {

	// set cash key
	$key     = md5( $place );
	$content = '';

	// if there is no data with that key
	if ( WP_DEBUG == true || ! ( $content = wp_cache_get( $key, 'oi-yamaps' ) ) ) {

		$place = urlencode( $place );

		$url = "https://geocode-maps.yandex.ru/1.x/?geocode=" . $place . '&format=json';

		// get data by GET method
		$content = wp_remote_get( $url, apply_filters( 'oiyamaps_wp_remote_get_args', array() ) );

		if ( ! is_wp_error( $content ) ) {

			// get the content body
			$content = wp_remote_retrieve_body( $content );

		} else {

			// something goes wrong

			if ( ! ( $content = @file_get_contents( $url ) ) ) {

				// if cURL is on
				if ( _isCurl() ) {

					$content = curl_get_contents( $url );
				} else {
					$content = __( 'To show the map cURL must be enabled.', 'oi-yamaps' );

					return $content;
				}
			}
		}
		$content = json_decode( $content, true );

		wp_cache_set( $key, $content, 'oi-yamaps', HOUR_IN_SECONDS * 24 );
	}

	return $content;
}


/**
 * The function determines what was passed in and how function was called. As a result of processing returns an array
 * containing information about the address, its coordinates, and a flag indicating
 * that what was transmitted - coordinates($result[2]=true) or address($result[2]=false)
 *
 * @param null $place
 *
 * @return array
 */
function get_place( $place = null ) {
	$address     = '';
	$coordinates = '';
	$flag        = true;

	// address was given
	$is_coordinates = false;

	if ( ! empty( $_POST['place'] ) ) {
		$place = $_POST['place'];
		$flag  = false;
	}

	if ( ! empty( $place ) ) {
		$coordinates = explode( ',', $place );
		if ( sizeof( $coordinates ) == 2 && is_numeric( trim( $coordinates[0] ) ) && is_numeric( trim( $coordinates[1] ) ) ) {
			$coordinates = array_map( 'trim', $coordinates );

			$coordinates = implode( ',', array_reverse( $coordinates ) );

			// get address coordinates
			$address = oiyamap_geocode( $coordinates );

			$address     = $address['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
			$coordinates = implode( ',', array_reverse( explode( ',', $coordinates ) ) );

			$is_coordinates = true;
		} else {

			$coordinates = oiyamap_geocode( urldecode( $place ) );
			$address     = $coordinates['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];

			// get address
			$coordinates = $coordinates['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];

			// set coordinates by format: lon, len
			$coordinates = implode( ',', array_reverse( explode( ' ', trim( $coordinates ) ) ) );
		}
	}

	$result = array( $address, $coordinates, $is_coordinates );

	if ( $flag == false ) {
		wp_send_json( $result );
	}

	return $result;
}

add_action( 'wp_ajax_' . 'oiyamaps_get_place', __NAMESPACE__ . '\get_place' );

/**
 * Returns an associated array where lowercase key has original value.
 *
 * @return array
 */
function get_match_list( $list ) {

	$associated_list = array();
	if ( ! empty( $list ) ) {
		foreach ( $list as $key => $value ) {
			$associated_list[ strtolower( $key ) ] = $key;
		}
	}

	return $associated_list;
}

/**
 * Trim every element of a string/array list and return it as a string separated by comma
 *
 * @param $list
 *
 * @return array|string
 */
function array_cut_list( $atts ) {
	$atts = wp_parse_args( $atts, array(
		'list'    => array(),
		'before'  => '',
		'between' => '',
		'after'   => ',',
	) );
	if ( is_string( $atts['list'] ) ) {
		$atts['list'] = trim( $atts['list'] );
		$atts['list'] = explode( ',', $atts['list'] );
	}


	if ( ! empty( $atts['list'] ) ) {
		$atts['list'] = array_map( 'trim', $atts['list'] );

		$list = $atts['before'] . implode( $atts['after'] . $atts['between'] . $atts['before'], $atts['list'] ) . $atts['after'];

	} else {
		$list = '';
	}

	return $list;
}

/**
 * Perform options list for JavaScript API
 *
 * @param $user_list
 *
 * @return array|string
 */
function map_options_add( $atts ) {

	$atts = wp_parse_args( $atts, array(
		'data'                      => [],
		'name'                      => '',
		'positive_before'           => "'",
		'positive_after'            => "',",
		'negative_before'           => '',
		'negative_after'            => ',',
		'container_positive_before' => '([',
		'container_positive_after'  => '])',
		'container_negative_before' => '([',
		'container_negative_after'  => '])',
	) );

	if ( empty( $atts['data'] ) || empty( $atts['name'] ) ) {
		return '';
	}

	// get user's list of options
	$user_list = $atts['data'][ $atts['name'] ];

	// get controls match list
	$matches = get_match_list( get_api_names( $atts['name'] ) );

	// if $user_list not emty
	if ( ! empty( $user_list ) ) {

		// if $user_list is a string
		if ( is_string( $user_list ) ) {

			// convert it to an array
			$user_list = explode( ',', $user_list );
		}

		// trim every element
		$user_list = array_map( 'trim', $user_list );
		$user_list = array_map( 'strtolower', $user_list );
	} else {

		// set $user_list equal to keys of $matches
		$user_list = array_keys( $matches );
	}

	// set new array
	$gist_add    = array();
	$gist_remove = array();

	// loop control list
	foreach ( $matches as $key => $value ) {

		// if control exists
		if ( in_array( $key, $user_list ) ) {

			// add list
			$gist_add[] = $value;
		} else {

			// remove list
			$gist_remove[] = $value;
		}
	}

	// remove control
	$gist_remove = array_cut_list( array(
		'list'   => $gist_remove,
		'before' => $atts['negative_before'],
		'after'  => $atts['negative_after'],
	) );

	// add control
	$gist_add = array_cut_list( array(
		'list'   => $gist_add,
		'before' => $atts['positive_before'],
		'after'  => $atts['positive_after'],
	) );


	$matches = array();

	// -- save the order: remove then add -- //

	// if $gist_remove not empty
	if ( ! empty( $gist_remove ) ) {

		$matches[] = $atts['container_negative_before'] . $gist_remove . $atts['container_negative_after'];
	}

	// if $gist_add not empty
	if ( ! empty( $gist_add ) ) {

		$matches[] = $atts['container_positive_before'] . $gist_add . $atts['container_positive_after'];
	}


	return implode( "\n", $matches );
}


/**
 * Show block with the map on a page
 *
 * @param      $atts
 * @param null $content
 *
 * @return string
 */
function showyamap( $atts, $content = null ) {
	print_r( get_option( 'active_plugins' ) );// die();
	// get attributes from options
	$option = wp_parse_args( get_option( prefix() . 'options' ), oi_yamaps_defaults() );

	// get attributes of concrete map
	$atts = wp_parse_args( $atts, $option );

	$out                = '';
	$vars               = array();
	$vars['placemarks'] = array();

	if ( empty( $atts['button_caption'] ) ) {
		$atts['button_caption'] = __( 'Show the map', 'oi-yamaps' );
	}

	$map_options = array();
	// perform behaviors list
	$map_options[] = map_options_add( array(
		'data'                      => $atts,
		'name'                      => 'behaviors',
		'positive_before'           => "'",
		'positive_after'            => "',",
		'negative_before'           => "'",
		'negative_after'            => "',",
		'container_positive_before' => "myMap.behaviors.enable([",
		'container_positive_after'  => "]);",
		'container_negative_before' => "myMap.behaviors.disable([",
		'container_negative_after'  => "]);",
	) );

	// perform controls list
	$map_options[] = map_options_add( array(
		'data'                      => $atts,
		'name'                      => 'controls',
		'positive_before'           => "\n" . 'myMap.controls.add("',
		'positive_after'            => '");',
		'negative_before'           => "\n" . '.remove("',
		'negative_after'            => '")',
		'container_positive_before' => '',
		'container_positive_after'  => '',
		'container_negative_before' => 'myMap.controls',
		'container_negative_after'  => ';',
	) );

	$map_options = implode( "\n", $map_options );


	// set id of map block
	$id = Ya_map_connected::$id;

	// if coordinates not set...
	if ( empty( $atts['coordinates'] ) ) {

		// if we have an address, then...
		if ( ! empty( $atts['address'] ) ) {

			// take coordinates
			$place = get_place( $atts['address'] );

			// если были указаны координаты
			if ( $place[2] == true ) {

				// определение адреса
				$atts['address'] = $place[0];
			}

			$atts['coordinates'] = $place[1];

			// if we don't...
		} else {

			// get latitude from post meta
			$atts['latitude'] = get_post_meta( get_the_ID(), 'latitude', true );

			// get longitude from post meta
			$atts['longitude'] = get_post_meta( get_the_ID(), 'longitude', true );

			// if we have coordinates...
			if ( $atts['latitude'] && $atts['longitude'] ) {

				// split them
				$atts['coordinates'] = $atts['latitude'] . ',' . $atts['longitude'];
			} else {
				$atts['coordinates'] = '';
			}
		}
	}

	if ( ! empty( $atts['coordinates'] ) ) {
		$vars['placemarks'][] = array(
			'pid'         => $id,
			'header'      => $atts['header'],
			'body'        => $atts['body'],
			'footer'      => $atts['footer'],
			'hint'        => $atts['hint'],
			'coordinates' => $atts['coordinates'],
			'iconcontent' => $atts['iconcontent'],
			'iconcaption' => $atts['iconcaption'],
			'placemark'   => $atts['placemark'],
			'iconimage'   => $atts['iconimage'],
			'iconsize'    => '',
			'iconoffset'  => '',
			'iconrect'    => '',
		);
	}

	// delete all not necessary simbols from $content

	// shortcode not started flag
	$atts['record'] = false;

	// shortcode container
	$vars['content'] = '';

	// going thru $content
	for ( $i = 0; $i < strlen( $content ); $i ++ ) {

		if ( $content[ $i ] == '[' ) {

			$atts['record'] = true;
		}

		// shortcode started
		if ( $atts['record'] == true ) {

			$vars['content'] .= $content[ $i ];
		}

		// make shortcode string

		// shortcode ended
		if ( $content[ $i ] == ']' ) {

			// set flag
			$atts['record'] = false;

			// add array of vars to $vars['placemarks'] array
			$vars['placemarks'][] = json_decode( do_shortcode( $vars['content'] ), true );
			$vars['content']      = '';
		}
	}

	if ( ! empty( $atts['center'] ) ) {
		$atts['center'] = trim( $atts['center'] );
	}

	if ( ! empty( $vars['placemarks'] ) ) {

		// make placemarks string, for adding to code
		$atts['placemark_code'] = '';
		$atts['lat']            = array();
		$atts['lon']            = array();
		foreach ( $vars['placemarks'] as $key => $value ) {
			// set placemark if it's not...
			if ( empty( $value['placemark'] ) ) {
				$value['placemark'] = $atts['placemark'];
			}
			if ( empty( $atts['center'] ) ) {
				list( $atts['lat'][], $atts['lon'][] ) = explode( ',', $value['coordinates'] );
			}
			$atts['placemark_code'] .= placemark_code( $value );
		}
		if ( empty( $atts['center'] ) ) {
			// center betwin all placemarks
			$atts['center'] = io_ya_map_center( $atts['lat'], $atts['lon'] );
		}

		if ( ! empty( $atts['author_link'] ) && $atts['author_link'] == 1 ) {
			$atts['author_link'] = '<a class="oi_yamaps_author_link" target="_blank" href="https://oiplug.com/">' . __( 'OiYM', 'oi-yamaps' ) . '</a>';
		}
		if ( ! empty( $atts['show_by_click'] ) && $atts['show_by_click'] == 1 ) {
			$atts['display'] = 'display: none;';
			$atts['button']  = '<button id="YMaps_' . $id . '_btn" class="btn btn-default">' . $atts['button_caption'] . '</button>';
			$atts['btn_s']   = 'jQuery("#YMaps_' . $id . '_btn").click(function(){';
			$atts['btn_e']   =
				'jQuery("#YMaps_' . $id . '_btn").hide();' .
				'jQuery("#YMaps_' . $id . '").show();' .
				'});';
		} else {
			$atts['display'] = '';
			$atts['button']  = '';
			$atts['btn_s']   = '';
			$atts['btn_e']   = '';
		}
		$vars['style'] = ' style="width:' . $atts['width'] . ';height:' . $atts['height'] . ';' . $atts['display'] . '"';
		$out           .=
			'<div id="YMaps_' . $id . '" class="YMaps"' . $vars['style'] . '>' . $atts['author_link'] . '</div>' .
			$atts['button'] .
			'<script type="text/javascript">' .
			$atts['btn_s'] .
			'
	ymaps.ready(init);

	function init () {
		var myMap = new ymaps.Map("YMaps_' . $id . '", {
			center: [' . $atts['center'] . '],
			zoom: ' . $atts['zoom'] . '
		});
		'
			. $map_options
			. '
		' . $atts['placemark_code'] . '
	}
	' .
			$atts['btn_e'] .
			'</script>';

		// set new id
		Ya_map_connected::$id ++;
		// if no maps on a page...
		if ( $id == 0 ) {
			// ...and show the map
			$out = '<script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=' . $atts['lang'] . '"></script>' .
			       '<style>.YMaps {position: relative;} .YMaps .oi_yamaps_author_link {position: absolute;bottom: 9px; right:330px; z-index: 999;padding:0;display: table!important;line-height:12px;text-decoration:underline!important;white-space: nowrap!important;font-family: Verdana,serif!important;font-size: 10px!important;padding-left: 2px!important;color: #000!important;background-color: rgba(255, 255, 255, 0.7)!important;border:none;}</style>' .
			       "\n" . $out;
		} else {

		}
	}

	// show the map
	return $out;
}

add_shortcode( 'showyamap', __NAMESPACE__ . '\showyamap' );
add_shortcode( 'yamap', __NAMESPACE__ . '\showyamap' );


// searching center betwin all placemarks
function io_ya_map_center( $lat, $lon ) {
	$la     = 0;
	$lo     = 0;
	$la_min = 0;
	$la_max = 0;
	$lo_min = 0;
	$lo_max = 0;

	$sizeof = sizeof( $lat );
	for ( $i = 0; $i < $sizeof; $i ++ ) {
		if ( $la == 0 ) {
			$la_min = (float) $lat[ $i ];
			$la_max = (float) $lat[ $i ];
			$lo_min = (float) $lon[ $i ];
			$lo_max = (float) $lon[ $i ];
		}
		$la = (float) $lat[ $i ];
		$lo = (float) $lon[ $i ];
		if ( $la_min > $la ) {
			$la_min = $la;
		}
		if ( $la_max < $la ) {
			$la_max = $la;
		}
		if ( $lo_min > $lo ) {
			$lo_min = $lo;
		}
		if ( $lo_max < $lo ) {
			$lo_max = $lo;
		}

	}
	$la     = ( $la_min + $la_max ) / 2;
	$lo     = ( $lo_min + $lo_max ) / 2;
	$center = $la . ',' . $lo;

	return $center;
}

function oi_ya_map_brackets( $s ) {
	return str_replace( ')', ']', str_replace( '(', '[', $s ) );
}

function placemark_code( $atts ) {
	$atts = shortcode_atts( array(
		'pid'         => '',
		'header'      => '',
		'body'        => '',
		'footer'      => '',
		'hint'        => '',
		'coordinates' => '',
		'iconcontent' => '',
		'iconcaption' => '',
		'placemark'   => '',
		'iconimage'   => '',
		'iconsize'    => '',
		'iconoffset'  => '',
		'iconrect'    => '',
	), $atts );

	foreach ( $atts as $key => $value ) {
		if ( ! empty( trim( $atts[ $key ] ) ) ) {

			switch ( $key ) {
				case 'iconcontent':
					$atts['iconcontent'] = 'iconContent: "' . $atts['iconcontent'] . '",';
					// if content for placemark given, make placemark stretch

					if ( ! empty( trim( $atts['placemark'] ) ) ) {

						// remove icon name with 2.1 API name
						$atts['placemark'] = str_replace( 'twirl', 'islands', $atts['placemark'] );
						$atts['placemark'] = str_replace( 'Dot', '', $atts['placemark'] );
						$atts['placemark'] = str_replace( 'Icon', 'StretchyIcon', $atts['placemark'] );
					}
					break;

				case 'iconcaption':
					$atts['iconcaption'] = 'iconCaption: "' . $atts['iconcaption'] . '",';
					break;

				case 'header':
					$atts['header'] = 'balloonContentHeader: "' . $atts['header'] . '",';
					break;
				case 'body':
					$atts['body'] = 'balloonContentBody: "' . $atts['body'] . '",';
					break;
				case 'footer':
					$atts['footer'] = 'balloonContentFooter: "' . $atts['footer'] . '",';
					break;


				case 'hint':
					$atts['hint'] = 'hintContent: "' . $atts['hint'] . '"';
					break;

				case 'iconimage':
					$atts['iconimage'] = 'iconImageHref: "' . $atts['iconimage'] . '", ';
					break;
				case 'iconsize':
					$atts['iconsize'] = 'iconImageSize: ' . oi_ya_map_brackets( $atts['iconsize'] ) . ', ';
					break;
				case 'iconoffset':
					$atts['iconoffset'] = 'iconImageOffset: ' . oi_ya_map_brackets( $atts['iconoffset'] ) . ' ';
					break;
				case 'iconrect':
					$atts['iconrect'] = 'iconImageClipRect: ' . oi_ya_map_brackets( $atts['iconrect'] ) . ' ';
					break;
				case 'placemark':
					if ( ! $atts['iconimage'] ) {
						$atts['placemark'] = 'preset: "' . $atts['placemark'] . '"';
					} else {
						$atts['placemark'] = '';
					}
					break;
			}
		}
	}

	// replace braces with triangular brackets
	$content_tags = array( 'header', 'body', 'footer', );
	foreach ( $content_tags as $tag ) {
		if ( ! empty( trim( $atts[ $tag ] ) ) ) {
			$atts[ $tag ] = str_replace( array( '{', '}', ), array( '<', '>', ), $atts[ $tag ] );
		}
	}

	$output = '
				myPlacemark_' . $atts['pid'] . ' = new ymaps.Placemark([' . $atts['coordinates'] . '], {' .
	          $atts['iconcontent'] .
	          $atts['iconcaption'] .
	          $atts['header'] .
	          $atts['body'] .
	          $atts['footer'] .
	          $atts['hint'] .
	          '},
				{' .
	          $atts['placemark'] .
	          $atts['iconimage'] .
	          $atts['iconsize'] .
	          $atts['iconoffset'] .
	          $atts['iconrect'] .
	          '}
				);
				myMap.geoObjects.add(myPlacemark_' . $atts['pid'] . ');
	
	';

	return $output;


}

function placemark( $atts ) {
	$atts = wp_parse_args( $atts, array(
		'address'     => '',
		'header'      => '',
		'body'        => '',
		'footer'      => '',
		'hint'        => '',
		'coordinates' => null,
		'iconcontent' => '',
		'iconcaption' => '',
		'placemark'   => '',
		'iconimage'   => '',
		'iconsize'    => '',
		'iconoffset'  => '',
		'iconrect'    => '',
	) );

	// get coordinates, if it's not set
	// if coordinates not set...
	if ( empty( $atts['coordinates'] ) ) {

		// if we have an address, then...
		if ( ! empty( $atts['address'] ) ) {

			// take coordinates
			$place = get_place( $atts['address'] );

			// если были указаны координаты
			if ( $place[2] == true ) {

				// определение адреса
				$atts['address'] = $place[0];
			}

			$atts['coordinates'] = $place[1];

		}
	}

	$placemark = array();
	if ( ! empty( $atts['coordinates'] ) ) {
		Ya_map_connected::$pid ++;
		$atts['pid'] = Ya_map_connected::$pid;
		$placemark   = array(
			'pid'         => $atts['pid'],
			'header'      => $atts['header'],
			'body'        => $atts['body'],
			'footer'      => $atts['footer'],
			'hint'        => $atts['hint'],
			'coordinates' => $atts['coordinates'],
			'iconcontent' => $atts['iconcontent'],
			'iconcaption' => $atts['iconcaption'],
			'placemark'   => $atts['placemark'],
			'iconimage'   => $atts['iconimage'],
			'iconsize'    => $atts['iconsize'],
			'iconoffset'  => $atts['iconoffset'],
			'iconrect'    => $atts['iconrect'],
		);

	}

	$placemark = json_encode( $placemark );

	return $placemark;
}

add_shortcode( 'placemark', __NAMESPACE__ . '\placemark' );


function oi_yamaps_same_page( $url = null ) {
	//redirect is back to the current page
	// Default 
	$uri = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if ( ! empty( $url ) ) {
		if ( strlen( $_SERVER['QUERY_STRING'] ) > 0 ) {
			// make query with new params

			// take part without query
			list( $uri ) = explode( '?', $uri );
			$query_string = $_SERVER['QUERY_STRING'] . '&' . $url; // teke query with a new params
			// Parses the string into variables
			parse_str( $query_string, $atts );
			$i   = 0;
			$url = '';
			// going through query
			foreach ( $atts as $key => $value ) {
				if ( $i > 0 ) {
					$delimiter = '&';
				} else {
					$delimiter = '';
				}
				$url .= $delimiter . $key . '=' . $value; // make new query without duplicates
				$i ++;
			}
			$uri .= '?' . $url;
		} else {
			$uri = str_replace( '?', '', $uri ) . '?' . $url;
		}
	}

	return $uri;
}


function multiselect( $atts ) {
	$atts = wp_parse_args( $atts, array(
		'key'    => '',
		'class'  => '',
		'values' => '',
	) );

	$out    = array();
	$values = explode( ',', $atts['values'] );

	if ( ! empty( $atts['key'] ) ) {
		$list = get_api_names( $atts['key'] );
		foreach ( $list as $key => $value ) {
			if ( in_array( $key, $values ) ) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}
			$out[] = '<div class="' . $atts['class'] . '__group">'
			         . '<label class="' . $atts['class'] . '__label-checkbox">'
			         . '<input type="checkbox" class="' . $atts['class'] . '__control js-checkbox" name="' . $atts['key'] . '" value="' . $key . '"' . $checked . '>'
			         . $value['caption']
			         . '<span class="' . $atts['class'] . '__label-checkbox-bullet"></span>'
			         . '</label>'
			         . '</div>';
		}
	}

	$out = implode( "\n", $out );

	return $out;
}

// eof
