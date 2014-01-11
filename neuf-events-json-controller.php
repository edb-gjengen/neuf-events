<?php

/*
 * Custom events controller providing handy methods.
 */
class JSON_API_Events_Controller {

    /* /?json=events.get_upcoming */
    public function get_upcoming() {
        global $json_api;
        $meta_query = array(
            'key'     => '_neuf_events_starttime',
            'value'   => date( 'U' , strtotime( '-8 hours' ) ), 
            'compare' => '>',
            'type'    => 'numeric'
        );

        $query = array(
            'post_type'      => 'event',
            'meta_query'     => array( $meta_query ),
            'posts_per_page' => 300,
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_neuf_events_starttime',
            'order'          => 'ASC'
        );
        $custom_fields = array(
            '_neuf_events_price_regular',
            '_neuf_events_price_member',
            '_neuf_events_starttime',
            '_neuf_events_endtime',
            '_neuf_events_venue',
            '_neuf_events_bs_url',
            '_neuf_events_fb_url',
        );
        $json_api->query->custom_fields = implode(',', $custom_fields);
        $events = $json_api->introspector->get_posts($query);
        $events = $this->add_parent_root_event_types($events);
        return $this->events_result($events);
    }
    protected function events_result($events) {
        global $wp_query;
        return array(
            'count' => count($events),
            'count_total' => (int) $wp_query->found_posts,
            'pages' => $wp_query->max_num_pages,
            'events' => $events
        );
    }
    protected function add_parent_root_event_types($events) {
        foreach ($events as $event) {
            $event_types = array();
            $event_array = get_the_terms( $event->id , 'event_type');
            foreach ( $event_array as $event_type ) {
                while ( $event_type->parent !== 0) {
                    $id = (int)$event_type->parent;
                    $event_type = get_term( $id, 'event_type' );
                } 

                $event_types[] = $event_type->name;
            }
            $event->event_type_parents = $event_types;
        }

        return $events;
    }

}
?>
