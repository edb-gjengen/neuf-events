<?php

/* Add custom columns. */
function change_columns( $cols ) {
	$custom_cols = array(
		'cb'        => '<input type="checkbox" />',
		'title'     => __( 'Title', 'neuf_event' ),
		'starttime' => __( 'Date & Time', 'neuf_event' ),
		'endtime'   => __( 'Ending Date & Time', 'neuf_event'),
		'venue'     => __( 'Venue', 'neuf_event' ),
		'type'      => __( 'Type', 'neuf_event' ),
		'promoperiod' => __( 'Promo Period', 'neuf_event' ),
		'date'      => __( 'Date Published', 'neuf_event' ),
		'author'    => __( 'Author', 'neuf_event' ),
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
		echo $endtime ? format_datetime($endtime) : __("None", 'neuf_event');
		break;
	case "type":
		echo the_terms( $post_id , 'event_type', '', ', ', '' );
		break;
	case "venue":
		echo get_post_meta( $post_id , '_neuf_events_venue', true);
		break;
	case "promoperiod":
		echo get_post_meta( $post_id, '_neuf_events_promo_period', true);
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
		'promoperiod'     => 'promoperiod',
	);
	return array_merge($cols, $custom_cols);
}
add_filter( "manage_edit-event_sortable_columns", "sortable_columns" );
/*
 * http://scribu.net/wordpress/custom-sortable-columns.html#comment-4732
 */
function starttime_column_orderby( $vars ) {
    if ( isset( $vars['orderby']) ) {
        if('starttime' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_neuf_events_starttime',
                'orderby' => 'meta_value'
            ) );
        }
        if ( 'endtime' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_neuf_events_endtime',
                'orderby' => 'meta_value'
            ) );
        }
    }
    return $vars;
}
add_filter( 'request', 'starttime_column_orderby' );


function add_admin_script_and_styles() {
	// Date-selection for events
    wp_register_style('jquery-ui-css', plugins_url("/neuf-events/style/jquery-ui-1.8.12.custom.css", dirname(__FILE__)));
	wp_register_style('jquery-ui-timepicker-css', plugins_url("/neuf-events/style/jquery-ui-timepicker-addon.css", dirname(__FILE__)));

	wp_register_script('timepicker', plugins_url("/neuf-events/script/jquery-ui-timepicker-addon.js", dirname(__FILE__)), array('jquery-ui-datepicker') );
	wp_register_script('timedefs', plugins_url("/neuf-events/script/timepickdef.js", dirname(__FILE__)));
	// form validation: http://docs.jquery.com/Plugins/Validation#API_Documentation
	wp_register_script('formvalidation', plugins_url("/neuf-events/script/jquery.validate.min.js", dirname(__FILE__)));
	wp_register_script('validation_rules', plugins_url("/neuf-events/script/validation_rules.js", dirname(__FILE__)));

	wp_enqueue_style('jquery-ui-css');  
	wp_enqueue_style('jquery-ui-timepicker-css');  
	wp_enqueue_script('jquery');      
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-mouse');
    wp_enqueue_script('jquery-ui-slider');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('timepicker');
	wp_enqueue_script('timedefs');
	wp_enqueue_script('formvalidation');
	wp_enqueue_script('validation_rules');
}
add_action('admin_enqueue_scripts', 'add_admin_script_and_styles');

/* Add metaboxes (with styles) */
function add_events_metaboxes() {

	add_meta_box(
		'neuf_events_details',
		__('Event Details', 'neuf_event'),
		'neuf_event_details',
		'event',
		'side',
		'high'
	);
}

function neuf_event_details() {
?>
	<div class="misc-pub-section">
		<?php neuf_date_details(); ?>
	</div>
	<div class="misc-pub-section ">
		<?php neuf_event_venue(); ?>
	</div>
	<div class="misc-pub-section ">
		<?php neuf_event_div(); ?>
	</div>
	<div class="misc-pub-section misc-pub-section-last">
        <?php
            /* Only editor or superior can set the promo period */
            if ( current_user_can('publish_posts') ) {
                neuf_event_promoperiod();
            }
        ?>
	</div>
<?php
}
/*
 * Date & Time
 * Note: Jquery datetimepickers is loaded in scripts/timepickdef.js
 */
function neuf_date_details() {
	global $post;

	$start = get_post_meta($post->ID, '_neuf_events_starttime', true);
	$end  = get_post_meta($post->ID, '_neuf_events_endtime', true);

	wp_nonce_field( 'neuf_events_nonce','neuf_events_nonce' );
?>
		<label for="_neuf_events_starttime"><?php _e('Starts', 'neuf_event'); ?>:</label><input type="text" class="datepicker required" name="_neuf_events_starttime"  value="<?php echo $start ? date("Y-m-d H:i", $start) : "" ?>" /><br />
		<label for="_neuf_events_endtime"><?php _e('Ends', 'neuf_event'); ?>:</label><input name="_neuf_events_endtime" type="text" class="datepicker" value="<?php echo $end ? date("Y-m-d H:i", $end) : "" ?>" /><br />
<?php
}

