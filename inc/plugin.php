<?php
/**
 * SHIVS Base Class
 * This is the base class that both Admin class and Public class extends.
 * 
 * It also makes the Config class available to both sub classes and has
 * two methods for plugging into WordPress actions and filters.
 *
 * @abstract
 */
abstract class Shivs_Poll_Plugin {
	protected $_config;

	/**
	 * This initializes the configuration data and prepares the init function
	 * @param  $config Shivs_Poll_Config
	 * @return Shivs_Poll_Plugin
	 */
	public function __construct( Shivs_Poll_Config $config ) {
		$this->_config = $config;
		$this->init();
	}

	/**
	 * This is used by both Admin and Public classes to setup action and filter hooks
	 * @abstract
	 * @access protected
	 */
	abstract protected function init();

	/**
	 * This function will be used in Admin class for WordPress action hooks
	 * @param mixed $action
	 * @param mixed $function
	 * @param mixed $priority
	 * @param mixed $accepted_args
	 */
	protected function add_action( $action, $function = '', $priority = 10, $accepted_args = 1 ) {
		add_action( $action, array($this, $function == '' ? $action : $function ), $priority, $accepted_args );
	}

	/**
	 * This function will be used in Admin class for WordPress action hooks
	 * @param mixed $action
	 * @param mixed $function
	 * @param mixed $priority
	 * @param mixed $accepted_args
	 */
	protected function remove_action( $action, $function = '' ) {
		remove_action( $action, array($this, $function == '' ? $action : $function ) );
	}

	/**
	 * This function will be used in Public class for WordPress filter hooks
	 * @param [type]  $filter        [WordPress filter hook]
	 * @param [type]  $function      [your defined function to be executed with WordPress hook]
	 * @param integer $priority      [number between 1 to 10]
	 * @param integer $accepted_args
	 */
	protected function add_filter( $filter, $function, $priority = 10, $accepted_args = 1 ) {
		add_filter( $filter, array($this, $function == '' ? $filter : $function ), $priority, $accepted_args );
	}
}
?>