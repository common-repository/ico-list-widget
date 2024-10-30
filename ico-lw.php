<?php
/**
 * Plugin Name: ICO List Widget
 * Plugin URI:  https://icowatchlist.com/
 * Description: This plugin adds a widget to your wordpress site with a list of all the live and upcoming ICO (initial coin offering) projects in the cryptoeconomy space.
 * Version:     1.0.0
 * Author:      ICOWatchlist.com
 * Author URI:  https://icowatchlist.com/
 * License:     GPLv2 or later
 * Text Domain: ico-lw
 * Domain Path: /languages
 */

// Use composer autoload.
require 'vendor/autoload.php';

/**
 * Main initiation class.
 *
 * @since  0.0.1
 */
final class ICOLW_Events_Listing_Widget {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	const VERSION = '1.0.0';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $basename = '';

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $name = 'ico-lw';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.1
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    ICOLW_Events_Listing_Widget
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	/**
	 * Instance of ICOLW_Api
	 *
	 * @since0.0.1
	 * @var ICOLW_Api
	 */
	protected $api;

	/**
	 * Instance of ICOLW_Enqueue_Scripts
	 *
	 * @since0.0.1
	 * @var ICOLW_Enqueue_Scripts
	 */
	protected $enqueue_scripts;

	/**
	 * Instance of ICOLW_Cache
	 *
	 * @since0.0.1
	 * @var ICOLW_Cache
	 */
	protected $cache;

	/**
	 * Instance of ICOLW_Settings
	 *
	 * @since1.0.0
	 * @var ICOLW_Settings
	 */
	protected $settings;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  ICOLW_Events_Listing_Widget A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.1
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.1
	 */
	public function plugin_classes() {

		require( $this->path . '/includes/class-events-widget.php' );
		$this->api             = new ICOLW_Api( $this );
		$this->enqueue_scripts = new ICOLW_Enqueue_Scripts( $this );
		$this->cache           = new ICOLW_Cache( $this );
		$this->settings        = new ICOLW_Settings( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'icolw_cron_hook', array( $this, 'do_cron_jobs' ) );
	}

	public function do_cron_jobs() {
		$this->update_events_data();
	}

	/**
	 * Update events data from api
	 *
	 * @return null|stdClass Null - if api call didn't return data or Object with data
	 */
	public function update_events_data() {
		$data = $this->api->get_data();
		if ( is_null( $data ) ) {
			error_log( '[ICOLW] Can not get data from api' );

			return null;
		} else {
			$this->cache->save_events_data( $data, 3600 );
		}

		return $data;
	}

	/**
	 * Get events data from cache or api
	 *
	 * @return null|stdClass Null - if api call didn't return data or Object with data
	 */
	public function get_events_data() {
		$data = $this->cache->get_events_data();

		if ( false === $data ) {
			$data = $this->update_events_data();
		}

		return $data;
	}

	/**
	 * Create json-ld data from events data
	 * TODO: Add json-ld data to the frontend
	 */
	public function get_events_microdata() {
	    $result = [];
	    $events_data = $this->get_events_data();

	    return null;
    }

	/**
	 * Activate the plugin.
	 *
	 * @since  0.0.1
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();

		// Setup cron jobs
		if ( ! wp_next_scheduled( 'icolw_cron_hook' ) ) {
			wp_schedule_event( time(), 'hourly', 'icolw_cron_hook' );
		}
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.1
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
		// Remove cron jobs
		$timestamp = wp_next_scheduled( 'icolw_cron_hook' );
		wp_unschedule_event( $timestamp, 'icolw_cron_hook' );
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.1
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Initialize CMB2
		$cmb2_bootstrap = $this->path . 'vendor/webdevstudios/cmb2/init.php';
		if ( file_exists( $cmb2_bootstrap ) ) {
			require_once $cmb2_bootstrap;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'ico-lw', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.1
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {
			$this->activation_errors[] = 'Minimum php version should be at least 5.6. Please upgrade.';

			return false;
		}

		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.0.1
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'Events Listing Widget is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'ico-lw' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
        <div id="message" class="error">
            <p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
        </div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'name':
			case 'url':
			case 'path':
			case 'api':
			case 'cron':
			case 'enqueue_scripts':
			case 'cache':
			case 'settings':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}
}


function icolw_elw() {
	return ICOLW_Events_Listing_Widget::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( icolw_elw(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( icolw_elw(), '_activate' ) );
register_deactivation_hook( __FILE__, array( icolw_elw(), '_deactivate' ) );
