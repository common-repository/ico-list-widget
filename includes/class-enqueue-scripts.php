<?php
/**
 * Events Listing Widget Enqueue Scripts.
 *
 * @since 0.0.1
 */
class ICOLW_Enqueue_Scripts {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.0.1
	 *
	 * @var   Events_Listing_Widget
	 */
	protected $plugin = null;

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
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
	}

	/**
	 * Enqueue scripts for admin
	 *
	 * @param $hook string Page hook
	 */
	public function admin_scripts( $hook ) {
		// Enqueue this script only on widgets.php page
		if ('widgets.php' === $hook) {
			/** Widget requirements */
			$r_widget = array( 'wp-color-picker' );
			wp_enqueue_script(
				$this->plugin->name,
				$this->plugin->url . 'assets/js/elw.back-widgets-page.min.js',
				$r_widget,
				$this->plugin->version,
				true
			);

			wp_enqueue_style('wp-color-picker');

		}

		// Enqueue this script only on plugin settings page
		if ( 'settings_page_icolw_settings' === $hook ) {
			wp_enqueue_script(
				$this->plugin->name,
				$this->plugin->url . 'assets/js/elw.back-settings-page.min.js',
				array(),
				$this->plugin->version,
				true
			);
		}

		wp_enqueue_style(
			$this->plugin->name,
			$this->plugin->url . 'assets/css/elw.back.min.css',
			array(),
			$this->plugin->version,
			'all'
		);
	}

	/**
	 * Enqueue scripts for frontend
	 */
	public function front_scripts() {
		if ( WP_DEBUG ) {
			wp_enqueue_script(
				$this->plugin->name,
				$this->plugin->url . 'assets/js/elw.front.js',
				array( 'backbone', 'jquery-ui-tabs' ),
				$this->plugin->version,
				true
			);

			wp_enqueue_style(
				$this->plugin->name,
				$this->plugin->url . 'assets/css/elw.front.css',
				array( 'dashicons' ),
				$this->plugin->version,
				'all'
			);
		} else {
			wp_enqueue_script(
				$this->plugin->name,
				$this->plugin->url . 'assets/js/elw.front.min.js',
				array( 'backbone', 'jquery-ui-tabs' ),
				$this->plugin->version,
				true
			);

			wp_enqueue_style(
				$this->plugin->name,
				$this->plugin->url . 'assets/css/elw.front.min.css',
				array( 'dashicons' ),
				$this->plugin->version,
				'all'
			);
		}

		wp_localize_script(
			$this->plugin->name,
			'ICOLW',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'data'    => $this->plugin->get_events_data(),
				'labels'  => array(
					'live'     => __( 'LIVE ICOs', 'ico-lw' ),
					'upcoming' => __( 'UPCOMING ICOs', 'ico-lw' ),
				)
			)
		);
	}
}
