<?php


namespace Geo;

use oiyamaps\GeoCoder;

class Geo {
	static private $geocoder;

	static public function get( $place ) {
		self::$geocoder = new GeoCoder( $place );

		return self::$geocoder->get();
	}
}