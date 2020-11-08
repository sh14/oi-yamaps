<?php


namespace oiyamaps;


class GeoCoder {
	public $place = '';
	public $error_messages = [];

	public function __construct( $place ) {
		$this->place = $place;
	}

	/**
	 * @param string $src
	 * @param array $params
	 */
	private function url( $src, array $params ) {
		if ( ! empty( $params ) && ! empty( $params['geocode'] ) ) {
			$params['geocode'] = urlencode( $params['geocode'] );
		}

		return $src . ! empty( $params ) ? '?' . join( '&', $params ) : '';
	}

	/**
	 * Create a cache key for given place in given local.
	 *
	 * @return string
	 */
	private function key() {
		return md5( mb_strtolower( $this->place ) . get_locale() );
	}

	/**
	 * Getting data from Yandex
	 *
	 * @param $place
	 *
	 * @return string
	 */
	public function get() {

		// if there is no cached data
		if ( /*WP_DEBUG == true ||*/ ! ( $content = $this->cacheGet() ) ) {


			$options = get_option( __NAMESPACE__ . '_options' );

			$url = $this->url( "https://geocode-maps.yandex.ru/1.x/", [
				'geocode' => $this->place,
				'apikey'  => $options['apikey'],
				'format'  => 'json',
				'lang'    => get_locale(),
			] );
echo $this->place       ;
echo $url;

			// get data by GET method
			$content = wp_remote_get( $url, apply_filters( 'oiyamaps_wp_remote_get_args', [] ) );

			if ( ! is_wp_error( $content ) ) {

				// get the content body
				$content = wp_remote_retrieve_body( $content );

			} else {

				// something goes wrong
				if ( ! ( $content = @file_get_contents( $url ) ) ) {

					// if cURL is on
					if ( $this->isCurl() ) {

						$content = $this->curl_get_contents( $url );
					} else {
						$this->errorsAdd( __( 'To show the map cURL must be enabled.', __NAMESPACE__ ) );

						return '';
					}
				}
			}

			if ( is_json( $content ) ) {
				$content = json_decode( $content, true );
				$this->cacheSet( $content, apply_filters( 'oiyamaps_cache_time', WEEK_IN_SECONDS ) );
			}
		}

		return $content;
	}

	/**
	 * Check if an error list is empty.
	 *
	 * @return bool
	 */
	public function isErrors() {
		return ! ! ! empty( $this->error_messages );
	}

	/**
	 * Gett errors list.
	 *
	 * @return array
	 */
	public function errors() {
		return $this->error_messages;
	}

	/**
	 * Add an error message to the list.
	 *
	 * @param string $error
	 */
	private function errorsAdd( $error ) {
		$this->error_messages[] = $error;
	}


	/**
	 * Get cached data by given place key.
	 *
	 * @return array
	 */
	private function cacheGet() {
		global $wpdb;
		$this->cacheRemove();
		$table  = Plugin::$data['table_prefix'] . 'cache';
		$query  = $wpdb->prepare( "SELECT `yandex_data` FROM {$table} WHERE `user_key` = %s", $this->key() );
		$result = $wpdb->get_var( $query );
		if ( ! empty( $result ) && is_json( $result ) ) {
			return json_decode( $result, true );
		}

		return [];
	}

	/**
	 *
	 * @param array $yandex_data
	 * @param float|int $expire
	 *
	 * @return bool
	 */
	private function cacheSet( $yandex_data, $expire ) {
		global $wpdb;
		$this->cacheRemove( $this->key() );
		$table = Plugin::$data['table_prefix'] . 'cache';
		if ( is_array( $yandex_data ) ) {
			$yandex_data = json_encode( $yandex_data );
		}

		$query = $wpdb->prepare( "INSERT INTO {$table} SET `user_key` = %s, `yandex_data` = %s, `expire` = %s", $this->key(), $yandex_data, $this->expirationDate( $expire ) );
		$wpdb->query( $query );
		if ( ! empty( $wpdb->last_error ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Remove cached data.
	 *
	 * @param string $user_key
	 *
	 * @return bool
	 */
	private function cacheRemove( $user_key = '' ) {
		global $wpdb;
		$table = Plugin::$data['table_prefix'] . 'cache';
		if ( ! empty( $user_key ) ) {
			$query = $wpdb->prepare( "DELETE FROM {$table} WHERE `user_key` = %s", $this->key() );
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


	/**
	 * Calculate an exiration date-time
	 *
	 * @param float|int $delta
	 *
	 * @return false|string
	 */
	private function expirationDate( $delta ) {
		return date( 'Y-m-d H:i:s', absint( current_time( 'timestamp' ) ) + absint( $delta ) );
	}


	/**
	 * Check if cURL module is on.
	 *
	 * @return bool
	 */
	private function isCurl() {
		return function_exists( 'curl_version' );
	}


	/**
	 * Get content via cURL.
	 *
	 * @param $url
	 *
	 * @return mixed
	 */
	private function curl_get_contents( $url ) {
		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
		$data = curl_exec( $curl );
		curl_close( $curl );

		return $data;
	}

}
