<?php

namespace oiyamaps;

//Создаёт кнопку для перехода к страницу настроек плагина в админке WP
function admin_page() {
	add_options_page( 'options',
		__( 'Oi Yandex.Maps', 'xxx' ),
		'manage_options',
		__FILE__ . 'options_page',
		'\oiyamaps\options_page'
	);
}

// Добавление нового пункта меню в админку
add_action( 'admin_menu', '\oiyamaps\admin_page' );

function option_manage_level() {
	return 'administrator';
}

/**
 * Функция создания и обработки страницы настроек плагина
 */
function options_page() {

	// определение настроек по умолчанию
	$default_options = get_options();
	$options         = \get_option( 'oiyamaps-settings' );

	// если из формы были переданы данные
	if ( isset( $_POST['oiyamaps-settings'] ) && ! empty( $_POST['oiyamaps-settings'] ) && option_manage_level() ) {
		$options = $_POST['oiyamaps-settings'];

		$options = wp_parse_args( $options, $default_options );

		// настройки плагина обновляются
		update_option( 'oiyamaps-settings', $options );

	}

	// шаблон вывода поля
	$template      = '<tr><th>%label%</th><td>%%<p><span class="help-block description">%hint%</span></p></td></tr>';
	$template_sub  = '<tr><th></th><td><b>%label%:</b>%%<p><span class="help-block description">%hint%</span></p></td></tr>';
	$template_dash = '<tr><td colspan="2"><hr/><td></tr>';

	$levels = array(
		'subscriber'    => __( 'Subscriber' ),
		'contributor'   => __( 'Contributor' ),
		'author'        => __( 'Author' ),
		'editor'        => __( 'Editor' ),
		'administrator' => __( 'Administrator' ),
	);
	echo oinput( array(
		'type'  => 'option',
		'name'  => $levels,
		'value' => $options['admin_bar_remove_level'],

	) );

	$fields     = array(
		array(
			'type' => 'html',
			'html' => '<div class="wrap">' .
			          '<h2>' . __( 'Oi Frontend Profile', 'oiyamaps' ) . '</h2>' .
			          '<h3>' . __( 'Settings', 'oiyamaps' ) . '</h3>',
		),
		array(
			'type' => 'html',
			'html' => '<table class="form-table">',
		),

	);
	$fields_all = get_edit_form();
	$fields     = array_merge( $fields, array(
		array(
			'type'  => 'html',
			'label' => __( 'Profile page', 'oiyamaps' ),
			'after' => __( 'This page will be displayed when editing a profile', 'oiyamaps' ),
			'hint'  => __( sprintf( 'This page MUST contain %s shortcode', '<b>[oiyamaps content="profile"]</b>' ), 'oiyamaps' ),
			'field' => wp_dropdown_pages( array(
				'child_of'     => 0,
				'sort_order'   => 'ASC',
				'sort_column'  => 'post_title',
				'hierarchical' => 0,
				'name'         => 'oiyamaps-settings[profile_post_id]',
				'selected'     => $options['profile_post_id'],
				'post_type'    => 'page',
				'echo'         => 0,
			) ),
			'html'  => str_replace( '%%', '%field%', $template ),
		),
		'save'   => array(

			'type' => 'hidden',

		),
		array(
			'type' => 'html',
			'html' => '</table>',
		),
		'submit' => array(
			'type'  => 'submit',
			'class' => 'button button-primary',
			'value' => __( 'Save Changes', 'oiyamaps' ),
			'html'  => '<p class="submit">%%</p>',
		),
		array(
			'type' => 'html',
			'html' => '</div>',
		),
	) );

	// перебор каждой опции
	foreach ( $fields as $key => $field ) {


		if ( ! is_numeric( $key ) && empty( $fields[ $key ]['name'] ) ) {
			$fields[ $key ]['name'] = 'oiyamaps-settings[' . $key . ']';
		}

		// если текущая опция не указана
		if ( ! empty( $options[ $key ] ) && ( $field['type'] != 'html' || $field['type'] != 'submit' ) ) {

			// берется значение по умолчанию
			//$fields[ $key ]['value'] = $options[ $key ];
		}
	}

	$atts = array(
		'action' => $_SERVER['REQUEST_URI'],
		'method' => 'post',
		'echo'   => true,
	);

	oinput_form( $fields, $atts );
	/*
		?>
		<div class="wrap">
			<h2>Oi Frontend Profile</h2>
			<form method="post">
				<table>
					<?php
					foreach ( $fields as $field ) {
						the_oinput( $field );
					}
					?>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
										 value="<?php  _e( 'Save Changes' ) ?>"></p>
			</form>
		</div>

		<?php
	*/
}


/*------------------------*/


function wp_dropdown_pages( $args = '' ) {
	$defaults = array(
		'depth'                 => 0,
		'child_of'              => 0,
		'selected'              => 0,
		'echo'                  => 1,
		'name'                  => 'page_id',
		'id'                    => '',
		'class'                 => '',
		'show_option_none'      => '',
		'show_option_no_change' => '',
		'option_none_value'     => '',
		'value_field'           => 'ID',
		'before_options'        => '',
		'after_options'         => '',
	);

	$r = wp_parse_args( $args, $defaults );

	$pages  = get_pages( $r );
	$output = '';
	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $r['id'] ) ) {
		$r['id'] = $r['name'];
	}

	if ( ! empty( $pages ) ) {
		$class = '';
		if ( ! empty( $r['class'] ) ) {
			$class = " class='" . esc_attr( $r['class'] ) . "'";
		}

		$output = "<select name='" . esc_attr( $r['name'] ) . "'" . $class . " id='" . esc_attr( $r['id'] ) . "'>\n";
		if ( $r['show_option_no_change'] ) {
			$output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
		}
		if ( $r['show_option_none'] ) {
			$output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
		}
		$output .= walk_page_dropdown_tree( $pages, $r['depth'], $r );
		$output .= "</select>\n";
	}

	/**
	 * Filter the HTML output of a list of pages as a drop down.
	 *
	 * @since 2.1.0
	 * @since 4.4.0 `$r` and `$pages` added as arguments.
	 *
	 * @param string $output HTML output for drop down list of pages.
	 * @param array  $r      The parsed arguments array.
	 * @param array  $pages  List of WP_Post objects returned by `get_pages()`
	 */
	$html = apply_filters( 'wp_dropdown_pages', $output, $r, $pages );

	if ( $r['echo'] ) {
		echo $html;
	}

	return $html;
}

