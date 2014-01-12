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
    protected function is_root_event_type($term) {
        return $term->parent == 0;

    }
    protected function get_parent_root($term) {
        if( $this->is_root_event_type($term) ) {
            return $term;
        }
        $parent_term = null;
        foreach($this->event_types as $type) {
            if($type->term_id == $term->parent) {
                $parent_term = $type;
                break;
            }
        }
        return $this->get_parent_root($parent_term);
    }
    protected function add_parent_root_event_types($events) {
        // taxonomy tree
        $this->event_types = get_terms(array('event_type'));
        foreach ($events as $event) {
            $event_types = array();
            foreach ( $event->taxonomy_event_type as $event_type ) {
                $event_type = $this->get_parent_root($event_type);
                /* Can be either JSON_API_Category or stdClass */
                $name = get_class($event_type) === 'stdClass' ? $event_type->name : $event_type->title;
                $event_types[] = $name;
            }
            $event->event_type_parents = $event_types;
        }

        return $events;
    }

}
?>
