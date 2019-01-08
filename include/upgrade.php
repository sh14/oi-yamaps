<?php
/**
 * Date: 08/01/2019
 * @author Isaenko Alexey <info@oiplug.com>
 */

namespace oiyamaps;

function upgrade( $upgrader_object, $options ) {

	if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
		foreach ( $options['plugins'] as $each_plugin ) {
			if ( $each_plugin == Plugin::$data['slug'] ) {
				$options = \get_option( 'oiym_options' );
				if ( ! empty( $options ) ) {
					update_option( __NAMESPACE__.'_options', $options );
					delete_option( 'oiym_options' );
				}
			}
		}
	}
}

add_action( 'upgrader_process_complete', __NAMESPACE__ . '\upgrade', 10, 2 );

// eof
