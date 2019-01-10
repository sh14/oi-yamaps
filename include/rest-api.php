<?php
/**
 * Date: 09/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

function rest_api_endpoints() {
	$endpoints = apply_filters( __NAMESPACE__ . '_endpoints', array() );

	if ( ! empty( $endpoints ) ) {
		foreach ( $endpoints as $namespace => $points ) {
			foreach ( $points as $endpoint => $data ) {
				register_rest_route( $namespace, $endpoint, $data );
			}
		}
	}
}

add_action( 'rest_api_init', __NAMESPACE__ . '\rest_api_endpoints' );


function add_endpoints() {
	return array(
		__NAMESPACE__ . '/v1' => array(
			'/getplace/(?P<place>.*?$)' => array(
				'methods'  => 'GET',
				'callback' => function ( $data ) {

					if ( ! empty( $data['place'] ) ) {
						$result = get_place( $data['place'] );
						if ( ! empty( $result ) ) {
							wp_send_json_success( $result );
						}
					}

					wp_send_json_error();
				},
			),
		),
	);
}

add_filter( __NAMESPACE__ . '_endpoints', __NAMESPACE__ . '\add_endpoints' );

// eof
