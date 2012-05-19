<?php
/**
 * Adds a custom taxonomy 'event_type'.
 *
 * We use the taxonomy to differ between different event types, such as concerts, movie screenings and parties. The taxonomy can also be applied to regular posts, so that you can assign terms to a news story. This way, we are able to showcase relevant events alongside our news articles.
 */
function neuf_events_register_taxonomies() {
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

/**
 * Extend post_class() to include event type classes.
 *
 * post_class() is called to output semantic classes to post elements. In this function, we add a class for each event type associated with the post.
 *
 * In the neuf-old-style theme, consider using neuf_post_class() instead. (Defined in the theme's functions.php.)
 *
 * This function was originally written to be able to display different icons on different types of events.
 */
function neuf_post_class_event_type( $classes ) {
	global $post;

	if ( is_object_in_taxonomy( $post->post_type, 'event_type' ) ) {
		foreach ( (array) get_the_terms ( $post->ID , 'event_type' ) as $event_type ) {
			if ( empty( $event_type->slug ) )
				continue;
			$classes[] = 'event-type-' . sanitize_html_class( $event_type->slug, $event_type->term_id);
		}
	}

	return $classes;
}
add_filter( 'post_class', 'neuf_post_class_event_type' );

/* Default terms for custom taxonomies */
function neuf_events_set_default_object_terms( $post_id, $post ) {
    /* Make sure it's an event */
    if ( !isset($_POST['post_type']) || $_POST['post_type'] !== 'event' ) {
        return;
    }
    /* Taxonomy => array of default terms */
    $defaults = array(
        'event_type' => array( 'annet' ),
        );
    $taxonomies = get_object_taxonomies( $_POST['post_type'] );
    foreach ( (array) $taxonomies as $taxonomy ) {
        $terms = wp_get_post_terms( $post_id, $taxonomy );
        /* No tax terms assoc with post? */
        if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
            /* ... then add defaults */
            wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
        }
    }
}
add_action( 'save_post', 'neuf_events_set_default_object_terms', 100, 2 );
?>
