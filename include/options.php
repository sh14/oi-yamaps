<?php

namespace oiyamaps;

function oiym_fields() {
	$fields = array(
		'apikey'     => array(
			'title'   => __( 'API Key', 'oi-yamaps' ),
			'type'    => 'text',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			//'hint'    => __( 'Use px or %. 400px by default', 'oi-yamaps' ),
		),
		'height'      => array(
			'title'   => __( 'Map height', 'oi-yamaps' ),
			'type'    => 'text',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			'hint'    => __( 'Use px or %. 400px by default', 'oi-yamaps' ),
		),
		'width'       => array(
			'title'   => __( 'Map width', 'oi-yamaps' ),
			'type'    => 'text',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			'hint'    => __( 'Use px or %. 100% by default', 'oi-yamaps' ),
		),
		'zoom'        => array(
			'title'   => __( 'Map zoom', 'oi-yamaps' ),
			'type'    => 'text',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			'hint'    => __( '16 by default', 'oi-yamaps' ),
		),
		'placemark'   => array(
			'title'   => __( 'Default placemark', 'oi-yamaps' ),
			'type'    => 'text',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			'hint'    => sprintf( __( 'You can use different placemarks. Checkout that page - %s', 'oi-yamaps' ), '<a target="_blank" href="https://tech.yandex.ru/maps/doc/jsapi/2.1/ref/reference/option.presetStorage-docpage/">https://tech.yandex.ru/maps/...</a>' ),
		),
		'author_link' => array(
			'title'   => __( "Show link to the plugin's page", 'oi-yamaps' ),
			'type'    => 'checkbox',
			'section' => 'setting_section_1',
			'page'    => 'oiym-setting-admin',
			'hint'    => __( 'It is just a link to the plugin page in corner of the map.', 'oi-yamaps' ),
		),
	);

	return $fields;
}

function oiym_psf( $atts ) {
	$fields = oiym_fields();
	$atts   = wp_parse_args( $atts, array(
		'key'         => '',
		'type'        => '',
		'before'      => null,
		'after'       => null,
		'width'       => '200px',
		'placeholder' => '',
		'hint'        => '',
		'value'       => '',
		'readonly'    => false,
		'disabled'    => false,
		'required'    => '',
		'addon'       => '',
	) );

	$out = '';

	if ( empty( $atts['hint'] ) ) {
		$atts['hint'] = ! empty( $fields[ $atts['key'] ]['hint'] ) ? $fields[ $atts['key'] ]['hint'] : '';
	}
	if ( $atts['type'] == '' ) {
		$atts['type'] = $fields[ $atts['key'] ]['type'];
	}
	if ( $atts['key'] ) {
		if ( $atts['hint'] ) {
			$atts['hint'] = '<p class="help-block description">' . $atts['hint'] . '</p>';
		}
		if ( $atts['placeholder'] ) {
			$atts['placeholder'] = ' placeholder="' . $atts['placeholder'] . '"';
		}
		if ( $atts['readonly'] == true ) {
			$atts['readonly'] = ' readonly';
		} else {
			$atts['readonly'] = '';
		}
		if ( $atts['disabled'] == true ) {
			$atts['disabled'] = ' disabled="disabled"';
		} else {
			$atts['disabled'] = '';
		}
		if ( $atts['width'] == '200px' && $atts['type'] == 'textarea' ) {
			$atts['style'] = 'style="width: 98%;height: 100px;"';
		} else {
			$atts['style'] = '';
		}
		$atts['addon'] = $atts['style'] . $atts['readonly'] . $atts['disabled'] . $atts['placeholder'] . ' ' . $atts['required'] . ' ' . $atts['addon'];
		if ( $atts['type'] == 'checkbox' ) {
			$atts['class'] = ' class="checkbox-inline"';
		} else {
			$atts['class'] = '';
		}
		if ( $atts['before'] ) {
			$atts['before'] = '<label' . $atts['class'] . ' for="' . $atts['key'] . '">' . $atts['before'] . '</label>';
		}
		if ( $atts['after'] ) {
			$atts['after'] = '<label' . $atts['class'] . ' style="margin-right:25px;" for="' . $atts['key'] . '">' . $atts['after'] . '</label>';
		}

		switch ( $atts['type'] ) {
			case 'select':
				$out = $atts['before'] .
				       '<select class="form-control" id="' . $atts['key'] . '" name="' . $atts['key'] . '"' . $atts['addon'] . '>' .
				       $atts['value'] .
				       '</select>' .
				       $atts['after'] . $atts['hint'];
				break;
			case 'text':
				$out = $atts['before'] . '<input type="' . $atts['type'] . '" id="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" name="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" class="regular-text" value="' . $atts['value'] . '" ' . $atts['addon'] . '/>' . $atts['after'] . $atts['hint'];
				break;
			case 'hidden':
				$out = $atts['before'] . '<input type="' . $atts['type'] . '" id="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" name="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" value="' . $atts['value'] . '">' . $atts['after'] . $atts['hint'];
				break;
			case 'checkbox':
				if ( $atts['value'] == '1' ) {
					$checked_flag = ' checked';
				} else {
					$checked_flag = '';
				}
				$out = $atts['before'] . '<input type="' . $atts['type'] . '" id="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" name="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']"' . ' value="1"' . $checked_flag . '' . $atts['addon'] . '>' . $atts['after'] . $atts['hint'];
				break;
			case 'textarea':
				$out = $atts['before'] . '<textarea class="wp-editor-area" id="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" name="' . __NAMESPACE__ . '_options[' . $atts['key'] . ']" ' . $atts['addon'] . '>' . $atts['value'] . '</textarea>' . $atts['after'] . $atts['hint'];
				break;
		}
	}

	return $out;
}


