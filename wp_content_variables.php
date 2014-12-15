<?php

/*
Plugin Name: WP Content Variables
Plugin URI: http://aramzs.me/
Description: Variables for your content
Version: 0.0.1
Author: Aram Zucker-Scharff
License: GPL2
*/

class WP_Content_Variables {

  protected $data;

  protected static $instance;

  public static function get_instance() {
  		$class = get_called_class();
  		if ( ! isset( static::$instance ) ) {
  			$instance = new $class;
  			// Standard setup methods
  			foreach( array( 'setup_globals', 'includes', 'setup_actions' ) as $method ) {
  				if ( method_exists( $instance, $method ) )
  					$instance->$method();
  			}

  			self::$instance = $instance;
  		}
  		return self::$instance;
  }



	private function __construct() {
  		/** Prevent the class from being loaded more than once **/

  }



  	public function __isset( $key ) {
  		return isset( $this->data[$key] );
  	}

  	public function __get( $key ) {
  		return isset( $this->data[$key] ) ? $this->data[$key] : null;
  	}

  	public function __set( $key, $value ) {
  		$this->data[$key] = $value;
  	}

	/**
	 * Set up variables for the class.
	 */
	protected function setup_globals() {

		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'WP_Content_Variables_plugin_basename', plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'WP_Content_Variables_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'WP_Content_Variables_plugin_dir_url',  plugin_dir_url( $this->file ) );
		$this->ver = apply_filters( 'WP_Content_Variables_version',  '0.0.1' );
		$this->slug = apply_filters( 'WP_Content_Variables_slug',  'wpcv' );

    if (!isset($res)){
      $extend = new stdClass();
    }
    $this->extend = $extend;

	}

	/**
	 * Load required or conditional includes
	 */
	protected function includes() {
		require_once('include/admin.php');

	}

  public function setup_actions(){
    add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

  }

	/**
	 * Enqueue Scripts
	 *
	 * @return null
	 */
	public function scripts() {


	}


}

function WP_Content_Variables() {
   return WP_Content_Variables::get_instance();
}
add_action( 'plugins_loaded', 'WP_Content_Variables' );
