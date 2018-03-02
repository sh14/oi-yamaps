<?php
/*
Plugin Name: Oi Yandex.Maps for WordPress
Plugin URI: https://oiplug.com/plugin/oi-yandex-maps-for-wordpress/
Description: The plugin allows you to use Yandex.Maps on your site pages and put the placemarks on the map. Without an API key.
Author: Alexei Isaenko
Version: 2.51
Author URI: http://www.sh14.ru
This plugin is Copyright 2012 Sh14.ru. All rights reserved.
*/
/*
// Date: 25.04.2014 - make code as a single plugin from other big project
// Date: 20.05.2014 - Stretchy Icons support added  
// Date: 21.07.2014 - 2.0 release
// Date: 22.07.2014 - 2.1 fix html in placemark; center parametr added; curl enable check
// Date: 16.09.2014 - 2.2 fix error when coordinates used; added shortcode button; localization
// Date: 08.12.2014 - 2.3 fix showmap coordinates missing; map center; added custom image; placemarks;
// Date: 21.10.2015 - 2.4 fix notices; form view; shortcode making; plugin url;
// Date: 21.10.2015 - 2.41 fix нормальное отображение center при указании;
// Date: 21.10.2015 - 2.42 fix удален вывод координат перед картой;
// Date: 05.08.2017 - 2.50 добавлено кэширование получения координат адреса, переписана функция определения координат,
оставлено одно поле для ввода адреса или координат;

*/

function oiyamaps_html( $template, $atts, $include = array() ) {

	foreach ( $include as $i => $item ) {
		$include[ $item ] = '';
		unset( $include[ $i ] );
	}
	$atts = array_merge( $include, $atts );

	foreach ( $atts as $key => $value ) {
		if ( ! is_array( $value ) ) {
			if ( empty( $value ) ) {
				$value = '';
			}
			$template = str_replace( '%' . $key . '%', $value, $template );
		}

	}

	return $template;
}


require_once "include/init.php";
//require_once "include/tinymce/shortcode.php";


function oi_yamaps_path( $file = '' ) {
	if ( empty( $file ) ) {
		return plugin_dir_path( __FILE__ );
	} else {
		return plugin_dir_path( $file, __FILE__ );
	}
}

function oi_yamaps_url( $file = '' ) {
	if ( empty( $file ) ) {
		return plugins_url( __FILE__ );
	} else {
		return plugins_url( $file, __FILE__ );
	}
}


add_action( 'init', 'oi_yamaps' );

