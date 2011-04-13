<?php
/*
        Plugin Name: neuf-events rewrite
        Plugin URI: http://www.studentersamfundet.no
        Description: Plugin to manage events for studentersamfundet.no
        Version 0.1
        Author: EDB-web
        Author URI: http://www.studentersamfundet.no
        License: GPL v2 or later
*/
?>

<?php
/** 
        Register the event post type
*/
add_action('init', 'post_type_event');

/**
        Create the fields the post type should have
*/
function post_type_event() {
  register_post_type(
		     'event',
		     array(
			   'labels' => array(
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
					     ),
			   'public'              =>  true,
			   'publicly_queryable'  =>  true,
			   'query_var'           =>  'event',
			   'show_ui'             =>  true,
			   'capability_type'     =>  'post',
			   'supports'            =>  array(
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
?>


<?php
/**
        Add a meta box.
        TODO point and move the function to 'neuf_meta_boxes'
*/

function add_events_metaboxes() {
  // Date-selection for events
  /*
  add_meta_box(
	       'neuf_events_timestamps',
	       __('Starttime'),
	       'neuf_date_custom_box',
	       'event',
	       'side',
	       'high'
	       );
  */
  add_meta_box(
	       'neuf_event_type',
	       __('Eventtype'),
	       'neuf_eventtype_custom_box',
	       'event',
	       'side',
	       'high'
	       );

  add_meta_box(
	       'neuf_eventvenue',
	       __('Venue'),
	       'neuf_eventvenue_custom_box',
	       'event',
	       'side',
	       'high'
	       );

  add_meta_box(
	       'neuf_event_div',
	       __('Eventinfo'),
	       'neuf_event_div_custom_box',
	       'event'
	       );
 
}

?>

<?php

/*******************************************************************************
********************************************************************************
**  Start and endtime metabox   ************************************************
********************************************************************************
*******************************************************************************/
/* TODO JQUERY fra fredrls
function neuf_date_custom_box() {

  $start_date_timestamp = get_post_meta($post->ID, 'event_start_date', true);
  $event_end_timestamp  = get_post_meta($post->ID, 'event_end_time', true);

        echo '<input type="hidden" name="neuf_events_noncename" id="neuf_events_noncename" value="' .
	  wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

        echo '<div id="neuf_time" style="margin-top:8px;">';
        echo '<label for="event_starttime">';
        echo $start_date_timestamp ?
                'N&aring;v&aelig;rende dato: '.
	  date("j. F Y H:i", $start_date_timestamp) :
	  '<span style="color:red;">Ingen dato har blitt satt.</span>' ;
        echo '<br /></label>';

}
*/
?>

<?php

/*******************************************************************************
********************************************************************************
**  Eventtype metabox   ********************************************************
********************************************************************************
*******************************************************************************/

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

?>

<?php

/*******************************************************************************
********************************************************************************
**  Event venue metabox ********************************************************
********************************************************************************
*******************************************************************************/

function neuf_eventvenue_custom_box(){

  global $post;

  $neuf_event_venue = get_post_meta($post->ID, 'neuf_events_venue', true);

  echo 'Sted:';
  echo '<select name="neuf_events_venue">';
  
  $venues = array ("Det Norske Studentersamfund", "Glassbaren", "Storsalen", "Biblioteket", "BokCafeen");
  echo $neuf_event_venue;

  foreach ($venues as $venue) {
    echo '<option value="'.$venue.'"';
    echo '>'.$venue.'</option>';
  }
  echo '</select>';

}

?>

<?php

/*******************************************************************************
********************************************************************************
**  Random info metabox ********************************************************
********************************************************************************
*******************************************************************************/


function neuf_event_div_custom_box(){

  global $post;
  
  $event_price = get_post_meta($post->ID, 'neuf_events_price', true);
  $event_bs = get_post_meta($post->ID, 'neuf_events_bs_url', true);
  $event_fb = get_post_meta($post->ID, 'neuf_events_fb_url', true);

  echo '<h4>Andre detaljer</h4>';
  echo '<br />Pris:<br /><input name="neuf_events_price" value="'.$event_price.'" />';
  echo '<br />Billettservice url:<br /><input name="neuf_events_bs_url" value="'.$event_bs.'" />';
  echo '<br />Facebook url:<br /><input name="neuf_events_fb_url" value="'.$event_fb.'" />';
  echo '<p style="font-style:italic;">(bare la feltene stå tomme om de ikke er relevante)</p>';
  echo '</div> <!-- #neuf_events_time -->';

}

?>


<?php

/**
 *  When the post is saved, saves our custom data
 */ 
add_action('save_post', 'neuf_events_save_info');
function neuf_events_save_info( $post_id ) {

  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  //var_dump($_POST['neuf_event_venue']);
  //	if ( !wp_verify_nonce( $_POST['neuf_events_noncename'], plugin_basename(__FILE__) )) {
  //		return $post_id;
  //	}

  // verify if this is an auto save routine. If it is, our form has not been submitted, and
  // we dont want to do anything

  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )  return $post_id;
  
  // Check permissions
  if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;

  // OK, we're authenticated: we need to find and save the data

  // Get posted data
  
  $events_venue = $_POST['neuf_events_venue'];
  $events_type = $_POST['neuf_events_type'];
  $events_price = $_POST['neuf_events_price'];
  $events_bs_url = $_POST['neuf_events_bs_url'];
  $events_fb_url = $_POST['neuf_events_fb_url'];

  // Convert time data to timestamp
  //$startdate = strtotime( $_POST['neuf_event_end_date'] );
  //$enddate = strtotime( $_POST['neuf_event_end_date'] );

  //$date = strtotime( $year.'-'.$month.'-'.$day.'T'.$hour.':'.$minute );

  $quicksave = array('events_price','events_bs_url','events_fb_url',);
  foreach($quicksave as $save){
    if( !($meta = get_post_meta($post_id, 'neuf_' . $save)))
      add_post_meta($post_id, 'neuf_' . $save, true);
    else if($meta != $$save)
      update_post_meta($post_id, 'neuf_' . $save, $$save);
  }

  // Save venue                                                                                                                                                                                                            
  if ( !get_post_meta($post_id, 'neuf_events_venue') ) {
    add_post_meta($post_id, 'neuf_events_venue', $events_venue, true);
  } elseif ( $events_venue != get_post_meta($post_id, 'neuf_events_venue', true) ) {
    update_post_meta($post_id, 'neuf_events_venue', $events_venue);
  }

  if ( !get_post_meta($post_id, 'neuf_events_type') ) {
    add_post_meta($post_id, 'neuf_events_type', $events_type, true);
  }elseif($events_type != get_post_meta($post_id, 'neuf_events_type', true)){
    update_post_meta($post_id, 'neuf_events_type',$events_type);
  }

  return $post_id;

  // Save timestamp
 
  //  if ( !get_post_meta($post_id, 'neuf_events_start_date') ) 
  //   add_post_meta($post_id, 'neuf_events_start_date', $date, true);
  //  elseif ( $date != get_post_meta($post_id, 'neuf_events_start_date', true) ) 
  //    update_post_meta($post_id, 'neuf_events_start_date', $date);
  //  die(get_post_meta($post_id, 'neuf_events_start_date', true));
 

  //  return $date;
}


?>

<?php

/**
 * Add shortcode which will output the program.
 */

add_shortcode( 'neuf-events-program' , 'neuf_events_program');
function neuf_events_program() {
  global $post, $wp_locale;

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

  //  $start_date = get_post_meta( $post->ID , 'neuf_events_start_date' , true );
  $venue = get_post_meta( $post->ID , 'neuf_events_venue' , true );
  $type = get_post_meta( $post->ID, 'neuf_events_type', true);
  $price = get_post_meta( $post->ID, 'neuf_events_price', true) ;

  //$date_new = date ( 'Y-m-d' , $start_date );
  //			if ( $start_date && $date != $date_new ) {
  //$date = $date_new;
  //				$html .= '    <tr>';
  //				$html .= '        <td colspan="3"><strong>' . ucfirst($wp_locale->get_weekday(date('w',$start_date))) . ' ' . date ('d. F',$start_date) . '</strong></td>';
  //				$html .= '    </tr>';
  //			}
  //
  $html .= '    <tr>';
  $html .= '        <td class="price" style="width:10%;">' . $price . '</td>';
  $html .= '        <td class="title" style="padding-right:10px;"><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>';
  $html .= '        <td class="type" style="font-size:smaller;">' . $type . '</td>';
  $html .= '        <td class="place" style="width:27%;padding-left:10px;">' . $venue . '</td>';
  $html .= '    </tr>';

  endwhile;
  
  $html .= '</table><!-- .event-table -->';
  
  endif;
  
  return $html;
}
?>