$(function(){
	// Datepicker
	$('.datepicker').datetimepicker({
		currentText: 'Nå',
		closeText: 'ferdig',
		hourText: 'Time',
		dateFormat: 'dd.mm.yy',
		timeFormat: 'hh:mm',
		minuteText: 'Minutt',
		timeText: 'Tid',
		firstDay: 1,
		monthNames: ['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember'],
		dayNames: ['søndag','mandag','tirsdag','onsdag','torsdag','fredag','lørdag'],
		dayNamesShort: ['søn','man','tir','ons','tor','fre','lør'],
		dayNamesMin: ['sø','ma','ti','on','to','fr','lø']
	});
});
