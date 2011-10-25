<?php

/* Add custom columns. */
function change_columns( $cols ) {
	$custom_cols = array(
		'cb' => '<input type="checkbox" />',
		'starttime' => __( 'Dato og klokkeslett', 'trans' ),
		'endtime' => __( 'Sluttdato og -klokkeslett', 'trans' ),
	);
	return array_merge($cols, $custom_cols);
}
add_filter( "manage_event_posts_columns", "change_columns" );

// Add values to the custom columns
function custom_columns( $column, $post_id ) {
	switch ( $column ) {
	case "starttime":
		$starttime = get_post_meta( $post_id, 'neuf_events_starttime', true);
		echo date_i18n(get_option('date_format') . " k\l. " .get_option('time_format'), $starttime );
		break;
	case "endtime":
		$endtime = get_post_meta( $post_id, 'neuf_events_endtime', true);
		if( $endtime ) {
			echo date_i18n(get_option('date_format') . " k\l. " .get_option('time_format'), $endtime );
		} else {
			echo __("Ikke satt");
		}
		break;
	}
}
add_action( "manage_posts_custom_column", "custom_columns", 10, 2 );

// Make these columns sortable
function sortable_columns( $cols ) {
	$custom_cols = array(
		'starttime' => 'starttime',
		'endtime' => 'endtime',
	);
	return array_merge($cols, $custom_cols);
}
add_filter( "manage_edit-event_sortable_columns", "sortable_columns" );

/* Add metaboxes (with styles) */
function add_events_metaboxes() {
	// Date-selection for events
	wp_register_style('timecss', plugins_url("/neuf-events/style/jquery-ui-1.8.12.custom.css", dirname(__FILE__)));

	wp_register_script('timepicker', plugins_url("/neuf-events/script/jquery-ui-timepicker-addon.js", dirname(__FILE__)));
	wp_register_script('timedefs', plugins_url("/neuf-events/script/timepickdef.js", dirname(__FILE__)));
	// for upgrading jQuery ui, get core, widget, mouse, slider and datepicker
	wp_register_script('custom-jqui', plugins_url("/neuf-events/script/jquery-ui-1.8.11.custom.min.js", dirname(__FILE__)));

	// form validation: http://docs.jquery.com/Plugins/Validation#API_Documentation
	wp_register_script('formvalidation', plugins_url("/neuf-events/script/jquery.validate.min.js", dirname(__FILE__)));
	wp_register_script('validation_rules', plugins_url("/neuf-events/script/validation_rules.js", dirname(__FILE__)));

	wp_enqueue_style('timecss');  
	wp_enqueue_script('jquery');      
	wp_enqueue_script('custom-jqui');
	wp_enqueue_script('timepicker');
	wp_enqueue_script('timedefs');
	wp_enqueue_script('formvalidation');
	wp_enqueue_script('validation_rules');

	add_meta_box(
		'neuf_events_timestamps',
		__('Dato og klokkeslett'),
		'neuf_date_custom_box',
		'event',
		'side',
		'high'
	);

	add_meta_box(
		'neuf_event_type',
		__('Arrangementstype'),
		'neuf_eventtype_custom_box',
		'event',
		'side',
		'high'
	);

	add_meta_box(
		'neuf_eventvenue',
		__('Sted'),
		'neuf_eventvenue_custom_box',
		'event',
		'side'
	);

	add_meta_box(
		'neuf_event_div',
		__('Arrangementsdetaljer'),
		'neuf_event_div_custom_box',
		'event'
	);
}

/*
 * Time metabox.
 * 
 * Note: Jquery datetimepickers is loaded in scripts/timepickdef.js
 */
function neuf_date_custom_box() {
	global $post;

	$start = get_post_meta($post->ID, 'neuf_events_starttime', true);
	$end  = get_post_meta($post->ID, 'neuf_events_endtime', true);

	wp_nonce_field( 'neuf_events_nonce','neuf_events_nonce' );

	if( $start ) {
		echo '<label for="neuf_events_starttime">Start:</label><input type="text" class="datepicker required" name="neuf_events_starttime"  value="'.date("d.m.Y H:i", intval($start)).'" /><br />';
		echo 'N&aring;v&aelig;rende dato: '.date("d.m.Y H:i", intval($start))."<br />";

	} else {
		echo '<label for="neuf_events_starttime">Start:</label><input type="text" class="datepicker required" name="neuf_events_starttime" value="" /><br />';
		echo '<span style="color:red;">Startdato og -klokkeslett er ikke satt.</span><br />';

	}

	if( $end ) {
		echo '<label for="neuf_events_endtime">Slutt:</label><input name="neuf_events_endtime" type="text" class="datepicker" value="'.date("d.m.Y H:i", intval($end)).'" /><br />';
	} else {
		echo '<label for="neuf_events_endtime">Slutt:</label><input name="neuf_events_endtime" type="text" class="datepicker" value="" /><br />';
	}
}

/* Event type metabox */
function neuf_eventtype_custom_box(){
	global $post;

	$neuf_event_type = get_post_meta($post->ID, 'neuf_events_type', true);

	echo "Type:";
	echo '<select name="neuf_events_type">';
	echo $neuf_event_type;

	$types = array(
		'Annet' ,'Debatt','Fest','Film','Foredag',
		'Forfatteraften','Klubb','Konsert','Quiz',
		'Teater','Upop','Stand-up'
	);

	foreach($types as $type){
		echo '<option value="' . $type . '"';
		if($type == $neuf_event_type)
			echo ' selected="selected"';
		echo '>' . $type . '</option>';
	}
	echo '</select>';
}

/* Venue metabox */
function neuf_eventvenue_custom_box(){
	global $post;

	$neuf_event_venue = get_post_meta($post->ID, 'neuf_events_venue', true);

	echo 'Sted:';
	echo '<select name="neuf_events_venue">';

	$venues = query_posts( array('post_type' => 'venue', 'posts_per_page' => -1, 'order' => 'ASC'));
	echo $neuf_event_venue;

	foreach ($venues as $venue) {
		if ($venue->post_title != '' ){
			echo '<option value="'.$venue->post_title.'"';
			if($venue->post_title == $neuf_event_venue)
				echo ' selected="selected"';
			echo '>'.$venue->post_title.'</option>';
		}
	}
	echo '</select>';
}

/* Metabox with additional info. */
function neuf_event_div_custom_box(){
	global $post;

	$event_price = get_post_meta($post->ID, 'neuf_events_price') ? get_post_meta($post->ID, 'neuf_events_price', true) : "";
	$event_bs = get_post_meta($post->ID, 'neuf_events_bs_url') ? get_post_meta($post->ID, 'neuf_events_bs_url', true) : "";
	$event_fb = get_post_meta($post->ID, 'neuf_events_fb_url', true);

	echo '<br />Pris:<br /><input name="neuf_events_price" type="text"y value="'.$event_price.'" />';
	echo '<br />Billettservice url:<br /><input type="text" name="neuf_events_bs_url" value="'.$event_bs.'" />';
	echo '<br />Facebook url:<br /><input type="text" name="neuf_events_fb_url" value="'.$event_fb.'" />';
	echo '<p style="font-style:italic;">(bare la feltene stå tomme om de ikke er relevante)</p>';
}

?>
