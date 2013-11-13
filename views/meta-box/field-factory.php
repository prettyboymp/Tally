<?php

class TALLY_Field_Factory {

//-----------------------------------------------------------------------------
// SETTINGS
//-----------------------------------------------------------------------------

	protected static $shared_field_defaults = array(
		'label'       => '',
		'desc'        => '',
		'id'          => '',
		'class'       => '',
		'default'     => '',
		'hidden'      => false
	);

	protected static $option_default = array(
		'label' => '',
		'value' => ''
	);

	protected static $field_defaults = array(

		//ERROR - DEFAULT IF SOMETHING WENT WRONG
		'error' => array(),

		'hidden' => array(
			'hidden' => true
		),

		//TEXT INPUT
		'text' => array(
			'attr' => array()
		),

		//NUMBER INPUT
		'number' => array(
			'min'  => false,
			'max'  => false,
			'step' => false,
			'attr' => array()
		),

		//CHECKBOX INPUT
		'checkbox' => array(
			'default' => false
		),

		//SELECT DROPDOWN BOX
		'select' => array(
			'options' => array(),
			'attr'    => array()
		),

		//DATE PICKER
		'date' => array(
			'attr' => array()
		),

		//REPEATABLE
		'repeater' => array(
		)
	);

//-----------------------------------------------------------------------------
// NORMALIZATION
//-----------------------------------------------------------------------------

	public static function normalize_fields($fields) {
		$new_fields = array();
		if (!is_array($fields)) return $new_fields;

		foreach($fields as $field) 
			$new_fields[] = static::normalize_field($field);

		return $new_fields;
	}

	protected static function normalize_field($field) {
		if (!is_array($field)) $field = array();

		if (!isset($field['type']) || !array_key_exists($field['type'], static::$field_defaults))
			$field['type'] = 'error';

		$field = wp_parse_args($field, static::$shared_field_defaults);
		$field = wp_parse_args($field, static::$field_defaults[$field['type']]);

		if (isset($field['options'])) $field['options'] = static::normalize_options($field['options']);

		return $field;
	}

	protected static function normalize_options($options) {
		if (!is_array($options)) $options = array();
		$new_options = array();

		foreach($options as $key => $option) {		
			if (!is_array($option)) {
				if (is_string($key)) $option = array('value' => $key, 'label' => $option);
				else $option = array('value' => $option);
			}

			$option = wp_parse_args($option, static::$option_default);

			if (!$option['label']) $option['label'] = $option['value'];
			if (!$option['value']) $option['value'] = $option['label'];

			$new_options[] = $option;
		}

		return $new_options;
	}

//-----------------------------------------------------------------------------
// VALUE RETRIEVAL
//-----------------------------------------------------------------------------

	protected static function apply_field_value($field, $event, $prefix = '') {

		$datum = str_replace($prefix, '', $field['id']);
		$datum = str_replace('[]', '', $datum);
		$field['value'] = $event->$datum;

		if ($field['type'] === 'date' && $field['value'] instanceof DateTime)
			$field['value'] = $field['value']->format('m/d/Y');

		if ('' === $field['value']) $field['value'] = $field['default'];
		return $field;
	}

	protected static function apply_field_values($fields, $event, $prefix = '') {
		$new_fields = array();
		if (!is_array($fields)) return $new_fields;

		foreach($fields as $field) 
			$new_fields[] = static::apply_field_value($field, $event, $prefix);

		return $new_fields;
	}

//-----------------------------------------------------------------------------
// VALUE SANITIZATION
//-----------------------------------------------------------------------------

	protected static function sanitize_field_value($field) {
		$value = isset($_POST[$field['id']]) ? $_POST[$field['id']] : '';
		if ('checkbox' === $field['type']) $value = (bool)$value; 
		$field['value'] = $value;
		return $field;
	}

	protected static function sanitize_field_values($fields) {
		$new_fields = array();
		if (!is_array($fields)) return $new_fields;

		foreach($fields as $field) 
			$new_fields[] = static::sanitize_field_value($field);

		return $new_fields;
	}

//-----------------------------------------------------------------------------
// DISPLAY
//-----------------------------------------------------------------------------

	public static function display_fields($fields, $event, $prefix = '') {
		$fields = static::normalize_fields($fields);
		$fields = static::apply_field_values($fields, $event, $prefix);
		static::print_fields($fields);
	}

	public static function print_fields($fields) {
		$hidden_fields = array();
		
		echo '<table class="form-table tally-fields">';
		foreach($fields as $field) {
			if ($field['hidden']) {
				$hidden_fields[] = $field;
				continue;
			}
			$class = static::field_class($field);
			echo "<tr class=\"$class\"><th><label for=\"{$field['id']}\">{$field['label']}</label></th><td>";
			static::display_field($field);
			echo "</td></tr>";
		}
		echo '</table>';

		foreach($hidden_fields as $field) static::display_field($field);
	}

