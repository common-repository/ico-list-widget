<?php
/**
 * Events Listing Widget Api.
 *
 * @since 0.0.1
 */
class ICOLW_Api {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.1
	 *
	 * @var   Events_Listing_Widget
	 */
	protected $plugin = null;

	/**
	 * Api Url
	 *
	 * @var string
	 */
	protected $api_url = 'https://api.icowatchlist.com/wp/v1/';
	//protected $api_url = 'http://10.0.2.2:3000/db';

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
	 * Get data from api server
	 *
	 * @return null|stdClass Object with data or null on failure
	 */
	public function get_data() {
		/* Don't know the reason, but headers not send to the api
		 *
		$headers = array(
			'wpv' => get_bloginfo('version'),
			'plv' => $this->plugin->version,
			'site' => get_site_url()
		);
		$response = wp_remote_get($this->api_url, array('headers' => $headers));
		$http_code = wp_remote_retrieve_response_code($response);
		if (200 !== $http_code) {
			return null;
		}
		$result = json_decode(wp_remote_retrieve_body($response));
		return $result;
		*/
		$headers = array(
			'wpv' => get_bloginfo('version'),
			'plv' => $this->plugin->version,
			'site' => parse_url(get_site_url())['host']
		);
		$url = $this->api_url . '?' . http_build_query($headers);
		$response = file_get_contents($url);
		$result = json_decode($response);
		return $result;
	}
}
