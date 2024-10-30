<?php
/**
 * Events Listing Widget Settings class.
 *
 * @since 1.0.0
 */
class ICOLW_Settings {
	/**
	 * Parent plugin class.
	 *
	 * @var    Events_Listing_Widget
	 * @since  1.0.0
	 */
	protected $plugin = null;

	/**
	 * Option key, and option page slug.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected static $key = 'icolw_settings';

	/**
	 * Options page metabox ID.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected static $metabox_id = 'icolw_settings_metabox';

	/**
	 * Options Page title.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $title = '';

	/**
	 * Options Page hook.
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 *
	 * @param  Events_Listing_Widget $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Set our title.
		$this->title = esc_attr__( 'ICO List Shortcode Settings', 'ico-lw' );
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  1.0.0
	 */
	public function hooks() {

		// Hook in our actions to the admin.

		add_action( 'cmb2_admin_init', array( $this, 'add_options_page_metabox' ) );

	}

	/**
	 * Add custom fields to the options page.
	 *
	 * @since  1.0.0
	 */
	public function add_options_page_metabox() {

		// Add our CMB2 metabox.
		$cmb = new_cmb2_box( array(
			'id'           => self::$metabox_id,
			'title'        => $this->title,
			'object_types' => array( 'options-page' ),

			/*
			 * The following parameters are specific to the options-page box
			 * Several of these parameters are passed along to add_menu_page()/add_submenu_page().
			 */

			'option_key'  => self::$key,
			// The option key and admin menu page slug.
			// 'icon_url'        => 'dashicons-palmtree', // Menu icon. Only applicable if 'parent_slug' is left empty.
			// 'menu_title'      => esc_html__( 'Options', 'cmb2' ), // Falls back to 'title' (above).
			'parent_slug' => 'options-general.php',
			// Make options page a submenu item of the themes menu.
			// 'capability'      => 'manage_options', // Cap required to view options-page.
			// 'position'        => 1, // Menu position. Only applicable if 'parent_slug' is left empty.
			// 'admin_menu_hook' => 'network_admin_menu', // 'network_admin_menu' to add network-level options page.
			// 'display_cb'      => false, // Override the options-page form output (CMB2_Hookup::options_page_output()).
			// 'save_button'     => esc_html__( 'Save Theme Options', 'cmb2' ), // The text for the options-page save button. Defaults to 'Save'.
		) );

		$cmb->add_field( array(
			'name'    => __( 'Primary Color', 'ico-lw' ),
			'desc'    => __( 'Select main color for widget', 'ico-lw' ),
			'id'      => 'primary_color', // No prefix needed.
			'type'    => 'colorpicker',
			'default' => '#EBA21E',
		) );

		$cmb->add_field( array(
			'name'             => __( 'Number of events', 'ico-lw' ),
			'desc'             => __( 'Choose number of events to show on each page', 'ico-lw' ),
			'id'               => 'num_of_events',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => 4,
			'options'          => array(
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
				6 => 6,
				8 => 8
			),
		) );

		$cmb->add_field( array(
			'name'             => __( 'Type of timer', 'ico-lw' ),
			'desc'             => __( 'Choose timer type', 'ico-lw' ),
			'id'               => 'timer_type',
			'type'             => 'select',
			'show_option_none' => false,
			'default'          => 'progress',
			'options'          => array(
				'date'      => __( 'Date', 'ico-lw' ),
				'countdown' => __( 'Countdown', 'ico-lw' ),
				'progress'  => __( 'Progress', 'ico-lw' )
			),
		) );

		$cmb->add_field( array(
			'name'    => __( 'Shortcode', 'ico-lw' ),
			'desc'    => __( 'Paste this shortcode everywhere you want ICO Widget to appear', 'ico-lw' ),
			'id'      => 'shortcode',
			'type'    => 'text',
			'classes' => 'shortcode',
			'attributes' => array(
				'readonly' => 'readonly',
			)
		) );

	}

	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $key Options array key
	 * @param  mixed $default Optional default value
	 *
	 * @return mixed           Option value
	 */
	public static function get_value( $key = '', $default = false ) {
		if ( function_exists( 'cmb2_get_option' ) ) {

			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( self::$key, $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( self::$key, $default );

		$val = $default;

		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}
}
