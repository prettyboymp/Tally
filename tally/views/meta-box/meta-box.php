<?php

class TALLY_Meta_Box {

	private static $id = 'tally';
	private static $title = 'Tally Registration';
	private static $context = 'normal';	
	private static $priority = 'low';
	private static $nonce = 'tally-nonce';

	private static $post_types = array(
		'page',
		'post',
		'tz-event'
	);

	private static function post_types() {
		return apply_filters('tally_post_types', self::$post_types);
	}

	public static function initialize() {
		$cls = get_called_class();
		add_action('add_meta_boxes', array($cls, 'add'));
		add_action('save_post', array($cls, 'save'), 10, 2);
		add_action('admin_enqueue_scripts', array($cls, 'enqueue'));
	}

	public static function add() {
		foreach(static::post_types() as $type) {
			add_meta_box(
				static::$id,
				static::$title,
				array(get_called_class(), 'display'),
				$type,
				static::$context,
				static::$priority
			);			
		}
	}

	public static function enqueue() {
		global $wp_scripts;
		$ui = $wp_scripts->query('jquery-ui-core');

		wp_enqueue_media();

		wp_enqueue_style(
			'tally_jquery-ui-smoothness',
			"//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.css",
			false,
			null
		);
		wp_enqueue_style('tally_admin_styles', TALLY_URL.'resources/css/admin.css');

		wp_enqueue_script('tally_admin_scripts', TALLY_URL.'resources/js/admin.js', array(
			'jquery',
			'jquery-ui-datepicker'
		));
	}

	private static $fields = array(
		array(
			'type'  => 'checkbox',
			'id'    => 'enabled',
			'label' => 'Enabled',
			'desc'  => 'Registration is enabled'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'open',
			'label' => 'Open Registration',
			'desc'  => 'Registrations are currently being accepted.'
		),
		array(
			'type'  => 'date',
			'id'    => 'start_date',
			'label' => 'Start Date',
			'desc'  => 'Leave blank to start immediately.'
		),
		array(
			'type'  => 'date',
			'id'    => 'end_date',
			'label' => 'End Date',
			'desc'  => 'Leave blank for continuous registration. This value overrides "Open Registration" if specified.'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'capture_salutation',
			'label' => 'Capture Salutation',
			'desc'  => 'Include Mr./Ms./Mrs. with names'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'capture_shipping',
			'label' => 'Capture Shipping',
			'desc'  => 'Include Address 1/2, City, State & Zip fields'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'capture_organization',
			'label' => 'Capture Organization',
			'desc'  => 'Include optional Organization/Company field'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'capture_registrants',
			'label' => 'Capture Registrants',
			'desc'  => 'Includes names and specified custom fields for each registrant.'
		),
		array(
			'type'  => 'number',
			'id'    => 'max_registrants',
			'label' => 'Max Registrants',
			'desc'  => 'Set to 0 for unlimited registrants.',
			'min'   => 0,
			'step'  => 1
		),
		array(
			'type'  => 'repeater',
			'id'    => 'custom_fields',
			'label' => 'Additional Fields',
			'desc'  => 'Additional fields collected for a registration. All fields are standard text inputs.',
		),
		array(
			'type'  => 'repeater',
			'id'    => 'registrant_custom_fields',
			'label' => 'Registrant Additional Fields',
			'desc'  => 'Additional fields collected for each registrant. Only applicable if "Capture Registrants" is specified'
		),
		array(
			'type'    => 'select',
			'id'      => 'payment_method',
			'label'   => 'Payment Method',
			'options' => array(
				array(
					'label' => 'Select Method',
					'value' => -1
				),
				array(
					'label' => 'Bill Later',
					'value' => '00'
				)
			)
		)
	);

	private static $reg_fields = array(
		array(
			'type'  => 'hidden',
			'id'    => 'type-id[]',
			'value' => '%d'
		),
		array(
			'type'  => 'text',
			'id'    => 'type-name[]',
			'value' => '%s',
			'label' => 'Name'
		),
		array(
			'type'  => 'text',
			'id'    => 'type-description[]',
			'value' => '%s',
			'label' => 'Description',
			'desc'  => 'Describe the benefits of this particular type'
		),
		array(
			'type'  => 'number',
			'id'    => 'type-registrant_count[]',
			'value' => '%d',
			'label' => 'Registrant Count',
			'desc'  => 'The number of registrants per one of this type',
			'min'   => 1,
			'step'  => 1
		),
		array(
			'type'  => 'number',
			'id'    => 'type-price[]',
			'value' => '%f',
			'label' => 'Price',
			'min'   => 0,
			'step'  => 0.01
		),
		array(
			'type'  => 'number',
			'id'    => 'type-max_quantity[]',
			'value' => '%d',
			'label' => 'Max Quantity Per Registration',
			'desc'  => 'Limit how many of one type can be purchased per registration. Set to 0 for unlimited.',
			'min'   => 0,
			'step'  => 1
		),
		array(
			'type'  => 'number',
			'id'    => 'type-max_allowed[]',
			'value' => '%d',
			'label' => 'Max Quantity Per Event',
			'desc'  => 'Limit how many of one type can be purchased for an event. Set to 0 for unlimited.',
			'min'   => 0,
			'step'  => 1
		),
		array(
			'type'  => 'date',
			'id'    => 'type-start_date[]',
			'value' => '%s',
			'label' => 'Start Date',
			'desc'  => 'Leave blank to start immediately.'
		),
		array(
			'type'  => 'date',
			'id'    => 'type-end_date[]',
			'value' => '%s',
			'label' => 'End Date',
			'desc'  => 'Leave blank for continuous registration. This value overrides "Registration Open" if specified.'
		),
		array(
			'type'  => 'checkbox',
			'id'    => 'type-open[]',
			'value' => '%d',
			'label' => 'Registration Open',
			'desc'  => 'This type is currently available for registrations'
		)
	);

	public static function save($post_id, $post) {
		if (!in_array($post->post_type, static::post_types())) return $post_id;
		if (!isset($_POST[static::$nonce]) || !wp_verify_nonce($_POST[static::$nonce], static::$nonce)) return $post_id;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

		// save event values

		$event = TALLY_Event::with_post_id($post_id);

		foreach(static::$fields as $field) {
			$id = 'tally-'.$field['id'];
			$value = isset($_POST[$id]) ? $_POST[$id] : false;
			$event->$field['id'] = $value;
		}

		$event->save();

		// save reg types
	}

	public static function display($post, $args = null) {
		$event = TALLY_Event::with_post_id($post->ID);
		wp_nonce_field(static::$nonce, static::$nonce);
		echo '<h2>Event Details</h2>';
		TALLY_Field_Factory::display_fields(static::$fields, $event);
		static::display_reg_types($post->ID);
	}

	protected static function display_reg_types($post_id)
	{
		$types = TALLY_Registration_Type::with_post_id($post_id);
		echo '<h2>Registration Types</h2>';
		static::reg_type_template();
	}

	protected static function reg_type_template() {
		$fields = TALLY_Field_Factory::normalize_fields(static::$reg_fields);
		TALLY_Field_Factory::print_fields($fields);
	}

}
