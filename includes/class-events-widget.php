<?php
/**
 * Events Listing Widget class.
 *
 * @since 0.0.1
 */
class ICOLW_Events_Widget extends WP_Widget {

	/**
	 * Unique identifier for this widget.
	 *
	 * Will also serve as the widget class.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $widget_slug = 'icolw-events-widget';


	/**
	 * Widget name displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $widget_name = '';


	/**
	 * Default widget title displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 * @since  0.0.1
	 */
	protected $default_widget_title = '';

	/**
	 * Primary color
	 *
	 * @var string rgba color
	 */
	protected $default_primary_color = 'rgba(235,162,30, 1)';

	/**
	 * Timer type. One of: countdown, date
	 *
	 * @var string Timer type
	 */
	protected $default_timer_type = 'progress';

	/**
	 * Options for timer select box
	 *
	 * @var array
	 */
	protected $timer_options = null;

	/**
	 * Default number of events to show
	 *
	 * @var int Number of events
	 */
	protected $default_num_events = 4;

	/**
	 * Shortcode name for this widget
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected static $shortcode = 'icowidget';

	/**
	 * Construct widget class.
	 *
	 * @since  0.0.1
	 */
	public function __construct() {

		$this->widget_name   = esc_html__( 'ICO List Widget', 'ico-lw' );
		$this->timer_options = array(
			'countdown' => __( 'Countdown', 'ico-lw' ),
			'date'      => __( 'Date', 'ico-lw' ),
			'progress'  => __( 'Progress', 'ico-lw' )
		);

		parent::__construct(
			$this->widget_slug,
			$this->widget_name,
			array(
				'classname'   => $this->widget_slug,
				'description' => esc_html__( 'Adds list of all the live and upcoming ICO (initial coin offering) projects in the cryptoeconomy space.', 'ico-lw' ),
			)
		);

		// Clear cache on save.
		add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		// Add a shortcode for our widget.
		add_shortcode( self::$shortcode, array( __CLASS__, 'get_widget' ) );
	}

	/**
	 * Delete this widget's cache.
	 *
	 * Note: Could also delete any transients
	 * delete_transient( 'some-transient-generated-by-this-widget' );
	 *
	 * @since  0.0.1
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->widget_slug, 'widget' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $args The widget arguments set up when a sidebar is registered.
	 * @param  array $instance The widget settings as set by user.
	 */
	public function widget( $args, $instance ) {

		// Set widget attributes.
		$atts = array(
			'title' => $instance['title'],
			'color' => $instance['color'],
			'type'  => $instance['type'],
			'items' => $instance['items'],
		);

		// Display the widget.
		echo self::get_widget( $atts ); // WPCS XSS OK.
	}

	/**
	 * Return the widget/shortcode output
	 *
	 * @since  0.0.1
	 *
	 * @param  array $atts Array of widget/shortcode attributes/args.
	 *
	 * @return string      Widget output
	 */
	public static function get_widget( $atts ) {

		$defaults = array(
			'title' => '',
			'color' => '',
			'type'  => '',
			'items' => 4,
		);

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, self::$shortcode );

		$data = array(
			'title' => esc_html( $atts['title'] ),
			'color' => esc_attr( $atts['color'] ),
			'type'  => esc_attr( $atts['type'] ),
			'items' => esc_attr( $atts['items'] ),
            'widget-id' => uniqid('elw_'),
		);

		$data_atts = join( ' ', array_reduce( array_keys( $data ), function ( $carry, $key ) use ( $data ) {
			$carry[] = sprintf( "data-%s='%s'", $key, $data[ $key ] );

			return $carry;
		}, array() ) );

		// Start an output buffer.
		ob_start();

		echo "<div class='rer-elw-widget' $data_atts>";

		echo "</div>";

