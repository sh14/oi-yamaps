<?php
/**
 * Date: 09/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

function rest_api_endpoints() {
	$endpoints = array(
		__NAMESPACE__ . '/v1' => array(
			'/getplace/(?P<place>.*?$)' => array(
				'methods'  => 'GET',
				'callback' => __NAMESPACE__ . '\api_get_place',
			),
		),
	);

	foreach ( $endpoints as $namespace => $points ) {
		foreach ( $points as $endpoint => $data ) {
			register_rest_route( $namespace, $endpoint, $data );
		}
	}
}

add_action( 'rest_api_init', __NAMESPACE__ . '\rest_api_endpoints' );

function api_get_place( $data ) {
	//$data = (array) $data;
	//wp_send_json_success( $data );
	//wp_send_json_success( $data['place'] );
	$result = array();
	if ( ! empty( $data['place'] ) ) {
		$result = get_place( $data['place'] );
	}

	wp_send_json_success( $result );
}

// eof