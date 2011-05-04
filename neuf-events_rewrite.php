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

if (!class_exists("NeufEvents")) {

  class NeufEvents{

    function NeufEvents(){

      /**
	 Create the fields the post type should have
      */
      function neuf_events_post_type() {
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

       
      /*******************************************************************************
      ********************************************************************************
      **  Add meta-boxes   ***********************************************************
      ********************************************************************************
      *******************************************************************************/

      function add_events_metaboxes() {
	// Date-selection for events

        wp_register_style('timecss', plugins_url("/neuf-events/style/jquery-ui-1.8.11.custom.css", dirname(__FILE__)));
        wp_enqueue_style('timecss');
        wp_register_script('jqmin', plugins_url("/neuf-events/script/jquery-1.5.1.min.js", dirname(__FILE__)));
        wp_enqueue_script('jqmin');
        wp_register_script('jquicust', plugins_url("/neuf-events/script/jquery-ui-1.8.11.custom.min.js", dirname(__FILE__)));
        wp_enqueue_script('jquicust');
        wp_register_script('timepicker', plugins_url("/neuf-events/script/jquery-ui-timepicker-addon.js", dirname(__FILE__)));
        wp_enqueue_script('timepicker');
        wp_register_script('timedefs', plugins_url("/neuf-events/script/timepickdef.js", dirname(__FILE__)));
        wp_enqueue_script('timedefs');
	
	  add_meta_box(
	  'neuf_events_timestamps',
	  __('Starttime'),
	  'neuf_date_custom_box',
	  'event',
	  'side',
	  'high'
	  );
	
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
		     'side'
		     );

	add_meta_box(
		     'neuf_event_div',
		     __('Eventinfo'),
		     'neuf_event_div_custom_box',
		     'event'
		     );

      }


      /*******************************************************************************
      ********************************************************************************
      **  Start and endtime metabox   ************************************************
      ********************************************************************************
      *******************************************************************************/

     function neuf_date_custom_box() {
     
        global $post;
 
	    $start = get_post_meta($post->ID, 'neuf_events_starttime', true);
	    $end  = get_post_meta($post->ID, 'neuf_events_endtime', true);

        echo '<input type="text" class="datepicker" name="neuf_events_starttime" value="'.date("d.m.Y H:i", $start).'" /><br />';
        echo $start ?
	    'N&aring;v&aelig;rende dato: '.date("d.m.Y H:i", $start) :
	    '<span style="color:red;">Ingen dato har blitt satt.</span><br />' ;

        echo '<input type="text" class="datepicker" name="neuf_events_endtime" value="'.date("d.m.Y H:i", $end).'" /><br />';

	 }
      


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
	  if($venue == $neuf_event_venue)
	    echo ' selected="selected"';
	  echo '>'.$venue.'</option>';
	}
	echo '</select>';

      }


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


      /**
       *  When the post is saved, saves our custom data
       */ 


      function neuf_events_save_info( $post_id ) {

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	//	if ( !wp_verify_nonce( $_POST['neuf_events_noncename'], plugin_basename(__FILE__) )) {
	//		return $post_id;
	//	}

	// verify if this is an auto save routine. If it is, our form has not been submitted, and
	// we dont want to do anything

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )  return $post_id;

	// Check permissions
	if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;

	// Get posted data
	$events_venue = $_POST['neuf_events_venue'];
	$events_type = $_POST['neuf_events_type'];
	$events_price = $_POST['neuf_events_price'];
	$events_bs_url = $_POST['neuf_events_bs_url'];
	$events_fb_url = $_POST['neuf_events_fb_url'];

	// Convert time data to timestamp
	$startdate = strtotime( $_POST['neuf_events_starttime'] );
	$enddate = strtotime( $_POST['neuf_events_endtime'] );

	$quicksave = array('price','bs_url','fb_url');
	foreach($quicksave as $save){
	  if( !($meta = get_post_meta($post_id, 'neuf_events_' . $save)))
	    add_post_meta($post_id, 'neuf_events_' . $save, true);
	  else if($meta != $$save)
	    update_post_meta($post_id, 'neuf_events_' . $save, $$save);
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

	// Save timestamps

    if ( !get_post_meta($post_id, 'neuf_events_starttime') ) 
	    add_post_meta($post_id, 'neuf_events_starttime', $startdate, true);
	elseif ( $startdate != get_post_meta($post_id, 'neuf_events_starttime', true) ) 
	    update_post_meta($post_id, 'neuf_events_starttime', $startdate);

    if ( !get_post_meta($post_id, 'neuf_events_endtime') ) 
	    add_post_meta($post_id, 'neuf_events_endtime', $enddate, true);
	elseif ( $enddate != get_post_meta($post_id, 'neuf_events_endtime', true) ) 
	    update_post_meta($post_id, 'neuf_events_endtime', $enddate);


    return $neuf_events_starttime;
    }


      /** View of the custom page */


      function neuf_events_program() {
	global $post, $wp_locale;

	$events = new WP_Query( array(
				      'post_type' => 'event',
				      'posts_per_page' => -1,
				      'meta_key' => 'neuf_events_start_date',
				      'orderby' => 'meta_value',
				      'order' => 'ASC'
				      ) );
	$html = '';

	if ( $events->have_posts() ) :
	  $date = "";

	$html .= '<table class="event-table">';

	while ( $events->have_posts() ) :
	  $events->the_post();
	
	//  $start_date = get_post_meta( $post->ID , 'neuf_events_start_date' , true );
	$venue = get_post_meta( $post->ID, 'neuf_events_venue',  true );
	$type  = get_post_meta( $post->ID, 'neuf_events_type',   true);
	$price = get_post_meta( $post->ID, 'neuf_events_price',  true) ;

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
    }
  }
}

if (class_exists("NeufEvents")) {
  $neuf_event_object = new NeufEvents();
}  

if ( isset($neuf_event_object)){

  /** 
      Register the event post type
  */
  add_action(       'init',                 'neuf_events_post_type');
  add_action(       'save_post',            'neuf_events_save_info');
  add_shortcode(    'neuf-events-program',  'neuf_events_program');

}

?>
