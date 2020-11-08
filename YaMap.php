<?php


namespace oiyamaps;

class YaMap {
	// default value - packege not loaded yet
	public static $id = 0;
	// default value - packege not loaded yet
	public static $pid = 0;

	public function staticValue() {
		// return actual value
		return self::$id;
	}

	public function staticValue1() {
		// return actual value
		return self::$pid;
	}
}
