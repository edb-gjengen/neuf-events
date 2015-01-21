jQuery(document).ready(function($){
	/* Datetimepicker */
	$('.datepicker').datetimepicker({
		currentText: 'Nå',
		closeText: 'Ok',
		hourText: 'Time',
		dateFormat: 'yy-mm-dd',
		timeFormat: 'HH:mm',
		minuteText: 'Minutt',
		timeText: 'Tid',
		firstDay: 1,
		monthNames: ['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'],
		dayNames: ['søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag'],
		dayNamesShort: ['søn','man','tir','ons','tor','fre','lør'],
		dayNamesMin: ['sø','ma','ti','on','to','fr','lø']
    });

    /* Validation rules */
    $("#post").validate({
        rules: {
            neuf_events_start_time: "required"
        },
        messages: {
            neuf_events_start_time: "Velg startdato og klokkeslett"
        }
    });
});
