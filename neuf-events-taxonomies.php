<?php
/**
 * Adds a custom taxonomy 'event_type'.
 *
 * We use the taxonomy to differ between different event types, such as concerts, movie screenings and parties. The taxonomy can also be applied to regular posts, so that you can assign terms to a news story. This way, we are able to showcase relevant events alongside our news articles.
 */
function neuf_register_event_taxonomies() {
	$labels = array(
		'name'              => __('Event Type', 'neuf_event'), //Arrangementstype
		'singular_name'     => __('Event Type', 'neuf_event'),
		'search_items'      => __('Search Event Types', 'neuf_event'),//'S&oslash;k etter arrangementstype',
		'all_items'	    => __('All Event Types', 'neuf_event'),
		'parent_item'       => __('Subtype of', 'neuf_event'),//'Undertype av',
		'parent_item_colon' => __('Subtype of:', 'neuf_event'),//'Undertype av:',
		'edit_item'         => __('Edit Event Type', 'neuf_event'),
		'update_item'       => __('Update Event Type', 'neuf_event'),
		'add_new_item'      => __('Add New Event Type', 'neuf_event'),
		'new_item_name'     => __('Name', 'neuf_event'),//'Navn p&aring; ny arrangementstype',
		'menu_name'         => __('Event Types', 'neuf_event'),
	);

	register_taxonomy( 'event_type', array(
		'post',
		'event'
	), array(
		'hierarchical' => true,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => array(
			'slug'         => 'eventtypes',
			'hierarchical' => true
		)
	) );
}
?>
