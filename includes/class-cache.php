<?php
/**
 * Events Listing Widget Cache.
 *
 * @since 0.0.1
 */
class ICOLW_Cache {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.1
	 *
	 * @var   Events_Listing_Widget
	 */
	protected $plugin = null;

	/**
	 * Cache key for events data
	 *
	 * @var string
	 */
	protected $events_key = 'icolw_events';

	/**
	 * Default time for cache to be stored
	 *
	 * @var int Number of seconds
	 */
	protected $default_time = 3600;

	/**
	 * Constructor.
	 *
	 * @since  0.0.1
	 *
	 * @param  Events_Listing_Widget $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {

	}

	/**
	 * Save data in cache
	 *
	 * @param $key string Transient key
	 * @param $data mixed Data to save
	 * @param $time int Number of seconds data should be saved
	 *
	 * @return bool True if data was saved, False otherwise
	 */
	protected function _set_cache($key, $data, $time) {
		return set_transient($key, $data, $time);
	}

	/**
	 * Get data from cache
	 *
	 * @param $key string Transient key
	 *
	 * @return mixed Value from cache
	 */
	protected function _get_cache($key) {
		return get_transient($key);
	}

	/**
	 * Remove cache by key
	 * @param $key
	 *
	 * @return bool True if delete was ok. False otherwise
	 */
	protected function _clear_cache($key) {
		return delete_transient($key);
	}

	/**
	 * Save events data
	 *
	 * @param $data stdClass Events data as Object
	 * @param $time int Number of seconds to save data
	 *
	 * @return bool True if data was saved. False otherwise
	 */
	public function save_events_data($data, $time = null) {
		$t = $time ? $time : $this->default_time;
		return $this->_set_cache($this->events_key, $data, $t);
	}

	/**
	 * Get events data from cache
	 *
	 * @return mixed Value from cache
	 */
	public function get_events_data() {
		return $this->_get_cache($this->events_key);
	}

	/**
	 * Clear events cache
	 *
	 * return bool True if data was removed. False otherwise
	 */
	public function clear_events_data() {
		return $this->_clear_cache($this->events_key);
	}
}
