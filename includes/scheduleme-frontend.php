<?php

/**
 * Settings Page
 */
class WPSM_Frontend
{

	function __construct()
	{
		/**
		 * Enqueue only frontend scripts
		 */
		add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
		add_shortcode('wpsm_schedule_form', array($this, 'cf_shortcode'));
		add_shortcode('wpsm_schedule_button', array($this, 'wpsm_btn_shortcode'));
		add_filter('parse_query', array($this, 'wpsm_exclude_pages_from_admin'));
	}

	function enqueue_frontend_scripts()
	{
		$options = get_option('wpsm_options');
		if (isset($options['wpsm_field_state']))
		{

			$current_page_id = get_queried_object_id();
			$plugin_page_id = get_option('wpsm_plugin_form_page_id');
			$ty_page_id = get_option('wpsm_plugin_ty_page_id');
			$is_plugin_page = false;
			$is_ty_page = false;

			if ($current_page_id == $plugin_page_id)
			{
				wp_register_script('datetimepicker_custom', plugin_dir_url(__FILE__) . "../js/jquery.datetimepicker.full.min.js", '', '', true);
				wp_enqueue_script('datetimepicker_custom');
				wp_enqueue_style('datetimepicker_custom_css', plugin_dir_url(__FILE__) . '../css/jquery.datetimepicker.min.css');
				$is_plugin_page = true;
			}
			else if ($current_page_id == $ty_page_id)
			{

				$is_ty_page = true;
			}

			wp_register_script('custom_js', plugin_dir_url(__FILE__) . "../js/custom_fn.js", '', '', true);

			// Localize the script with new data
			$data_array = array(
				'page_link' => get_page_link(get_option('wpsm_plugin_form_page_id')),
				'home_page_link' => site_url(),
				'is_plugin_page' => $is_plugin_page,
				'is_ty_page' => $is_ty_page
			);

			wp_localize_script('custom_js', 'data', $data_array);
			wp_enqueue_script('custom_js');
			wp_enqueue_style('custom_css', plugin_dir_url(__FILE__) . '../css/custom_style.css');
		}
	}

	function html_form_code()
	{
		echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
		echo '<p>';
		echo 'Meeting Title<span class="wpsm-required">*</span> <br/>';
		echo '<input type="text" name="cf-title" pattern="[a-zA-Z0-9 ]+" value="' . ( isset($_POST["cf-title"]) ? esc_attr($_POST["cf-title"]) : '' ) . '" size="40" required="required"/>';
		echo '</p>';
		echo '<p>';
		echo 'Full Name<span class="wpsm-required">*</span>  <br/>';
		echo '<input type="text" name="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '' ) . '" size="40" required="required"/>';
		echo '</p>';
		echo '<p>';
		echo 'Start Time<span class="wpsm-required">*</span> <br/>';
		echo '<input type="text" name="cf-start_time" id="cf-start_time" value="' . ( isset($_POST["cf-start_time"]) ? esc_attr($_POST["cf-start_time"]) : '' ) . '" required="required"/>';
		echo '</p>';
		echo '<p>';
		echo 'End Time<span class="wpsm-required">*</span> <br/>';
		echo '<input type="text" name="cf-end_time" id="cf-end_time" value="' . ( isset($_POST["cf-end_time"]) ? esc_attr($_POST["cf-end_time"]) : '' ) . '" required="required"/>';
		echo '</p>';
		echo 'Email<span class="wpsm-required">*</span> <br/>';
		echo '<input type="email" name="cf-email" value="' . ( isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '' ) . '" size="40" required="required"/>';
		echo '</p>';
		echo '<p>';
		echo 'Phone<span class="wpsm-required">*</span> <br/>';
		echo '<input type="text" name="cf-phone" value="' . ( isset($_POST["cf-phone"]) ? esc_attr($_POST["cf-phone"]) : '' ) . '" size="40" required="required"/>';
		echo '</p>';
		echo '<p>';
		echo 'Meeting Agenda <br/>';
		echo '<textarea rows="3" cols="35" name="cf-agenda">' . ( isset($_POST["cf-agenda"]) ? esc_attr($_POST["cf-agenda"]) : '' ) . '</textarea>';
		echo '</p>';
		echo '<p><input type="submit" name="cf-submitted-wpsm" value="Submit"></p>';
		echo '</form><input type="hidden" id="wpsm_redirect" value="">';
	}

