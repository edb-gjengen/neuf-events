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

/* TODO (nikolark):
 *  - Pickup stuff from: http://codex.wordpress.org/Post_Types
 */
require_once( 'neuf-events-post-types.php' );
require_once( 'neuf-events-admin.php' );
require_once( 'neuf-events-taxonomies.php' );

/* Register the translations */
add_action( 'init' , 'neuf_events_i18n' , 0 );
/* Register the event post type */
add_action( 'init' , 'neuf_events_post_type' , 0 );
add_action( 'save_post' , 'neuf_events_save_info' );
add_action( 'publish_neuf_events' , 'neuf_events_publish' );

/* Register taxonomies */
add_action( 'init' , 'neuf_register_event_taxonomies', 1 );

/* Register shortcode for program. */
add_shortcode( 'neuf-events-program' , 'neuf_events_program' );

function neuf_events_i18n() {
	load_plugin_textdomain( 'neuf_event', false, dirname( plugin_basename( __FILE__ ) ) .'/languages' );
}

?>
