<?php
/**
 * @package Neuf_Events
 * @author EDB-web
 * @version 0.2.1
 */
 /*  Copyright 2011  EDB-web  (email : edb-web@studentersamfundet.no)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 or later, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
Plugin Name: Neuf Events
Plugin URI: http://edb.studentersamfundet.no
Description: Manages events. Use with respect.
Author: EDB-web
Version: 0.2.1
Author URI: http://edb.studentersamfundet.no/
License: GNU General Public License 2 or later
 */

/*
 * label - A (plural) descriptive name for the post type marked for translation. Defaults to $post_type.
 * singular_label - A (singular) descriptive name for the post type marked for translation. Defaults to $label.
 * description - A short descriptive summary of what the post type is. Defaults to blank.
 * public - Whether posts of this type should be shown in the admin UI. Defaults to false.
 * exclude_from_search - Whether to exclude posts with this post type from search results. Defaults to true if the type is not public, false if the type is public.
 * publicly_queryable - Whether post_type queries can be performed from the front page.  Defaults to whatever public is set as.
 * show_ui - Whether to generate a default UI for managing this post type. Defaults to true if the type is public, false if the type is not public.
 * menu_position - The position in the menu order the post type should appear. Defaults to the bottom.
 * menu_icon - The url to the icon to be used for this menu. Defaults to use the posts icon.
 * inherit_type - The post type from which to inherit the edit link and capability type. Defaults to none.
 * capability_type - The post type to use for checking read, edit, and delete capabilities. Defaults to "post".
 * edit_cap - The capability that controls editing a particular object of this post type. Defaults to "edit_$capability_type" (edit_post).
 * edit_type_cap - The capability that controls editing objects of this post type as a class. Defaults to "edit_ . $capability_type . s" (edit_posts).
 * edit_others_cap - The capability that controls editing objects of this post type that are owned by other users. Defaults to "edit_others_ . $capability_type . s" (edit_others_posts).
 * publish_others_cap - The capability that controls publishing objects of this post type. Defaults to "publish_ . $capability_type . s" (publish_posts).
 * read_cap - The capability that controls reading a particular object of this post type. Defaults to "read_$capability_type" (read_post).
 * delete_cap - The capability that controls deleting a particular object of this post type. Defaults to "delete_$capability_type" (delete_post).
 * hierarchical - Whether the post type is hierarchical. Defaults to false.
 * supports - An alias for calling add_post_type_support() directly. See add_post_type_support() for Documentation. Defaults to none.
 * register_meta_box_cb - Provide a callback function that will be called when setting up the meta boxes for the edit form.  Do remove_meta_box() and add_meta_box() calls in the callback.
 * taxonomies - An array of taxonomy identifiers that will be registered for the post type.  Default is no taxonomies. Taxonomies can be registered later with register_taxonomy() or register_taxonomy_for_object_type().
 */

/**
 * Register the Event post type.
 */
add_action('init','neuf_events_post_type_event');
function neuf_events_post_type_event() {
	register_post_type( 
		'event',
		array(
			'label' => __('Events'),
			'singular_label' => __('Event'),
			'public' => true,
			'publicly_queryable' => true,
			'query_var' => 'event',
			'show_ui' => true,
			'capability_type' => 'post',
			'supports' => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				//'trackbacks',
				//'custom-fields',
				'comments',
				'revisions',
				'administrator'
			),
			'register_meta_box_cb' => 'neuf_events_post_type_event_meta_boxes',
		)
	);
}

/**
 * Add custom meta boxes for the Event post type.
 */
function neuf_events_post_type_event_meta_boxes() {
	// add_meta_box( $id, $title, $callback, $page, $context, $priority );
	add_meta_box('neuf_events_date_section','Time and Place','neuf_events_date_admin_html','event','page','high');
}

/**
 * Output admin HTML for the start date/time.
 */