/* Venue */
function neuf_event_venue(){
	global $post;

	$venues = array(
        'Betong', 'Betonghaven', 'Biblioteket', 'BokCaféen',
        'Foajeen', 'Galleriet', 'Klubbscenen', 'Lillesalen',
        'M&oslash;rkerommet', 'Teaterscenen', 'Storsalen', 'Hele huset',
	);
	$neuf_event_venue = get_post_meta($post->ID, '_neuf_events_venue', true);
	_e('Venue:', 'neuf_event');
	echo '<select name="_neuf_events_venue">';

	$selected = false;
	foreach ($venues as $venue) {
		echo '<option value="'.$venue.'"';
		if($venue == $neuf_event_venue) {
			echo ' selected="selected"';
			$selected = true;
		}
		echo '>'.$venue.'</option>';
	}
	$other = __('Elsewhere', 'neuf_event');
	if ( $selected )
		echo('<option value="' . $other . '">' . $other . '</option>');
	else
		echo('<option value="' . $other . '" selected="selected">' . $other . '</option>');
	echo '</select><br />';
}
/* Promo period */
function neuf_event_promoperiod(){
	global $post;

	$promoperiod = array(
            __('Default', 'neuf_event'), __('Week', 'neuf_event'), __('Month', 'neuf_event'), __('Semester', 'neuf_event'),
	);
	$neuf_event_promoperiod = get_post_meta($post->ID, '_neuf_events_promo_period', true);
	_e('Promo Period', 'neuf_event');
	echo '<select name="_neuf_events_promo_period">';

	foreach ($promoperiod as $period) {
		echo '<option value="'.$period.'"';
		if($period == $neuf_event_promoperiod) {
			echo ' selected="selected"';
		}
		echo '>'.$period.'</option>';
	}
	echo '</select><br />';
}

/* Price and additional info. */
function neuf_event_div() {
	global $post;

	$event_price_regular = get_post_meta($post->ID, '_neuf_events_price_regular') ? get_post_meta($post->ID, '_neuf_events_price_regular', true) : "";
	$event_price_member  = get_post_meta($post->ID, '_neuf_events_price_member') ? get_post_meta($post->ID, '_neuf_events_price_member', true) : "";
	$event_bs = get_post_meta($post->ID, '_neuf_events_bs_url') ? get_post_meta($post->ID, '_neuf_events_bs_url', true) : "";
	$event_fb = get_post_meta($post->ID, '_neuf_events_fb_url', true);

	?>
        <label for="_neuf_events_price_regular"><?php _e("Price Regular", 'neuf_event'); ?></label>
		<input name="_neuf_events_price_regular" type="text" value="<?php echo $event_price_regular; ?>"></input><br />
        <label for="_neuf_events_price_member"><?php _e("Price Member", 'neuf_event'); ?></label>
		<input name="_neuf_events_price_member" type="text" value="<?php echo $event_price_member; ?>"></input><br />
		<label for="_neuf_events_bs_url"><?php _e("Billettservice address", 'neuf_event'); ?>:</label>
		<input type="text" name="_neuf_events_bs_url" value="<?php echo $event_bs; ?>" /><br />
		<label for="_neuf_events_fb_url"><?php _e("Facebook address", 'neuf_event'); ?>:</label>
		<input type="text" name="_neuf_events_fb_url" value="<?php echo $event_fb; ?>" />
	<?php
}

/* Format a unix timestamp respecting the options set in Settings->General. */
if( !function_exists('format_datetime') ) {
	function format_datetime($timestamp) {
		return date_i18n(get_option('date_format')." ".get_option('time_format'), intval($timestamp));
	}
}

/**
 *  Add our stuff to the 'right now' dashboard widget.
 */
function neuf_events_right_now_dashboard() {
	// First, let's add a line about our events
	$post_type = get_post_type_object( 'event' );

	$num_posts = wp_count_posts( $post_type->name );
	$num = number_format_i18n( $num_posts->publish );
	$text = _n( $post_type->labels->singular_name, $post_type->labels->name , intval( $num_posts->publish ) );
	if ( current_user_can( 'edit_posts' ) ) {

		$num = "<a href='edit.php?post_type=$post_type->name'>$num</a>";
		$text = "<a href='edit.php?post_type=$post_type->name'>$text</a>";
	}
	echo '<tr><td class="first b b-' . $post_type->name . '">' . $num . '</td>';
	echo '<td class="t ' . $post_type->name . '">' . $text . '</td></tr>';

	// Now, let's add a line about our event types
	$taxonomy = get_taxonomy( 'event_type' );

	$num_terms  = wp_count_terms( $taxonomy->name );
	$num = number_format_i18n( $num_terms );
	$text = _n( $taxonomy->labels->singular_name, $taxonomy->labels->name , intval( $num_terms ));
	if ( current_user_can( 'manage_categories' ) ) {

		$num = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$num</a>";
		$text = "<a href='edit-tags.php?taxonomy=$taxonomy->name'>$text</a>";
	}
	echo '<tr><td class="first b b-' . $taxonomy->name . '">' . $num . '</td>';
	echo '<td class="t ' . $taxonomy->name . '">' . $text . '</td></tr>';
}
add_action( 'right_now_content_table_end' , 'neuf_events_right_now_dashboard' );

?>
