<?php
/**
 * Registers the event post type.
 *
 * This post type will be used to store events to our system. With events, we mean such events as concerts, movie screenings, parties and so on (as opposed to MouseOverEvents and the like).
 */

/* Create the fields the post type should have */
function neuf_events_post_type() {
	$labels = array(
		'name'                  =>      __( 'Events', 'neuf_event'),
		'singular_name'         =>      __( 'Event', 'neuf_event'),
		'add_new'               =>      __( 'Add New', 'neuf_event'),
		'add_new_item'          =>      __( 'Add New', 'neuf_event'),
		'edit_item'             =>      __( 'Edit Event', 'neuf_event'),
		'new_item'              =>      __( 'Add New Event', 'neuf_event'),
		'view_item'             =>      __( 'View Event', 'neuf_event'),
		'search_items'          =>      __( 'Search Events', 'neuf_event'),
		'not_found'             =>      __( 'No events found', 'neuf_event'),
		'not_found_in_trash'    =>      __( 'No events found in trash', 'neuf_event')
	);
	register_post_type(
		'event',
		array(
			'labels'             => $labels,
			'menu_position'      => 5,
			'public'             => true,
			'publicly_queryable' => true,
			'query_var'          => 'event',
			'show_ui'            => true,
			'capability_type'    => 'post',
			'supports'           => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'comments',
				'revisions',
				'administrator',
				'custom-fields'
			),
			'register_meta_box_cb' => 'add_events_metaboxes',
		)
	);
}


/* When the post is saved, save our custom data */ 
function neuf_events_save_post( $post_id, $post ) {
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !array_key_exists('neuf_events_nonce', $_POST) || !wp_verify_nonce( $_POST['neuf_events_nonce'], 'neuf_events_nonce' )) {
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
	$tosave['_neuf_events_price_regular'] = $_POST['_neuf_events_price_regular'];
	$tosave['_neuf_events_price_member'] = $_POST['_neuf_events_price_member'];
	$tosave['_neuf_events_bs_url'] = $_POST['_neuf_events_bs_url'];
	$tosave['_neuf_events_fb_url'] = $_POST['_neuf_events_fb_url'];
	$tosave['_neuf_events_venue'] = $_POST['_neuf_events_venue'];
	$tosave['_neuf_events_promo_period'] = $_POST['_neuf_events_promo_period'];

	// Update or add post meta
	foreach($tosave as $key=>$value)
		if(!update_post_meta($post_id, $key, $value)) {
			add_post_meta($post_id, $key, $value, true);
		}

	return $post_id;
}

/** Sample table view of events */
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
		$price_regular = get_post_meta( $post->ID, '_neuf_events_price_regular', true);
		$price_member = get_post_meta( $post->ID, '_neuf_events_price_member', true);
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

?>