function neuf_events_date_admin_html() {
	global $post;

	$start_date_timestamp = get_post_meta($post->ID, 'neuf_events_start_date', true);
	$event_end_time = get_post_meta($post->ID, 'neuf_events_end_time', true);
	$event_venue = get_post_meta($post->ID, 'neuf_events_venue', true);
	$event_type = get_post_meta($post->ID, 'neuf_events_type', true);
	$event_price = get_post_meta($post->ID, 'neuf_events_price',true);
	$event_fb_url = get_post_meta($post->ID, 'neuf_events_fb_url', true);
	$event_bs_url = get_post_meta($post->ID, 'neuf_events_bs_url', true);
	//$venues = array ("Det Norske Studentersamfund", "Glassbaren", "Storsalen");

	// Use nonce for verification
	echo '<input type="hidden" name="neuf_events_noncename" id="neuf_events_noncename" value="' . 
		wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	// The actual fields for data entry
	
	// Venue
	echo '<div>';
	echo '<label for="neuf_events_venue">Sted</label>';
	echo '<input type="text" name="neuf_events_venue" value="'.$event_venue.'" style="width:200px;margin-left:8px;" />';
	/*
	echo '<select name="neuf_events_venue">';
	foreach ($venues as $venue) {
		echo '<option value="'.$venue.'"';
		if ($event_venue == $venue) echo ' selected="selected"';
		echo '>'.$venue.'</option>';
	}
	echo '</select>';
	 */
	echo '</div> <!-- #neuf_events_place -->';

	echo 'Start:<br />';
	echo '<div id="neuf_event_time" style="margin-top:8px;">';
	echo '<label for="neuf_events_start_date">';
	echo $start_date_timestamp ? 'N&aring;v&aelig;rende dato: '.date("j. F Y H:i", $start_date_timestamp) : '<span style="color:red;">Ingen dato har blitt satt.</span>' ;
	echo '<br /></label>';

	// Day
	$day = $start_date_timestamp ? date('j', $start_date_timestamp) : 0;

	echo '<select name="neuf_events_day_value">';
	echo '<option value="null">Dag</option>';
	for ( $currentday = 1; $currentday <= 31; $currentday += 1) {
		echo '<option value="'.$currentday.'"';
		if ($currentday == $day) {echo ' selected="selected" ';}
		echo '>'.$currentday.'</option>';
	}
	echo "</select>";	

	// Month
	$month = $start_date_timestamp ? date('n', $start_date_timestamp) : date('n');

	$monthname = array(1=> "Januar", "Februar", "Mars", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Desember");

	echo '<select name="neuf_events_month_value">';
	//echo '<option value="null">M&aring;ned</option>';
	for($currentmonth = 1; $currentmonth <= 12; $currentmonth++)
	{
		echo '<option value="';
		echo intval($currentmonth);
		echo '"';
		if ($currentmonth == $month) {echo ' selected="selected" ';}
		echo '>'.$monthname[$currentmonth].'</option>';
	}
	echo '</select>';

	// Year
	$year = $start_date_timestamp ? date('Y', $start_date_timestamp) : date('Y');

	echo '<select name="neuf_events_year_value">';
	//echo '<option value="null">&Aring;r</option>';
	for ( $currentyear = date('Y') ; $currentyear <= date(Y) +5 ; $currentyear +=1 ) {
		echo "<option value=\"$currentyear\"";
		if ($currentyear == $year) {echo ' selected="selected" ';}
		echo ">$currentyear</option>";
	}
	echo "</select>";

	// Hour
	echo '<br/>';

	$hour = $start_date_timestamp ? date('H', $start_date_timestamp) : 18;

	echo '<select name="neuf_events_hour_value">';
	$c=00;
	while ($c <= 23) {
		echo "<option value=\"$c\"";
		if ($c == $hour) {echo ' selected="selected" ';}
		echo '>'.str_pad($c, 2, "0", STR_PAD_LEFT).'</option>';
		$c++;
	}
	echo "</select> : ";

	// Minute
	$min = $start_date_timestamp ? date('i', $start_date_timestamp) : 00;
	$i = array(00,04,13,15,30,45);

	echo '<select name="neuf_events_minute_value">';
	/*
	$c = 0;
	while ($c < 60) {
	 */
	foreach($i as $c) {
		echo '<option value="'.str_pad($c,2,"0", STR_PAD_LEFT).'"';
		if ($c == $min) {echo ' selected="selected" ';}
		echo '>'.str_pad($c,2,"0",STR_PAD_LEFT).'</option>';
		$c += 15;
	}

	echo "</select><br />";
	echo 'Slutt:<input name="neuf_events_end_time" value="' . $event_end_time . '" /><br />(f.eks. 18:00)';
	echo "<br />";
	echo "Type:";
	echo '<select name="neuf_events_type">';
	echo $neuf_event_type;
	$types = array('Annet','Debatt','Fest','Film','Foredag','Forfatteraften','Klubb','Konsert','Quiz','Teater','Upop','Stand-up');
	foreach($types as $type)
	{
		echo '<option value="' . $type . '"';
		if($type == $event_type)
			echo ' selected="selected"';
		echo '>' . $type . '</option>';
	}
	echo '</select>';
	echo '<h4>Andre detaljer</h4>';
	echo '<br />Pris:<br /><input name="neuf_events_price" value="' . $event_price . '" />';
	echo '<br />Billettservice&nbsp;url:<br /><input name="neuf_events_bs_url" value="' . $event_bs_url . '" />';
	echo '<br />Facebook&nbsp;url:<br /><input name="neuf_events_fb_url" value="' . $event_fb_url . '" />';
	echo '<p style="font-style:italic;">(bare la feltene stå tomme om de ikke er relevante)</p>';
	echo '</div> <!-- #neuf_events_time -->';

}

/**
 * Admin
 */

/**
 *  When the post is saved, saves our custom data
 */
add_action('save_post', 'neuf_events_save_start_date');
function neuf_events_save_start_date( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
//var_dump($_POST['neuf_events_nouncename']);
//	if ( !wp_verify_nonce( $_POST['neuf_events_noncename'], plugin_basename(__FILE__) )) {
//		return $post_id;
//	}

	// verify if this is an auto save routine. If it is, our form has not been submitted, and
	// we dont want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;

	// Check permissions
	if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;

	// OK, we're authenticated: we need to find and save the data

	// Get posted data
	$year = $_POST['neuf_events_year_value'];
	$month = $_POST['neuf_events_month_value'];
	$day = $_POST['neuf_events_day_value'];
	$hour = $_POST['neuf_events_hour_value'];
	$minute = $_POST['neuf_events_minute_value'];
	$event_venue = $_POST['neuf_events_venue'];
	$event_type = $_POST['neuf_events_type'];
	$events_price = $_POST['neuf_events_price'];
	$events_bs_url = $_POST['neuf_events_bs_url'];
	$events_fb_url = $_POST['neuf_events_fb_url'];
	$events_end_time = $_POST['neuf_events_end_time'];
	// Convert time data to timestamp
	//$date = strtotime( $month.'/'.$day.'/'.$year.' '.$hour.':'.$min );
	$date = strtotime( $year.'-'.$month.'-'.$day.'T'.$hour.':'.$minute );
	

	$quicksave = array('events_price','events_bs_url','events_fb_url','events_end_time');
	foreach($quicksave as $save)
	{
		if( !($meta = get_post_meta($post_id, 'neuf_' . $save)))
		{
			add_post_meta($post_id, 'neuf_' . $save, true);
		}
		else if($meta != $$save)
		{
			update_post_meta($post_id, 'neuf_' . $save, $$save);
		}
	}
	// Save timestamp
	if ( !get_post_meta($post_id, 'neuf_events_start_date') ) {
		add_post_meta($post_id, 'neuf_events_start_date', $date, true);
	} elseif ( $date != get_post_meta($post_id, 'neuf_events_start_date', true) ) {
		update_post_meta($post_id, 'neuf_events_start_date', $date);
	}
	die(get_post_meta($post_id, 'neuf_events_start_date', true));
	// Save venue
	if ( !get_post_meta($post_id, 'neuf_events_venue') ) {
		add_post_meta($post_id, 'neuf_events_venue', $event_venue, true);
	} elseif ( $event_venue != get_post_meta($post_id, 'neuf_events_venue', true) ) {
		update_post_meta($post_id, 'neuf_events_venue', $event_venue);
	}
	
	
	if ( !get_post_meta($post_id, 'neuf_events_type') ) {
		add_post_meta($post_id, 'neuf_events_type', $event_type, true);
	}elseif($event_type != get_post_meta($post_id, 'neuf_events_type', true)){
		update_post_meta($post_id, 'neuf_events_type',$event_type);
	}

	return $date;
}

/**
 * Add shortcode which will output the program.
 */
add_shortcode( 'neuf-events-program' , 'neuf_events_program');
function neuf_events_program() {
	global $post,$wp_locale;
	$events = new WP_Query( array(
		'post_type' => 'event',
		'posts_per_page' => -1,
		//'meta_key' => 'neuf_events_start_date',
		//'orderby' => 'meta_value',
		'order' => 'ASC'
	) );
	$html = '';

	if ( $events->have_posts() ) :

		$date = "";

		$html .= '<table class="event-table">';

		while ( $events->have_posts() ) :
			$events->the_post();

			$start_date = get_post_meta( $post->ID , 'neuf_events_start_date' , true );
			$venue = get_post_meta( $post->ID , 'neuf_events_venue' , true );
			$type = get_post_meta( $post->ID, 'neuf_events_type', true);

			$date_new = date ( 'Y-m-d' , $start_date );
			if ( $start_date && $date != $date_new ) {
				$date = $date_new;
				$html .= '    <tr>';
				$html .= '        <td colspan="3"><strong>' . ucfirst($wp_locale->get_weekday(date('w',$start_date))) . ' ' . date ('d. F',$start_date) . '</strong></td>';
				$html .= '    </tr>';
			}

			$html .= '    <tr>';
			$html .= '        <td class="time" style="width:10%;">' . date ( 'G:i' , $start_date ) . '</td>';
			$html .= '        <td class="title" style="padding-right:10px;"><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>';
			$html .= '        <td class="type" style="font-size:smaller;">' . $type . '</td>';
			$html .= '        <td class="place" style="width:27%;padding-left:10px;">' . $venue . '</td>';
			$html .= '    </tr>';

		endwhile;

		$html .= '</table><!-- .event-table -->';

	endif;

	return $html;
}


function get_program_ajax()
{
	$neuf_events = new WP_Query( array(
                    'post_type' => 'event',
                    'posts_per_page' => -1,'orderby' => 'meta_value',
                'order' => 'ASC'

            ) );
	foreach($neuf_events->posts as &$post)
        {
                $meta = get_post_custom($post->ID);

                $post->neuf_events_start_date = $meta['neuf_events_start_date'][0];
                $post->neuf_events_venue = $meta['neuf_events_venue'][0];
                $post->neuf_events_type = @$meta['neuf_events_type'][0];
                $post->neuf_events_price = @$meta['neuf_events_price'][0];
                $post->neuf_events_bs_url = @$meta['neuf_events_bs_url'][0];
                $post->neuf_events_fb_url = @$meta['neuf_events_fb_url'][0];

	}
	
	echo json_encode($neuf_events->posts);
	die;
	return true;
}


add_action('wp_ajax_programajax', 'get_program_ajax');
add_action('wp_ajax_nopriv_programajax', 'get_program_ajax');
