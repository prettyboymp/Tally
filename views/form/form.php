<?php

class TALLY_Form {


	public static function display_form($content) {
		global $post;
		
		if (!is_singular()) return $content;

		if (!in_array(get_post_type(), TALLY_Meta_Box::post_types())) return $content;

		$event = TALLY_Event::with_post_id($post->ID);

		if (0 === $event->post_id || false === $event->enabled) return $content;

		$total_spots = $event->total_spots();
		$taken_spots = $event->taken_spots();
		$remaining_spots = $total_spots - $taken_spots;

		$types = TALLY_Registration_Type::with_post_id($post->ID);
		$types_available = false;
		foreach($types as $type) $types_available |= $type->is_available($remaining_spots);

		$output = '<div class="tally-form">';
		
		$now = new DateTime();
		if ( !$event->open 
			|| (!is_null($event->start_date) && $now < $event->start_date) 
			|| (!is_null($event->end_date) && $now > $event->end_date)
			|| $remaining_spots <= 0
			|| !$types_available) 
			$output .= '<p class="tally-message tally-closed">Registration is currently closed.</p>';
		else {



			$output .= '<p>Good to go</p>';



		}



		//$output.= static::contact_info($event);


		//debug output
		ob_start();
		var_dump($event);
		var_dump($types);
		$output .= ob_get_clean();

		$output .= '</div>';

		return $content.$output;
	}

	public static function initialize() {
		$cls = get_called_class();
		add_filter('the_content', array($cls, 'display_form'));
	}
}
