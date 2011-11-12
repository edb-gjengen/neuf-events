<?php

/* Add custom columns. */
function change_columns( $cols ) {
	$custom_cols = array(
		'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Tittel', 'trans' ),
		'author'    => __( 'Forfatter', 'trans' ),
		'date'      => __( 'Publiseringsdato', 'trans' ),
		'type'      => __( 'Type', 'trans' ),
		'starttime' => __( 'Dato og klokkeslett', 'trans' ),
		'endtime'   => __( 'Sluttdato og -klokkeslett', 'trans' ),
		'venue'     => __( 'Sted', 'trans' )
	);
	return $custom_cols;
}
add_filter( "manage_event_posts_columns", "change_columns" );

// Add values to the custom columns
function custom_columns( $column, $post_id ) {
	switch ( $column ) {
	case "starttime":
		$starttime = intval(get_post_meta( $post_id, '_neuf_events_starttime', true));
		echo format_datetime($starttime);
		break;
	case "endtime":
		$endtime = intval(get_post_meta( $post_id, '_neuf_events_endtime', true));
		echo $endtime ? format_datetime($endtime) : __("Ikke satt");
		break;
	case "type":
		echo the_terms( $post_id , 'event_type', '', ', ', '' );
		break;
	case "venue":
		echo get_post_meta( $post_id , '_neuf_events_venue', true);
		break;
	}
}
add_action( "manage_posts_custom_column" , "custom_columns", 10, 2 );

// Make these columns sortable
function sortable_columns( $cols ) {
	$custom_cols = array(
		'starttime' => 'starttime',
		'endtime'   => 'endtime',
		'type'      => 'type',
		'venue'     => 'venue',
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
		'neuf_events_details',
		__('Arrangementsdetaljer'),
		'neuf_date_details',
		'event',
		'side',
		'high'
	);

	add_meta_box(
		'neuf_event_div',
		__('Ymse arrangementsdetaljer'),
		'neuf_event_div',
		'event'
	);
}

/*
 * Time metabox.
 * 
 * Note: Jquery datetimepickers is loaded in scripts/timepickdef.js
 */
function neuf_date_details() {
	global $post;
	echo '<div class="misc-pub-section">';

	$start = get_post_meta($post->ID, '_neuf_events_starttime', true);
	$end  = get_post_meta($post->ID, '_neuf_events_endtime', true);

	wp_nonce_field( 'neuf_events_nonce','neuf_events_nonce' ); ?>

	<label for="_neuf_events_starttime">Start:</label><input type="text" class="datepicker required" name="_neuf_events_starttime"  value="<?php echo $start ? date("Y-m-d h:i", $start) : "" ?>" /><br />
		<label for="_neuf_events_endtime">Slutt:</label><input name="_neuf_events_endtime" type="text" class="datepicker" value="<?php echo $end ? date("Y-m-d h:i", $end) : "" ?>" /><br />
	</div>';
	<div class="misc-pub-section misc-pub-section-last">
		<?php neuf_event_venue(); ?>
	</div>
<?php
}

/* Venue metabox */
function neuf_event_venue(){
	global $post;

	$venues = array(
		'Betong', 'Betonghaven','Biblioteket', 'BokCaféen',
		'Klubbscenen', 'Lillesalen', 'Teaterscenen', 'Storsalen',
	);
	$neuf_event_venue = get_post_meta($post->ID, '_neuf_events_venue', true);
	echo 'Sted: ';
	echo '<select name="_neuf_events_venue">';

	foreach ($venues as $venue) {
		echo '<option value="'.$venue.'"';
		if($venue == $neuf_event_venue) {
			echo ' selected="selected"';
		}
		echo '>'.$venue.'</option>';
	}
	echo '<option vlaue="Annet">Annet</option>';
	echo '</select><br />';
}

/* Metabox with additional info. */
function neuf_event_div(){
	global $post;

	$event_price = get_post_meta($post->ID, '_neuf_events_price') ? get_post_meta($post->ID, '_neuf_events_price', true) : "";
	$event_bs = get_post_meta($post->ID, '_neuf_events_bs_url') ? get_post_meta($post->ID, '_neuf_events_bs_url', true) : "";
	$event_fb = get_post_meta($post->ID, '_neuf_events_fb_url', true);

	?>
	<div class="misc-pub-section misc-pub-section-last">
		<label for="_neuf_events_price">Pris:</label><br />
		<input name="_neuf_events_price" type="text" value="<?php echo $event_price; ?>"></input><br />
		<label for="_neuf_events_bs_url">Billettservice adresse:</label>
		<input type="text" name="_neuf_events_bs_url" value="<?php echo $event_bs; ?>" /><br />
		<label for="_neuf_events_fb_url">Facebook addresse:</label>
		<input type="text" name="_neuf_events_fb_url" value="<?php echo $event_fb; ?>" />
	</div>
	<?php
}

/* Format a unix timestamp respecting the options set in Settings->General. */
if(!function_exists('format_datetime')) {
	function format_datetime($timestamp) {
		return date_i18n(get_option('date_format')." ".get_option('time_format'), intval($timestamp));
	}
}

?>
