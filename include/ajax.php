<?php
/**
 * Date: 09/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

function ajax_get_place() {
	$result = array();
	if ( ! empty( $_POST['place'] ) ) {
		$result = get_place( $_POST['place'] );
	}

	wp_send_json( $result );
}

add_action( 'wp_ajax_' . __NAMESPACE__ . '_' . 'ajax_get_place', __NAMESPACE__ . '\ajax_get_place' );


// eof
