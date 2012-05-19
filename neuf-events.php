<?php
/*
  Plugin Name: neuf-events
  Plugin URI: http://www.studentersamfundet.no
  Description: Events custom post type
  Version: 0.3
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
add_action( 'save_post' , 'neuf_events_save_post', 100, 2);
add_action( 'publish_neuf_events' , 'neuf_events_publish' );

/* Register taxonomies */
add_action( 'init' , 'neuf_events_register_taxonomies', 1 );

/* Register shortcode for sample table view of program. */
add_shortcode( 'neuf-events-program' , 'neuf_events_program' );

function neuf_events_i18n() {
	load_plugin_textdomain( 'neuf_event', false, dirname( plugin_basename( __FILE__ ) ) .'/languages' );
}
/* JSON API controller */
function add_events_controller($controllers) {
  $controllers[] = 'events';
  return $controllers;
}
add_filter('json_api_controllers', 'add_events_controller');

function set_events_controller_path() {
  return WP_PLUGIN_DIR."/".dirname( plugin_basename( __FILE__ ))."/neuf-events-json-controller.php";
}
add_filter('json_api_events_controller_path', 'set_events_controller_path');


?>
