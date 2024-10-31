jQuery(document).ready(function(  ) {
	if (data.is_plugin_page) {
		jQuery('#cf-start_time').datetimepicker({
			minDate: new Date(),
			startDate: new Date(),
		});
		jQuery('#cf-end_time').datetimepicker({
			minDate: new Date(),
			minTime: new Date().getTime() + 30 * 60000,
			onShow: function(ct) {
				this.setOptions({
					minDate: jQuery('#cf-start_time').val() ? new Date(jQuery('#cf-start_time').val()) : false,
					startDate: jQuery('#cf-start_time').val() ? new Date(jQuery('#cf-start_time').val()) : false,
					minTime: -1,
				});

			},
		});
		if (jQuery('#wpsm_redirect').val() != '') {
			window.location = jQuery('#wpsm_redirect').val();
		}
	} else if (data.is_ty_page) {

		setTimeout(function() {
			window.location = data.home_page_link;
		}, 4000);


	} else {
		setTimeout(function() {

			jQuery("body").append("<div id='wpsm_container'><a class='wpsm-btn' href=''>Schedule a Call<span class='dashicons dashicons-phone wpsm-ico'></span></a></div>")
			jQuery("#wpsm_container .wpsm-btn").attr('href', data.page_link);
			jQuery("#wpsm_container").fadeIn('slow');


		}, 3000);
	}

});