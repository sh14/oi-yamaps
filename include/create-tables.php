<?php
/**
 * Date: 04.11.18
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

/**
 * Функция создания таблиц из массива при активации плагина
 * формат столбца для формирования таблиц:
 * 'ig_id'     => array(
 *      'type'    => 'int', // тип в формате mysql
 *      'length'  => 22,
 *      'default' => '',
 *      'null'    => 0, // 0 - ноль запрещен, 1 - разрешен
 *      'ai'      => 1, // флаг, указывающий на автоинкремент
 * ),
 */
function create_tables() {
	global $wpdb;
	$data = apply_filters( __NAMESPACE__ . '\create_tables', array() );

	// если массив с таблицами определен
	if ( ! empty( $data ) ) {
		$ver         = Plugin::$data['version'];
		$current_ver = \get_option( __NAMESPACE__ . '_version' );

		// если текущая версия отличается от актуальной
		if ( $current_ver != $ver ) {
			$primary         = '';
			$charset_collate = $wpdb->get_charset_collate();
			$queries         = array();

			$prefix = Plugin::$data['table_prefix'];

			// составление запросов создания таблиц
			foreach ( $data as $table_name => $columns ) {
				if ( empty( $queries[ $table_name ] ) ) {
					$queries[ $table_name ] = array();
				}
				$queries[ $table_name ][] = "CREATE TABLE `{$prefix}{$table_name}`";
				$column_lines             = array();
				foreach ( $columns as $key => $value ) {
					if ( ! empty( $value['ai'] ) ) {
						$primary = $key;
					}

					$column_lines[ $key ]   = array();
					$column_lines[ $key ][] = '`' . $key . '`';
					$column_lines[ $key ][] = ( ! empty( $value['type'] ) ? strtoupper( $value['type'] ) : '' ) . ( ! empty( $value['length'] ) ? '(' . intval( $value['length'] ) . ')' : '' );
					$column_lines[ $key ][] = empty( $value['null'] ) ? 'NOT NULL' : 'NULL';
					$column_lines[ $key ][] = ! empty( $value['default'] ) ? 'DEFAULT ' . $value['default'] : '';
					$column_lines[ $key ][] = ! empty( $value['ai'] ) ? 'AUTO_INCREMENT' : '';
					$column_lines[ $key ]   = implode( ' ', array_filter( $column_lines[ $key ] ) );
				}
				if ( ! empty( $primary ) ) {
					$column_lines[] = 'PRIMARY KEY (`' . $primary . '`)';
				}
				$column_lines = implode( ', ' . "\n", array_filter( $column_lines ) );
				if ( ! empty( $column_lines ) ) {
					$queries[ $table_name ][] = '(' . $column_lines . ')';
				}

				if ( ! empty( $queries[ $table_name ] ) ) {
					$queries[ $table_name ][] = $charset_collate;
					$queries[ $table_name ][] = 'ENGINE = InnoDB';
					$queries[ $table_name ]   = implode( ' ', $queries[ $table_name ] );
					$queries[ $table_name ]   .= ';';
				}
			}

			// подключение специального файла с определением dbDelta()
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// если запросы существуют
			if ( ! empty( $queries ) ) {

				// перебор запросов
				foreach ( $queries as $query ) {

					// если запрос существует
					if ( ! empty( $query ) ) {

						// переопределение таблицы
						dbDelta( $query );
					}
				}
			}

			// обновление версии плагина в настройках
			update_option( __NAMESPACE__ . '_version', $ver );
		}
	}
}

// должен вызываться из основного файла плагина
// register_activation_hook( __FILE__, __NAMESPACE__ . '\create_tables' );

// eof
