<?php
/**
 * Created by PhpStorm.
 * User: sh14ru
 * Date: 24.10.17
 * Time: 22:39
 */

namespace oiyamaps;

/**
 * Getting template file path.
 *
 * @param string $slug
 * @param null   $name
 *
 * @return string
 */
function get_template_path( $slug, $name = null ) {

	$pathes = array();

	if ( ! empty( $name ) ) {
		$file = "{$slug}-{$name}.php";
	} else {
		$file = "{$slug}.php";
	}

	// template from theme root
	$pathes[] = trailingslashit( get_stylesheet_directory() ) . $file;

	// template from plugin directory in the theme
	$pathes[] = trailingslashit( get_stylesheet_directory() ) . trailingslashit( Plugin::$data['name'] ) . $file;

	// template from plugin directory
	$pathes[] = trailingslashit( Plugin::$data['path_dir'] ) . trailingslashit( 'templates' ) . $file;
	$pathes[] = trailingslashit( Plugin::$data['path_dir'] ) . $file;

	// filter $pathes
	$pathes = apply_filters( __NAMESPACE__.'_template_path', $pathes, $file );

	if ( ! empty( $pathes ) ) {
		foreach ( $pathes as $path ) {

			if ( file_exists( $path ) ) {

				return $path;
			}
		}
	}

	$path = '';

	return $path;
}

function the_template_part( $slug, $name = null, $atts = array(), $default = array(), $query_var = '' ) {
	$atts = wp_parse_args( $atts, $default );


	// set or extract variables
	if ( ! empty( $atts ) ) {
		if ( ! empty( $query_var ) ) {
			set_query_var( 'template_' . $query_var . '_vars', $atts );
		} else {
			extract( $atts, EXTR_SKIP );
		}
	}

	$action_name = __NAMESPACE__. '_get_template_part_' . $slug;
	if ( ! empty( $name ) ) {
		$action_name .= '-' . $name;
	}

	do_action( $action_name, $slug, $name, $atts );

	// filter $template
	$template = apply_filters( $action_name . '_filter', get_template_path( $slug, $name ), $slug, $name );

	if ( ! empty( $template ) && file_exists( $template ) ) {
		include $template;
	}
}

/**
 * Getting template as a string.
 *
 * @param string $slug
 * @param null   $name
 * @param array  $atts
 * @param array  $default
 * @param string $query_var
 *
 * @return string
 */
function get_template_part( $slug, $name = null, $atts = array(), $default = array(), $query_var = '' ) {

	ob_start();

	the_template_part( $slug, $name, $atts, $default, $query_var );

	return ob_get_clean();
}

/**
 * Функция возвращающая групповой чекбокс. Данные сохраняются в одно поле в виде строки с элементами через запятую
 *
 * @param $atts
 *
 * @return string
 */
function oiproaccount_multiselect( $atts ) {
	$atts = wp_parse_args( $atts, array(
		'name'        => 'multiselect',
		'class'       => '',
		'values'      => array(),
		// элементы списка: массив с элементами key => [label],
		'values_list' => array(),
		'checkbox'    => true,
	) );

	$atts['class'] .=' js-multiselect';

	/*if ( ! is_array( $atts['values'] ) ) {
		$atts['values'] = array_map( 'trim', explode( ',', $atts['values'] ) );
	}*/

	// если значение пусто и не нужно выводить галочки
	if ( empty( $values ) && $atts['checkbox'] == false ) {

		// возврат пустой строки
		return '';
	}
	$list = array();

	$checked_total = 0;
	$values        = array_map( 'trim', explode( ',', $atts['values'] ) );
	foreach ( $atts['values_list'] as $key => $value ) {
		if ( ! empty( $values ) && in_array( $key, $values ) ) {
			$checked = 'checked="checked"';
			$checked_total ++;
		} else {
			$checked = '';
		}

		// если нужно выводить чекбоксы
		if ( $atts['checkbox'] == true ) {
			$list[] = '<li class="' . $atts['class'] . '__item"><label class="' . $atts['class'] . '__label"><input class="' . $atts['class'] . '__input" type="checkbox" ' . $checked . ' name="' . $atts['name'] . '" value="' . $key . '">' . $value['label'] . '<span class="' . $atts['class'] . '__bullet-box"><i class="' . $atts['class'] . '__checkbox"></i></span></label></li>';
		} else {

			// выводить пункт только, если он был выбран ранее
			if ( ! empty( $checked ) ) {
				$list[] = '<li class="' . $atts['class'] . '__item">' . $value['label'] . '</li>';
			}
		}
	}

	// добавление пустого выбранного поля, чтобы пользователь мог сохранять список в котором не поставлена ни одна галочка
	$list[] = '<input type="hidden" checked="checked" name="' . $atts['name'] . '" value="empty_value">';

	$list = '<div class="' . $atts['class'] . '"><ul class="' . $atts['class'] . '__list">' . implode( "\n", $list ) . '</ul></div>';

	return $list;
}

// eof
