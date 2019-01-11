<?php
/**
 * Date: 09/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

use WP_REST_Server;

function rest_api_endpoints() {
	$endpoints = apply_filters( __NAMESPACE__ . '_endpoints', array() );

	if ( ! empty( $endpoints ) ) {
		foreach ( $endpoints as $namespace => $points ) {
			foreach ( $points as $endpoint => $data ) {
				if ( ! empty( $data['method'] ) ) {
					$data['method'] = strtoupper( $data['method'] );
				}
				register_rest_route( $namespace, $endpoint, $data );
			}
		}
	}
}

add_action( 'rest_api_init', __NAMESPACE__ . '\rest_api_endpoints' );


function add_endpoints() {
	$namespace = __NAMESPACE__ . '/v1';

	return array(
		$namespace => array(
			'/getplace/(?P<place>.*?$)' => array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => __NAMESPACE__ . '\get_place',
			),
		),
	);
}

add_filter( __NAMESPACE__ . '_endpoints', __NAMESPACE__ . '\add_endpoints' );

// eof
