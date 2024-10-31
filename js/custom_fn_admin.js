jQuery(document).ready(function(  ) {
	if (all_posts != '') {
		jQuery('#wpsm_calendar_view').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'listDay,listWeek,month'
			},
			views: {
				listDay: {buttonText: 'list day'},
				listWeek: {buttonText: 'list week'}
			},
			defaultDate: new Date(),
			navLinks: true, // can click day/week names to navigate views
			editable: false,
			eventLimit: true, // allow "more" link when too many events
			events: all_posts
		});
	}
});
