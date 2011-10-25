<?php
/*
  Plugin Name: neuf-events
  Plugin URI: http://www.studentersamfundet.no
  Description: Plugin to manage events for studentersamfundet.no
  Version 0.2.1
  Author: EDB-web
  Author URI: http://www.studentersamfundet.no
  License: GPL v2 or later
 */

/* TODO:
 *  - Pickup stuff from: http://codex.wordpress.org/Post_Types
 */

if (!class_exists("NeufEvents")) {

	class NeufEvents {
		function NeufEvents() {

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
				$tosave['neuf_events_starttime'] = strtotime( $_POST['neuf_events_starttime'] );
				$tosave['neuf_events_endtime'] = strtotime( $_POST['neuf_events_endtime'] );
				$tosave['neuf_events_price'] = $_POST['neuf_events_price'];
				$tosave['neuf_events_bs_url'] = $_POST['neuf_events_bs_url'];
				$tosave['neuf_events_fb_url'] = $_POST['neuf_events_fb_url'];
				$tosave['neuf_events_type'] = $_POST['neuf_events_type'];
				$tosave['neuf_events_venue'] = $_POST['neuf_events_venue'];

				// Update or add post meta
				foreach($tosave as $key=>$value)
					if(!update_post_meta($post_id, $key, $value))
						add_post_meta($post_id, $key, $value, true);

				return $post_id;
			}

			/** View of the custom page */
			function neuf_events_program() {
				global $post, $wp_locale;

				$events = new WP_Query( array(
					'post_type' => 'event',
					'posts_per_page' => -1,
					'meta_key' => 'neuf_events_starttime',
					'orderby' => 'meta_value',
					'order' => 'ASC'
				) );

				if ( $events->have_posts() ) :
					$date = "";
					ob_start();

					echo '<table class="event-table">';

					while ( $events->have_posts() ) : $events->the_post();
						$venue = get_post_meta( $post->ID, 'neuf_events_venue', true);
						$type  = get_post_meta( $post->ID, 'neuf_events_type', true);
						$price = get_post_meta( $post->ID, 'neuf_events_price', true);
						$time = get_post_meta( $post->ID, 'neuf_events_starttime', true);
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

		}
	}
}

if (class_exists("NeufEvents")) {
	$neuf_event_object = new NeufEvents();
}  

if ( isset($neuf_event_object) ) {
	/* Register the event post type */
	add_action('init', 'neuf_events_post_type');
	add_action('save_post', 'neuf_events_save_info');
	add_action('publish_neuf_events', 'neuf_events_publish' );
	add_shortcode('neuf-events-program', 'neuf_events_program');
}

?>
