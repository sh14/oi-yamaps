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

// eof