	function deliver_mail()
	{

		// if the submit button is clicked, send the email
		if (isset($_POST['cf-submitted-wpsm']))
		{

			// sanitize form values
			$title = sanitize_text_field($_POST["cf-title"]);
			$start_time = sanitize_text_field($_POST["cf-start_time"]);
			$end_time = sanitize_text_field($_POST["cf-end_time"]);
			$name = sanitize_text_field($_POST["cf-name"]);
			$email = sanitize_email($_POST["cf-email"]);
			$phone = sanitize_text_field($_POST["cf-phone"]);
			$agenda = esc_textarea($_POST["cf-agenda"]);

			if ( ! empty($title) && ! empty($start_time) && ! empty($end_time) && ! empty($name) && ! empty($email) && ! empty($phone))
			{
				//insert into custom post
				$new_post = array(
					'post_title' => wp_strip_all_tags($title),
					'post_status' => 'publish',
					'post_type' => "wpsm_scheduleme",
				);

				// Insert the post into the database
				$post_id = wp_insert_post($new_post);

				update_post_meta($post_id, "agenda", $agenda);
				update_post_meta($post_id, "full_name", $name);
				update_post_meta($post_id, "start_time", $start_time);
				update_post_meta($post_id, "end_time", $end_time);
				update_post_meta($post_id, "email_address", $email);
				update_post_meta($post_id, "phone", $phone);

				// get the blog administrator's email address
				$options = get_option('wpsm_options');


//				$headers = "From: Scheduler <$to>" . "\r\n";
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$message_admin = "Schedule Request by " . $email . " <br/>"
				. "<br/>Contact Information<br/><br/>"
				. "<br/>Name : " . $name
				. "<br/>Phone : " . $phone
				. "<br/>Email  : " . $email
				. "<br/>Start Time : " . $start_time
				. "<br/>End Time : " . $end_time;

				$message_user = "We have received your meeting request. We will get back to you as soon as possible.";

				if ($options['wpsm_field_email_address'] != '')
				{
					wp_mail($options['wpsm_field_email_address'], " Schedule Request: " . $title, $message_admin, $headers);
				}
				wp_mail($email, "Schedule Request Received", $message_user, $headers);

				// If email has been process for sending, display a success message
//				if (wp_mail($to, "Schedule Request: " . $title, $message_admin) && wp_mail($email, "Schedule Request Received", $message_user))
//				{
				echo "<input type='hidden' id='wpsm_redirect' value='" . get_page_link(get_option('wpsm_plugin_ty_page_id')) . "'>";
//					wp_redirect(get_page_link(get_option('wpsm_plugin_ty_page_id')));
//					die();
//				}
//				else
//				{
//					echo "<input type='hidden' id='wpsm_redirect' value='".get_page_link(get_option('wpsm_plugin_ty_page_id'))."'>";
////					die();
//				}
			}
			else
			{
				echo '<div>';
				echo '<p>Please fill in all the required fields.</p>';
				echo '</div>';
			}
		}
	}

	function cf_shortcode()
	{
		ob_start();
		$this->deliver_mail();
		$this->html_form_code();

		return ob_get_clean();
	}

	function wpsm_btn_shortcode()
	{
		echo "<div id='wpsm_container_widget'><a class='wpsm-btn_widget' href='" . get_page_link(get_option('wpsm_plugin_form_page_id')) . "'>Schedule a Call<span class='dashicons dashicons-phone wpsm-ico'></span></a></div>";
	}

	function wpsm_exclude_pages_from_admin($query)
	{

		if ( ! is_admin())
			return $query;

		global $pagenow, $post_type, $wpdb;


		if ('edit.php' == $pagenow && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ))
		{
			$the_formpage_id = get_option('wpsm_plugin_form_page_id');
			$the_typage_id = get_option('wpsm_plugin_ty_page_id');

			if ($the_formpage_id || $the_typage_id)
			{
				$query->query_vars['post__not_in'] = array("$the_formpage_id", "$the_typage_id"); // Enter your page IDs here
			}
		}
	}

}

new WPSM_Frontend();
