var $j = jQuery.noConflict();

$j(function(){
    $j("#post").validate({
        rules: {
            neuf_events_start_time: "required"
        },
        messages: {
            neuf_events_start_time: "Velg startdato og klokkeslett"
        }
    });
});