class OIYM_SettingsPage {
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			__( 'Oi Yandex.Maps Settings', 'oi-yamaps' ),
			__( 'Oi Yandex.Maps', 'oi-yamaps' ),
			'manage_options',
			'oiym-setting-admin',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function settings_page() {
		// Set class property
		$this->options = ( get_option( __NAMESPACE__ . '_options', oi_yamaps_defaults() ) );
		?>

        <div class="wrap">
            <h2><?php _e( 'Oi Yandex.Maps Settings', 'oi-yamaps' ); ?></h2>
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( __NAMESPACE__ . '_option_group' );
				submit_button();
				do_settings_sections( 'oiym-setting-admin' );
				submit_button();
				?>
            </form>

        </div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init() {
		register_setting(
			__NAMESPACE__ . '_option_group', // Option group
			__NAMESPACE__ . '_options', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		$sections = $this->sections();
		foreach ( $sections as $section => $val ) {
			$callback = array( $this, $section . '_callback' );
			add_settings_section(
				$section,                                // ID - ключ элемента
				$val['title'],                            // Заголовок
				$callback,    // Функция вывода
				$val['page']                            // Страница, на которой расположен элемент
			);
		}
		$fields = oiym_fields();
		foreach ( $fields as $field => $val ) {
			$callback = array( $this, $field . '_callback' );
			add_settings_field(
				$field,                                // ID - ключ элемента
				$val['title'],                        // Заголовок
				$callback,    // Функция вывода
				$val['page'],                        // Страница, на которой расположен элемент
				$val['section']                        // Группа в которой расположен элемент
			);
		}

	}

	// fields and theme attributes
	public function sections() {
		$fields = array(
			'setting_section_1' => array(
				'title' => __( 'Map defaults', 'oi-yamaps' ),
				'page'  => 'oiym-setting-admin',
			),
			'setting_section_2' => array(
				'title' => __( 'Info', 'oi-yamaps' ),
				'page'  => 'oiym-setting-admin',
			),
		);

		return $fields;
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		$new_input = array();
		$fields    = oiym_fields();
		foreach ( $fields as $field => $val ) {
			if ( isset( $input[ $field ] ) ) {
				if ( $val['type'] == 'number' ) {
					$new_input[ $field ] = absint( $input[ $field ] );
				}
				if ( $val['type'] == 'text' ) {
					$new_input[ $field ] = sanitize_text_field( $input[ $field ] );
				}
				if ( $val['type'] == 'checkbox' ) {
					$new_input[ $field ] = absint( $input[ $field ] );
				}
			}
		}

		return $new_input;
	}
	/* START: EDIT HERE */
	/**
	 * Print the Section text
	 */
	public function setting_section_1_callback() {

		_e( 'Default parameters', 'oi-yamaps' );
	}

	public function setting_section_2_callback() {
		?>
        <style>

            .myButton {
                -moz-box-shadow: inset 0 1px 0 0 #9acc85;
                -webkit-box-shadow: inset 0 1px 0 0 #9acc85;
                box-shadow: inset 0 1px 0 0 #9acc85;
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));
                background: -moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);
                background: -webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);
                background: -o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);
                background: -ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);
                background: linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#74ad5a', endColorstr='#68a54b', GradientType=0);
                background-color: #74ad5a;
                border: 1px solid #3b6e22;
                display: table;
                cursor: pointer;
                color: #fff;
                font-size: 20px;
                padding: 15px;
                text-decoration: none;
                text-align: center;
            }

            .myButton:hover {
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));
                background: -moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);
                background: -webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);
                background: -o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);
                background: -ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);
                background: linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#68a54b', endColorstr='#74ad5a', GradientType=0);
                background-color: #68a54b;
                color: #fff;
            }

            .myButton:active {
                position: relative;
                top: 1px;
                color: #fff;
            }
        </style>
        <div class="oiplug_ad">

            <p>
                <a href="https://oiplug.com/plugins/oi-yandex-maps-for-wordpress/?utm_source=wordpress&utm_medium=adminbar&utm_campaign=documentation&utm_content=1"
                   target="_blank"
                   class="myButton"><?php _e( 'Documentation', 'oi-yamaps' ); ?></a></p>
        </div>

		<?php
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function height_callback() {
		$key = 'height';
		print oiym_psf( array( 'key' => $key, 'value' => esc_attr( $this->options[ $key ] ), ) );
	}

	public function apikey_callback() {
		$key   = 'apikey';
		$value = ! empty( $this->options[ $key ] ) ? esc_attr( $this->options[ $key ] ) : '';
		print oiym_psf( array( 'key' => $key, 'value' => $value, ) );
	}

	public function width_callback() {
		$key = 'width';
		print oiym_psf( array( 'key' => $key, 'value' => esc_attr( $this->options[ $key ] ), ) );
	}

	public function zoom_callback() {
		$key = 'zoom';
		print oiym_psf( array( 'key' => $key, 'value' => esc_attr( $this->options[ $key ] ), ) );
	}

	public function placemark_callback() {
		$key = 'placemark';
		print oiym_psf( array( 'key' => $key, 'value' => esc_attr( $this->options[ $key ] ), ) );
	}

	public function author_link_callback() {
		$key = 'author_link';
		if ( ! empty( $this->options[ $key ] ) && $this->options[ $key ] !== 0 ) {
			$value = 1;
		} else {
			$value = 0;
		}
		print oiym_psf( array( 'key' => $key, 'value' => $value, ) );
	}

	/* END: EDIT HERE */

}

if ( is_admin() ) {
	$my_settings_page = new OIYM_SettingsPage();
}

// eof