		// Return the output buffer.
		return ob_get_clean();
	}

	/**
	 * Update form values as they are saved.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 *
	 * @return array               Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		// Previously saved values.
		$instance = $old_instance;

		// Sanity check new data existing.
		$title = isset( $new_instance['title'] ) ? $new_instance['title'] : '';

		// Sanitize title before saving to database.
		$instance['title'] = sanitize_text_field( $title );

		// Primary color
		if ( isset( $new_instance['color'] ) ) {
			$instance['color'] = $new_instance['color'];
		} else {
			$instance['color'] = $this->default_primary_color;
		}

		// Timer type
		if ( isset( $new_instance['type'] ) ) {
			$instance['type'] = $new_instance['type'];
		} else {
			$instance['type'] = $this->default_timer_type;
		}

		// Number of Events
		if ( isset( $new_instance['items'] ) ) {
			$instance['items'] = intval( $new_instance['items'] );
		} else {
			$instance['items'] = $this->default_num_events;
		}

		// Flush cache.
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Back-end widget form with defaults.
	 *
	 * @since  0.0.1
	 *
	 * @param  array $instance Current settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		// Set defaults.
		$defaults = array(
			'title' => $this->default_widget_title,
			'text'  => '',
			'color' => $this->default_primary_color,
			'type'  => $this->default_timer_type,
			'items' => $this->default_num_events
		);

		// Parse args.
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
        <p class="elw-widget-field">
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'ico-lw' ); ?>
            </label>
            <input class="widefat"
                   id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                   type="text"
                   value="<?php echo esc_html( $instance['title'] ); ?>"
                   placeholder="optional"/>
        </p>
        <p class="elw-widget-field">
            <label for="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>">
				<?php esc_html_e( 'Primary Color:', 'ico-lw' ); ?>
            </label>
        <div class="widefat">
            <input class="elw-color-picker"
                   id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>"
                   data-alpha="true"
                   data-default-color="<?php echo $this->default_primary_color ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>"
                   type="text"
                   value="<?php echo sanitize_text_field( $instance['color'] ) ?>"/>
        </div>
        </p>
        <p class="elw-widget-field">
            <label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>">
				<?php esc_html_e( 'Timer Type:', 'ico-lw' ); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>">
                <option value="progress" <?php echo ( 'progress' === $instance['type'] ) ? 'selected' : '' ?>>
		            <?php echo $this->timer_options['progress'] ?>
                </option>
                <option value="countdown" <?php echo ( 'countdown' === $instance['type'] ) ? 'selected' : '' ?>>
					<?php echo $this->timer_options['countdown'] ?>
                </option>
                <option value="date" <?php echo ( 'date' === $instance['type'] ) ? 'selected' : '' ?>>
					<?php echo $this->timer_options['date'] ?>
                </option>
            </select>
        </p>
        <p class="elw-widget-field">
            <label for="<?php echo esc_attr( $this->get_field_id( 'items' ) ); ?>">
				<?php esc_html_e( 'Number of Events to Show', 'ico-lw' ); ?>
            </label>
            <select class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'items' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'items' ) ); ?>">
                <option value="2" <?php echo ( 2 === $instance['items'] ) ? 'selected' : '' ?>>
                    2
                </option>
                <option value="3" <?php echo ( 3 === $instance['items'] ) ? 'selected' : '' ?>>
                    3
                </option>
                <option value="4" <?php echo ( 4 === $instance['items'] ) ? 'selected' : '' ?>>
                    4
                </option>
                <option value="5" <?php echo ( 5 === $instance['items'] ) ? 'selected' : '' ?>>
                    5
                </option>
                <option value="6" <?php echo ( 6 === $instance['items'] ) ? 'selected' : '' ?>>
                    6
                </option>
                <option value="8" <?php echo ( 8 === $instance['items'] ) ? 'selected' : '' ?>>
                    8
                </option>
            </select>
        </p>
		<?php
	}
}

/**
 * Register widget with WordPress.
 */
function icolw_register_events_widget() {
	register_widget( 'icolw_Events_Widget' );
}

add_action( 'widgets_init', 'icolw_register_events_widget' );
