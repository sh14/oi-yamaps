<?php
/**
 * Date: 08/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

function set_tables() {
	$data = array(
		'cache' => array(
			'cache_id'    => array(
				'type'    => 'int',
				'length'  => 22,
				'default' => '',
				'null'    => 0,
				'ai'      => 1, // 1 - set auto increment for that column
			),
			'user_key'    => array(
				'type' => 'text',
			),
			'yandex_data' => array(
				'type' => 'text',
			),
			'expire'      => array(
				'type'    => 'datetime',
				'default' => 'current_timestamp',
			),
		),
	);

	return $data;

}

add_filter( __NAMESPACE__ . '\create_tables', __NAMESPACE__ . '\set_tables', 10, 1 );

function get_address_cache( $user_key ) {
	global $wpdb;
	remove_address_cache();
	$table    = Plugin::$data['table_prefix'] . 'cache';
	$user_key = esc_sql( $user_key );
	$user_key = trim( $user_key );
	$query    = $wpdb->prepare( "SELECT `yandex_data` FROM {$table} WHERE `user_key` = %s", $user_key );
	$result   = $wpdb->get_var( $query );
	if ( is_json( $result ) ) {
		$result = json_decode( $result, true );
	}

	return $result;
}

function set_address_cache(
	$user_key, $yandex_data, $expire = /*WEEK_IN_SECONDS*/
30
) {
	global $wpdb;
	remove_address_cache( $user_key );
	$table    = Plugin::$data['table_prefix'] . 'cache';
	$user_key = esc_sql( $user_key );
	$user_key = trim( $user_key );
	if ( is_array( $yandex_data ) ) {
		$yandex_data = json_encode( $yandex_data );
	}
	$expire = intval( current_time( 'timestamp' ) ) + intval( $expire );
	$expire = date( 'Y-m-d H:i:s', $expire );
	$query  = $wpdb->prepare( "INSERT INTO {$table} SET `user_key` = %s, `yandex_data` = %s, `expire` = %s", $user_key, $yandex_data, $expire );
	$wpdb->query( $query );
	if ( ! empty( $wpdb->last_error ) ) {
		return false;
	}

	return true;
}

function remove_address_cache( $user_key = '' ) {
	global $wpdb;
	$table    = Plugin::$data['table_prefix'] . 'cache';
	$user_key = esc_sql( $user_key );
	$user_key = trim( $user_key );
	if ( ! empty( $user_key ) ) {
		$query = $wpdb->prepare( "DELETE FROM {$table} WHERE `user_key` = %s", $user_key );
	} else {
		$now   = time();
		$query = $wpdb->prepare( "DELETE FROM {$table} WHERE `expire` > %d", $now );
	}

	$wpdb->query( $query );
	if ( ! empty( $wpdb->last_error ) ) {
		return false;
	}

	return true;
}

// eof