// localization
function oi_yamaps() {
	load_plugin_textdomain( 'oiyamaps', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
	add_action( 'admin_footer', 'oi_yamaps_thickbox' );
	add_action( 'media_buttons', 'oiyamaps__button', 11 );
}

// do something on plugin activation
register_activation_hook( __FILE__, 'oi_yamaps_activation' );


function get_api_names( $key ) {
	$names = array(
		'controls' => array(
			'fullscreenControl',
			'geolocationControl',
			'rulerControl',
			//'mapTools', // depricated
			'routeEditor',
			'typeSelector',
			'zoomControl',
			//'smallZoomControl', // depricated
			//'scaleLine', // depricated
			//'miniMap', // depricated
			'searchControl',
			'trafficControl',
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
		'lang'              => get_locale(),
		'height'            => '400px',
		'width'             => '100%',
		'zoom'              => '16',
		'placemark'         => 'twirl#blueDotIcon',
		'author_link'       => '1',
		'show_by_click'     => 0,
		'address'           => '',
		'center'            => '',
		'header'            => '',
		'body'              => '',
		'footer'            => '',
		'hint'              => '',
		'coordinates'       => '',
		'iconimage'         => '',
		'button_caption'    => __( 'Show the map', 'oiyamaps' ),
		'iconcontent'       => '',
		'iconsize'          => '',
		'iconoffset'        => '',
		'iconrect'          => '',
		'zoomcontrol'       => 1,
		'typeselector'      => 1,
		'maptools'          => 1,
		'trafficcontrol'    => 1,
		'routeeditor'       => 1,
		'controls'          => 'zoomcontrol,typeselector,maptools,trafficcontrol,routeeditor,scaleLine',
		//'controls'          => 'zoomcontrol,typeselector,maptools,trafficcontrol,routeeditor,scaleLine,miniMap,smallZoomControl,searchControl',
		'behaviors-disable' => 'LeftMouseButtonMagnifier,DblClickZoom,scrollZoom',
	);

	return $defaults;
}

function oi_yamaps_js() // подключение js
{
	wp_enqueue_script( 'oi_yamaps_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '2015-10-21', true );
}

add_action( 'admin_enqueue_scripts', 'oi_yamaps_js' ); // встраиваем скрипт в футер

// set default variables on plugin activation
function oi_yamaps_activation() {
	// if we don't have any settengs
	if ( ! get_option( OIYM_PREFIX . 'options' ) ) {
		update_option( OIYM_PREFIX . 'options', oi_yamaps_defaults() );
	}
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

// проверка - включен ли модуль cURL
function _isCurl() {
	return function_exists( 'curl_version' );
}

// получение контента через cURL
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

function oiyamap_geocode( $place ) {
	// формирование ключа кэша
	$key     = md5( $place );
	$content = '';

	// если в кэше еще нет данных с данным ключом
	if ( WP_DEBUG == true || ! ( $res = wp_cache_get( $key, 'oi-ya-maps' ) ) ) {
		// получение данных от Яндекса в формате JSON
		$place = urlencode( $place );

		$url = "https://geocode-maps.yandex.ru/1.x/?geocode=" . $place . '&format=json';

		// получение методом HTTP GET
		$content = wp_remote_get( $url, apply_filters( 'oiyamaps_wp_remote_get_args', array() ) );
		// если ошибок при получении не возникло
		if ( ! is_wp_error( $content ) ) {
			// получение контента
			$content = wp_remote_retrieve_body( $content );

			// если не удалось получить данные через wp_remote_get
		} else {


			// если произошла ошибка
			if ( ! ( $content = @file_get_contents( $url ) ) ) {
				// если модуль cURL подключен
				if ( _isCurl() ) {
					// получение данных через cURL
					$content = curl_get_contents( $url ); // используем для получения данных curl
				} else {
					return __( 'To show the map cURL must be enabled.', 'oiyamaps' );
				}
			}
		}
		$content = json_decode( $content, true );

		wp_cache_set( $key, $content, 'oi-ya-maps', HOUR_IN_SECONDS * 24 );
	}

	return $content;
}


/**
 * Функция определяет что в нее передано и каким образом она вызвана, в результате обработки данных возвращает массив,
 * содержащий информацию об адресе, его координатах и флаг, указывающий на то,
 * что было передано - координаты($result[2]=true) или адрес($result[2]=false)
 *
 * @param null $place
 *
 * @return array
 */
function oiyamaps_get_place( $place = null ) {
	$address     = '';
	$coordinates = '';
	$flag        = true;

	// флаг, указывающий на то, что пользователь указал адрес, а не координаты
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

			// функция вычисления координат по предоставленному адресу
			$address = oiyamap_geocode( $coordinates );

			$address     = $address['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];
			$coordinates = implode( ',', array_reverse( explode( ',', $coordinates ) ) );

			$is_coordinates = true;
		} else {

			$coordinates = oiyamap_geocode( urldecode( $place ) );
			$address     = $coordinates['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['text'];

			// определение координат
			$coordinates = $coordinates['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];

			// расположение координат в формате "широта, долгота"
			$coordinates = implode( ',', array_reverse( explode( ' ', trim( $coordinates ) ) ) );
		}
	}

	$result = array( $address, $coordinates, $is_coordinates );

	if ( $flag == false ) {
		wp_send_json( $result );
	}

	return $result;
}

add_action( 'wp_ajax_' . 'oiyamaps_get_place', 'oiyamaps_get_place' );

/**
 * Returns an associated array where lowercase key has original value.
 *
 * @return array
 */
function get_match_list( $list ) {

	$associated_list = [];
	if ( ! empty( $list ) ) {
		foreach ( $list as $item ) {
			$associated_list[ strtolower( $item ) ] = $item;
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
 * Perform controls list for JavaScript API
 *
 * @param $enable_controls
 *
 * @return array|string
 */
function controls_add( $enable_controls ) {
pr($enable_controls);
	// get controls match list
	$controls = get_match_list( get_api_names( 'controls' ) );

	// if $enable_controls not emty
	if ( ! empty( $enable_controls ) ) {

		// if $enable_controls is a string
		if ( is_string( $enable_controls ) ) {

			// convert it to an array
			$enable_controls = explode( ',', $enable_controls );
		}

		// trim every element
		$enable_controls = array_map( 'trim', $enable_controls );
		$enable_controls = array_map( 'strtolower', $enable_controls );
	} else {

		// set $enable_controls equal to $controls
		$enable_controls = $controls;
		unset( $enable_controls['smallzoomcontrol'] );
		//unset( $enable_controls['searchcontrol'] );
		$enable_controls = array_keys( $enable_controls );
	}

	// set new array
	$controls_add    = array();
	$controls_remove = array();

	// loop control list
	foreach ( $controls as $key => $value ) {

		// if control exists
		if ( in_array( $key, $enable_controls ) ) {

			// add list
			$controls_add[] = $value;
		} else {

			// remove list
			$controls_remove[] = $value;
		}
	}

	// remove control
	$controls_remove = array_cut_list( array(
		'list'   => $controls_remove,
		'before' => "\n" . '.remove("',
		'after'  => '")',
	) );

	// add control
	$controls_add = array_cut_list( array(
		'list'   => $controls_add,
		'before' => "\n" . '.add("',
		'after'  => '")',
	) );


	$controls = array();

	// -- save the order: remove then add -- //

	// if $controls_remove not empty
	if ( ! empty( $controls_remove ) ) {

		$controls[] = 'myMap.controls' . $controls_remove . ';';
	}

	// if $controls_add not empty
	if ( ! empty( $controls_add ) ) {

		$controls[] = 'myMap.controls' . $controls_add . ';';
	}


	return implode( "\n", $controls );
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
	// get attributes from options
	$option = wp_parse_args( get_option( OIYM_PREFIX . 'options' ), oi_yamaps_defaults() );
pr($option);
pr($atts);
	// get attributes of concrete map
	$atts = wp_parse_args( $atts, $option );

	$out                = '';
	$vars               = array();
	$vars['placemarks'] = array();

	if ( empty( $atts['button_caption'] ) ) {
		$atts['button_caption'] = __( 'Показать карту', 'oiyamaps' );
	}

	if ( ! empty( $atts['behaviors-disable'] ) ) {
		$atts['behaviors-disable'] = "myMap.behaviors.disable([" . array_cut_list( array(
				'list'    => $atts['behaviors-disable'],
				'before'  => "'",
				'between' => ',',
				'after'   => "'",
			) ) . "]);";
	}

	// perform controls list
	$atts['controls'] = controls_add( $atts['controls'] );


	// set id of map block
	$id = Ya_map_connected::$id;

	// if coordinates not set...
	if ( empty( $atts['coordinates'] ) ) {

		// if we have an address, then...
		if ( ! empty( $atts['address'] ) ) {

			// take coordinates
			$place = oiyamaps_get_place( $atts['address'] );

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
			$atts['author_link'] = '<a class="oi_yamaps_author_link" target="_blank" href="https://oiplug.com/">' . __( 'OiYM', 'oi_ya_maps' ) . '</a>';
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
		' . $atts['behaviors-disable'] . '
		' . $atts['controls'] . '
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

add_shortcode( 'showyamap', 'showyamap' );
add_shortcode( 'yamap', 'showyamap' );


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
		'placemark'   => '',
		'iconimage'   => '',
		'iconsize'    => '',
		'iconoffset'  => '',
		'iconrect'    => '',
	), $atts );

	// if content for placemark given, make placemark stretch
	if ( $atts['iconcontent'] != '' ) {
		$atts['placemark'] = str_replace( 'Icon', 'StretchyIcon', str_replace( 'Dot', '', $atts['placemark'] ) );
	}

	if ( $atts['iconcontent'] ) {
		$atts['iconcontent'] = 'iconContent: "' . $atts['iconcontent'] . '",';
	}
	if ( $atts['header'] ) {
		$atts['header'] = 'balloonContentHeader: "' . $atts['header'] . '",';
	}
	if ( $atts['body'] ) {
		$atts['body'] = 'balloonContentBody: "' . $atts['body'] . '",';
	}
	if ( $atts['footer'] ) {
		$atts['footer'] = 'balloonContentFooter: "' . $atts['footer'] . '",';
	}
	if ( $atts['hint'] ) {
		$atts['hint'] = 'hintContent: "' . $atts['hint'] . '"';
	}

	if ( $atts['iconimage'] ) {
		$atts['iconimage'] = 'iconImageHref: "' . $atts['iconimage'] . '", ';
	}
	if ( $atts['iconsize'] ) {
		$atts['iconsize'] = 'iconImageSize: ' . oi_ya_map_brackets( $atts['iconsize'] ) . ', ';
	}
	if ( $atts['iconoffset'] ) {
		$atts['iconoffset'] = 'iconImageOffset: ' . oi_ya_map_brackets( $atts['iconoffset'] ) . ' ';
	}
	if ( $atts['iconrect'] ) {
		$atts['iconrect'] = 'iconImageClipRect: ' . oi_ya_map_brackets( $atts['iconrect'] ) . ' ';
	}
	if ( ! empty( $atts['placemark'] ) && ! $atts['iconimage'] ) {
		$atts['placemark'] = 'preset: "' . $atts['placemark'] . '"';
	} else {
		$atts['placemark'] = '';
	}

	$output = '
				myPlacemark_' . $atts['pid'] . ' = new ymaps.Placemark([' . $atts['coordinates'] . '], {' .
	          $atts['iconcontent'] .
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
			$place = oiyamaps_get_place( $atts['address'] );

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

add_shortcode( 'placemark', 'placemark' );


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