	protected static function display_field($field) {

		$id = 'tally-'.$field['id'];

		switch($field['type']) {

			//TEXT INPUT
			case 'text':
				printf(
					'<input type="text" name="%1$s" id="%1$s" value="%2$s" %3$s/><br/><span class="description">%4$s</span>',
					$id,
					esc_attr($field['value']),
					static::field_attributes($field),
					$field['desc']
				);
			break;

			case 'hidden':
					printf(
					'<input type="hidden" name="%1$s" id="%1$s" value="%2$s" %3$s/>',
					$id,
					esc_attr($field['value']),
					static::field_attributes($field)
				);			
			break;

			case 'number':
				printf(
					'<input type="number" name="%1$s" id="%1$s" value="%2$s" %3$s %5$s %6$s %7$s/><br/><span class="description">%4$s</span>',
					$id,
					esc_attr($field['value']),
					static::field_attributes($field),
					$field['desc'],
					$field['min'] !== false ? ' min="'.$field['min'].'" ' : '',
					$field['max'] !== false ? ' max="'.$field['max'].'" ' : '',
					$field['step'] !== false ? ' step="'.$field['step'].'" ' : ''
				);
			break;

			//CHECKBOX
			case 'checkbox':
				printf(
					'<input type="checkbox" name="%1$s" id="%1$s" %2$s /><label for="%1$s"> %3$s</label>',
					$id,
					$field['value'] ? 'checked="checked"' : '',
					$field['desc']
				);
			break;

			//SELECT DROP DOWN LIST
			case 'select':
				$options = implode('', array_map(
					function($o) use ($field) { return sprintf(
						'<option value="%s" %s>%s</option>',
						esc_attr($o['value']),
						$field['value'] === $o['value'] ? 'selected="selected"' : '',
						$o['label']
					);},
					$field['options']
				));
				printf(
					'<select name="%1$s" id="%1$s" %2$s>%3$s</select><br/><span class="description">%4$s</span>',
					$id,
					static::field_attributes($field),
					$options,
					$field['desc']
				);
			break;

			case 'date':
				printf(
					'<input type="text" name="%1$s" id="%1$s" value="%2$s" %3$s/><br/><span class="description">%4$s</span><script>%5$s</script>',
					$id,
					esc_attr($field['value']),
					static::field_attributes($field),
					$field['desc'],
					";jQuery(function($) { $('#{$id}').datepicker(); });"
				);
			break;

			case 'repeater':
				$template = '<li><input type="text" name="%1$s[]" value="%2$s"/> <span class="sort handle button">↕</span>	<input type="button" class="tally-repeater-remove button" value="-" /></li>';
				$options = !is_array($field['value'])
					? ''
					: implode('', array_map(
						function($o) use ($field, $template, $id) { return sprintf($template, $id, esc_attr($o)); },
						$field['value']
					));
				$js_template = sprintf($template, $id, '');
				$js = "
					;jQuery(function($) {
						var template = $('$js_template');
						var wrap = $('#tally-field-id-{$id}');
						var list = $('#tally-repeater-list-{$id}');
						var add  = $('#tally-repeater-button-{$id}');

						add.click(function(e) {
							e.preventDefault();
							list.append(template.clone(true));
						});

						list.on('click', '.tally-repeater-remove', function(e) {
							e.preventDefault();
							$(this).closest('li').remove();
						});

						list.sortable({
							opacity: 0.6,
							revert: true,
							cursor: 'pointer',
							handle: '.handle'
						});
					});
				";
				printf(
					'<ul id="tally-repeater-list-%1$s" class="tally-repeater-list">%2$s</ul>
					<input type="button" class="tally-repeater-button button" id="tally-repeater-button-%1$s" value="Add Field" />
					<br><span class="description">%3$s</span></div><script>%4$s</script>',
					$id,
					$options,
					$field['desc'],
					$js
				);
			break;

			//ERROR DISPLAY
			case 'error': /* fall through */
			default:
				echo '<span class="description">ERROR: The field could not be rendered.</span>';
		}
	}

	protected static function field_class($field) {
		$classes = explode(' ', $field['class']);
		$classes[] = 'tally-field';
		$classes[] = "tally-field-type-{$field['type']}";
		$classes[] = "tally-field-id-{$field['id']}";
		return implode(' ', $classes);
	}

	protected static function field_attributes($field) {
		$attr = isset($field['attr']) ? $field['attr'] : '';
		if (!is_array($attr)) return $attr;
		$output = '';
		foreach($attr as $key => $val)
			$output .= sprintf(' %s="%s"', $key, $val);
		return $output;
	}

}
