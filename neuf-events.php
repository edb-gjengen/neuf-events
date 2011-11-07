<?php
/*
  Plugin Name: neuf-events
  Plugin URI: http://www.studentersamfundet.no
  Description: Events custom post type
  Version 0.2.1
  Author: EDB-web
  Author URI: http://www.studentersamfundet.no
  License: GPL v2 or later
 */

/* TODO:
 *  - Pickup stuff from: http://codex.wordpress.org/Post_Types
 */
require_once("neuf-events-admin.php");

/* Create the fields the post type should have */
function neuf_events_post_type() {
	$labels = array(
		'name'                  =>      __( 'Events'                        ),
		'singular_name'         =>      __( 'Event'                         ),
		'add_new'               =>      __( 'Add New Event'                 ),
		'add_new_item'          =>      __( 'Add New Event'                 ),
		'edit_item'             =>      __( 'Edit Event'                    ),
		'new_item'              =>      __( 'Add New Event'                 ),
		'view_item'             =>      __( 'View Event'                    ),
		'search_items'          =>      __( 'Search Event'                  ),
		'not_found'             =>      __( 'No events found'               ),
		'not_found_in_trash'    =>      __( 'No events found in trash'      )
	);
	register_post_type(
		'event',
		array(
			'labels'				=> $labels,
			'menu_position'			=>  5,
			'public'				=>  true,
			'publicly_queryable'	=>  true,
			'query_var'				=>  'event',
			'show_ui'				=>  true,
			'capability_type'		=>  'post',
			'supports'				=>  array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'comments',
				'revisions',
				'administrator',
			),
			'register_meta_box_cb' => 'add_events_metaboxes',
		)
	);
}


/* When the post is saved, save our custom data */ 
function neuf_events_save_info( $post_id ) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['neuf_events_nonce'], 'neuf_events_nonce' )) {
		return $post_id;
	}

	// If this is an auto save routine, our form has not been submitted,
	// and we do nothing.
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

	// Check permissions
	if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;

	// Date strings are converted to unix time
	$tosave['_neuf_events_starttime'] = strtotime( $_POST['_neuf_events_starttime'] );
	$tosave['_neuf_events_endtime'] = strtotime( $_POST['_neuf_events_endtime'] );
	$tosave['_neuf_events_price'] = $_POST['_neuf_events_price'];
	$tosave['_neuf_events_bs_url'] = $_POST['_neuf_events_bs_url'];
	$tosave['_neuf_events_fb_url'] = $_POST['_neuf_events_fb_url'];
	$tosave['_neuf_events_type'] = $_POST['_neuf_events_type'];
	$tosave['_neuf_events_venue'] = $_POST['_neuf_events_venue'];

	// Update or add post meta
	foreach($tosave as $key=>$value)
		if(!update_post_meta($post_id, $key, $value)) {
			add_post_meta($post_id, $key, $value, true);
		}

	return $post_id;
}

/** View of the custom page */
function neuf_events_program() {
	global $post, $wp_locale;

	$events = new WP_Query( array(
		'post_type' => 'event',
		'posts_per_page' => -1,
		'meta_key' => '_neuf_events_starttime',
		'orderby' => 'meta_value',
		'order' => 'ASC'
	) );

	if ( $events->have_posts() ) :
		$date = "";
	ob_start();

	echo '<table class="event-table">';

	while ( $events->have_posts() ) : $events->the_post();
		$venue = get_post_meta( $post->ID, '_neuf_events_venue', true);
		$type  = get_post_meta( $post->ID, '_neuf_events_type', true);
		$price = get_post_meta( $post->ID, '_neuf_events_price', true);
		$time = get_post_meta( $post->ID, '_neuf_events_starttime', true);
		?>
			<tr>
				<td class="day">
					<?php echo date('l d. F', $time); ?>
				</td>
				<td class="time">
					kl <?php echo date("H.i", $time); ?>
				</td>
				<td class="title">
					<a href="<?php the_permalink();?>"><?php the_title();?></a>
				</td>
				<td class="type">
					<?php echo $type; ?>
				</td>
				<td class="place">
					<?php echo $venue;?>
				</td>
			</tr>
	<?php
	endwhile;

	echo '</table><!-- .event-table -->';
endif;

$html = ob_get_contents();
ob_end_clean();
return $html;
}

/* Register the event post type */
add_action('init', 'neuf_events_post_type');
add_action('save_post', 'neuf_events_save_info');
add_action('publish_neuf_events', 'neuf_events_publish' );
/* Register shortcode for program. */
add_shortcode('neuf-events-program', 'neuf_events_program');

?>
