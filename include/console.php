<?php
/**
 * Created by PhpStorm.
 * User: sh14ru
 * Date: 02.03.18
 * Time: 18:41
 */

namespace oiyamaps;

/**
 * Enqueue script and styles to console
 */
function console_enqueue_script() {
	// load all media sources for using in admin.js
	wp_enqueue_media();
	wp_enqueue_script( 'oi_yamaps_admin', Plugin::$data['url'] . 'js/admin.js', array( 'jquery' ), null, true );
	$options = get_option( __NAMESPACE__.'_options' );
	// todo: удалить след. строку и обновить настройки
	//$options = oi_yamaps_defaults();
	wp_localize_script( 'oi_yamaps_admin', 'oiyamaps', array(
		'options'      => $options,
		'id'           => array(),
		//'controls'     => implode( ',', get_match_list( get_api_names( 'controls' ) ) ),
		//'behaviors'    => implode( ',', get_match_list( get_api_names( 'behaviors' ) ) ),
		'localization' => array(
			'have_to_fillin' => __( 'Field cannot be empty', 'oi-yamaps' ),
		),
	) );
	wp_enqueue_style( 'custom_wp_admin_css', Plugin::$data['url'] . 'css/style.css' );
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\console_enqueue_script' );

/**
 * Function gets icons from Yandex documentation and build the icon list
 *
 * @return array|string
 */
function get_icons() {
	if ( ! $matches = get_transient( 'presets' ) ) {
		$preset_storage = wp_remote_get( 'https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/' );
		$preset_storage = $preset_storage['body'];
		preg_match_all( '/b-img-container.*?img.*?src="(.*?)".*?<td\>\'(.*?)\'\<\/td\>/si', $preset_storage, $matches );

		$matches = array( $matches[1], $matches[2] );

		set_transient( 'presets', $matches, 3600 * 24 * 30 );
	}

	list( $images, $names ) = $matches;
	$items = array();
	foreach ( $images as $i => $image ) {
		$items[] = '<li class="oiyamaps-icons__item" style="background-image: url(' . $image . ');" data-name="' . $names[ $i ] . '"></li>';
	}
	$items = '<div class="oiyamaps-icons"><ul class="oiyamaps-icons__list">' . implode( "\n", $items ) . '</ul></div>';

	return $items;
}

function get_edit_form() {

	$fields = array(
		'placemark' => array(
			'address'     => array(
				'type'        => 'text',
				'class'       => 'oiyamaps-form__control js-address_oiyamaps',
				'attributes'  => array(
					'data' => array(
						'coordinates' => '.js-coordinates_oiyamaps',
						'address'     => '.js-address_oiyamaps',
					),
				),
				'label_class' => 'oiyamaps-form__label',
				'placeholder' => __( 'Address or coordinates', 'oi-yamaps' ),
				'label'       => __( 'Address or coordinates', 'oi-yamaps' ),
				'hint'        => __( 'Type an address or coordinates. Address example: Moscow; Coordinates example: 55.755814,37.617635 ', 'oi-yamaps' ),
			),
			'coordinates' => array(
				'type'  => 'hidden',
				'class' => 'js-coordinates_oiyamaps',
			),
			'header'      => array(
				'type'        => 'text',
				'placeholder' => __( 'Baloon header', 'oi-yamaps' ),
				'label'       => __( 'Baloon header', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'body'        => array(
				'type'        => 'text',
				'placeholder' => __( 'Baloon body content', 'oi-yamaps' ),
				'label'       => __( 'Baloon body content', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'footer'      => array(
				'type'        => 'text',
				'placeholder' => __( 'Baloon footer', 'oi-yamaps' ),
				'label'       => __( 'Baloon footer', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'hint'        => array(
				'type'        => 'text',
				'placeholder' => __( 'Placemark hint', 'oi-yamaps' ),
				'label'       => __( 'Placemark hint', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'iconcontent' => array(
				'type'        => 'text',
				'placeholder' => __( 'Plcamark label', 'oi-yamaps' ),
				'label'       => __( 'Plcamark label', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'iconcaption' => array(
				'type'        => 'text',
				'placeholder' => __( 'Plcamark label', 'oi-yamaps' ),
				'label'       => __( 'Plcamark label', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			'placemark'   => array(
				'type'        => 'text',
				'placeholder' => __( 'Placemark type', 'oi-yamaps' ),
				'label'       => __( 'Placemark type', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
			),
			array(
				'type'       => 'submit',
				'attributes' => array(
					'data' => array(
						'gist' => 'placeholder',
					),
				),
				//'class'      => 'oiyamaps-button oiyamaps-button_primary oiyamaps-button_block js-submit_oiyamaps',
				'class'      => 'oiyamaps-button oiyamaps-button_primary oiyamaps-button_block',
				'value'      => __( 'Add placemark', 'oi-yamaps' ),
			),
			array(
				//'name' => 'controls',
				'type'  => 'button',
				'class' => 'oiyamaps-button oiyamaps-button_block oiyamaps-form__cancel js-cancel_oiyamaps',
				'value' => __( 'Cancel', 'oi-yamaps' ),
			),
		),
		'map'       => array(
			'center' => array(
				'type'        => 'text',
				'placeholder' => __( 'Map center', 'oi-yamaps' ),
				'label'       => __( 'Map center', 'oi-yamaps' ),
				'class'       => 'oiyamaps-form__control js-center_oiyamaps',
				'attributes'  => array(
					'data' => array(
						//'address'=>'.js-address_oiyamaps',
						'coordinates' => '.js-center_oiyamaps',
					),
				),
				'label_class' => 'oiyamaps-form__label',
				'hint'        => __( 'It should be a coordinates, like 55.754736,37.620875', 'oi-yamaps' )
				                 . '<br>'
				                 . __( 'By default center = coordinates', 'oi-yamaps' ),
			),
			'height' => array(
				'type'        => 'text',
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
				'placeholder' => __( 'Map height', 'oi-yamaps' ),
				'label'       => __( 'Map height', 'oi-yamaps' ),
			),
			/*		'width'  => array(
						'type'        => 'number',
						'class'       => 'oiyamaps-form__control',
						'label_class' => 'oiyamaps-form__label',
						'placeholder' => __( 'Map width', 'oi-yamaps' ),
						'label'      => __( 'Map width', 'oi-yamaps' ),
					),*/
			'zoom'   => array(
				'type'        => 'number',
				'class'       => 'oiyamaps-form__control',
				'label_class' => 'oiyamaps-form__label',
				'attributes'  => array(
					'min' => 1,
					'max' => 19,
				),
				'placeholder' => __( 'Map zoom', 'oi-yamaps' ),
				'label'       => __( 'Map zoom', 'oi-yamaps' ),
			),
			array(
				'name'        => 'controls',
				'type'        => 'html',
				'label'       => __( 'Map controls', 'oi-yamaps' ),
				'label_class' => 'oiyamaps-form__label',
				'html'        => '%label%'
				                 . '<div class="oiyamaps-form__multiselect">'
				                 . multiselect( array(
						'key'    => 'controls',
						'class'  => 'oiyamaps-form',
						'values' => get_default_api_names( 'controls' ),
					) )
				                 . '</div>',
			),
			array(
				'name'        => 'behaviors',
				'type'        => 'html',
				'label'       => __( 'Map options', 'oi-yamaps' ),
				'label_class' => 'oiyamaps-form__label',
				'html'        => '%label%'
				                 . '<div class="oiyamaps-form__multiselect">'
				                 . multiselect( array(
						'key'    => 'behaviors',
						'class'  => 'oiyamaps-form',
						'values' => get_default_api_names( 'behaviors' ),
					) )
				                 . '</div>',
			),
		),
	);

	$options = wp_parse_args( get_option( __NAMESPACE__.'_options' ), oi_yamaps_defaults() );

	$template = '<div class="oiyamaps-form__group">'
	            . '%label%'
	            . '<div class="oiyamaps-form__input">%%</div>'
	            . '<div class="oiyamaps-form__hint">%hint%</div>'
	            . '</div>';

	// loop filds sets
	foreach ( $fields as $gist => $gist_fields ) {

		// loop field set
		foreach ( $gist_fields as $key => $field ) {

			// if field has name
			if ( ! is_numeric( $key ) ) {

				// set field name
				$fields[ $gist ][ $key ]['name'] = $key;

				// if hint is empty
				if ( empty( $field['hint'] ) ) {
					$fields[ $gist ][ $key ]['hint'] = '';
				}

				// if default value doesn't empty
				if ( ! empty( $options[ $key ] ) ) {

					// set default value to the hint
					$fields[ $gist ][ $key ]['hint'] .= __( 'Default: ', 'oi-yamaps' ) . $options[ $key ];
				}

				// intval value if type of field is a number
				if ( $fields[ $gist ][ $key ]['type'] == 'number' ) {
					$value = intval( $options[ $key ] );
				} else {
					$value = $options[ $key ];
				}

				// set the field value
				$fields[ $gist ][ $key ]['value'] = $value;

				// make html if field type doesn't hidden
				if ( $fields[ $gist ][ $key ]['type'] != 'hidden' ) {
					$fields[ $gist ][ $key ]['html'] = $template;
				}
			}

		}
	}

	if ( function_exists( 'oinput_form' ) ) {
		$out = '<ul class="oiyamaps-form__slider js-slider">'
		       . '<li class="oiyamaps-form__slider-item js-slider-item active">'
		       . '<div class="oiyamaps__shortcodes js-placemark_list_oiyamaps"></div>'
		       . '<button class="oiyamaps-button oiyamaps-button_black oiyamaps-button_right js-placemark_form_show_oiyamaps">+</button>'
		       . oinput_form( $fields['placemark'], array(
				'attributes' => array(
					'class' => 'oiyamaps-form oiyamaps-hidden js-form_oiyamaps js-placemark_form_oiyamaps',
					'data'  => array(
						'gist' => 'placemark',
					),
				),
				'echo'       => false,
			) )
		       . '</li>'
		       . '<li class="oiyamaps-form__slider-item js-slider-item">'
		       . oinput_form( $fields['map'], array(
				'attributes' => array(
					'class' => 'oiyamaps-form js-form_oiyamaps js-form_map_oiyamaps',
					'data'  => array(
						'gist' => 'map',
					),
				),
				'echo'       => false,
			) )
		       . '</li>'
		       . '</ul>';
	} else {
		$out = 'необходимо установить плагин Oi Nput.';
	}

	return $out;
}

/**
 * Add button to media_buttons list
 */
function get_modal() {
	$title = __( 'Oi Yandex.Maps', 'oi-yamaps' );
	$out   = '<button type="button" class="js-modal_show_oiyamaps button" title="' . $title . '">' . $title . '</button>';

	echo $out;
}

add_action( 'media_buttons', __NAMESPACE__ . '\get_modal', 11 );

/**
 * Layout the modal window HTML
 */
function show_modal() {
	the_template_part( 'modal-box' );
}

add_action( 'admin_footer', __NAMESPACE__ . '\show_modal' );


// eof
