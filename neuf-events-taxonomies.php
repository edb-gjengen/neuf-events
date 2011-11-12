<?php
/**
 * Adds a custom taxonomy 'event_type'.
 *
 * We use the taxonomy to differ between different event types, such as concerts, movie screenings and parties. The taxonomy can also be applied to regular posts, so that you can assign terms to a news story. This way, we are able to showcase relevant events alongside our news articles.
 */
function neuf_register_event_taxonomies() {
	$labels = array(
		'name'              => 'Arrangementstype',
		'singular_name'     => 'Arrangementstype',
		'search_items'      => 'S&oslash;k etter arrangementstype',
		'all_items'	    => 'Alle arrangementstyper',
		'parent_item'       => 'Undertype av',
		'parent_item_colon' => 'Undertype av:',
		'edit_item'         => 'Rediger arrangementstype',
		'update_item'       => 'Oppdater arrangementstype',
		'add_new_item'      => 'Legg til ny arrangementstype',
		'new_item_name'     => 'Navn p&aring; ny arrangementstype',
		'menu_name'         => 'Arrangementstyper'
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
			'slug'         => 'arrangementstyper',
			'hierarchical' => true
		)
	) );
}
?>
