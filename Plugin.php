<?php


namespace oiyamaps;


// init plugin data
Plugin::init();

/**
 * Class contains plugin information.
 *
 * Class Plugin
 * @package oiyamaps
 */
class Plugin {
	public static $data = array();

	private static function get_table_prefix() {
		global $wpdb;

		return $wpdb->prefix . __NAMESPACE__ . '_';
	}

	public static function init() {

		// current plugin directory
		self::$data['table_prefix'] = self::get_table_prefix();

		// current plugin directory
		self::$data['path_dir'] = plugin_dir_path( __FILE__ );

		// current plugin slug
		self::$data['slug'] = plugin_basename( self::$data['path_dir'] );

		// full path to current plugin
		self::$data['path'] = self::$data['path_dir'] . self::$data['slug'] . '.php';

		// current plugin url directory
		self::$data['url'] = plugin_dir_url( __FILE__ );

		// current plugin 8kiB data
		$file_data  = \get_file_data( self::$data['path'], array(
			'version'     => 'Version',
			'name'        => 'Plugin Name',
			'link'        => 'Plugin URI',
			'description' => 'Description',
			'author'      => 'Author',
			'author_uri'  => 'Author URI',
			'domain'      => 'Text Domain',
			'domain_path' => 'Domain Path',
			'github_uri'  => 'GitHub Plugin URI',
		) );
		self::$data = array_merge( self::$data, $file_data );
	}
}
