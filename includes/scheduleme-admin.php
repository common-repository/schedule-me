<?php

/**
 * Settings Page
 */
class WPSM_Admin
{

	function __construct()
	{
		/**
		 * register our wpsm_add_menu to the admin_init action hook
		 */
		add_action('admin_menu', array($this, 'wpsm_add_menu'));
		/**
		 * register our wpsm_admin_init to the admin_menu action hook
		 */
		add_action('admin_init', array($this, 'wpsm_admin_init'));
		/**
		 * Enqueue only admin scripts
		 */
		add_action('admin_enqueue_scripts', array($this, 'admin_en_scripts'));
		/**
		 * register our wpsm_custom_post_type to the admin_menu action hook
		 */
		add_action('init', array($this, 'wpsm_custom_post_type'));
		/**
		 * register our wpsm_custom_post_type to the admin_menu action hook
		 */
		add_action('save_post', array($this, 'save_details'));
		/**
		 *  ONLY wpsm_scheduleme CUSTOM TYPE POSTS
		 */
		add_filter('manage_wpsm_scheduleme_posts_columns', array($this, 'columns_head_only_wpsm'), 10);
		/**
		 *  ONLY wpsm_scheduleme CUSTOM TYPE POSTS
		 */
		add_action('manage_wpsm_scheduleme_posts_custom_column', array($this, 'columns_content_only_wpsm'), 10, 2);
	}

	/*
	 * Actions perform at loading of admin menu
	 */

	function wpsm_add_menu()
	{
		add_menu_page('Schedule', 'Schedule Me', 'manage_options', 'scheduleme-menu', NULL, plugins_url('../images/wp-scheduleme-logo.png', __FILE__), '61');

		add_submenu_page('scheduleme-menu', 'Schedule Me' . ' Add New', 'Add New', 'manage_options', 'post-new.php?post_type=wpsm_scheduleme', NULL);
		add_submenu_page('scheduleme-menu', 'Schedule Me ' . ' Calender View', 'Calender View', 'manage_options', 'scheduleme-calenderview', array($this, 'wpsm_calender_page_html'));
		add_submenu_page('scheduleme-menu', 'Schedule Me ' . ' Users', 'Users', 'manage_options', 'scheduleme-users', array($this, 'wpsm_users_page_html'));
		add_submenu_page('scheduleme-menu', 'Schedule Me ' . ' Settings', '<b style="color:#f9845b">Settings</b>', 'manage_options', 'scheduleme-settings', array($this, 'wpsm_options_page_html'));
	}

	function wpsm_custom_post_type()
	{
		$labels = array(
			'name' => _x('Schedule Me', 'post type general name', 'wpsm'),
			'singular_name' => _x('Schedule', 'post type singular name', 'wpsm'),
			'menu_name' => _x('Schedules', 'admin menu', 'wpsm'),
			'name_admin_bar' => _x('Schedule', 'add new on admin bar', 'wpsm'),
			'add_new' => _x('Add New', 'team', 'wpsm'),
			'add_new_item' => __('Add New Schedule', 'wpsm'),
			'new_item' => __('New Schedule', 'wpsm'),
			'edit_item' => __('Edit Schedule', 'wpsm'),
			'view_item' => __('View Schedule', 'wpsm'),
			'all_items' => __('All Schedules', 'wpsm'),
			'search_items' => __('Search Schedules', 'wpsm'),
			'parent_item_colon' => __('Parent Schedules:', 'wpsm'),
			'not_found' => __('No Schedule found.', 'wpsm'),
			'not_found_in_trash' => __('No Schedule found in Trash.', 'wpsm')
		);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => "scheduleme-menu", //<--- HERE
			'query_var' => true,
			'rewrite' => array('slug' => 'wpsm_scheduleme'),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array('title')
		);

		register_post_type('wpsm_scheduleme', $args);
	}

	function wpsm_admin_init()
	{


		// register a new setting for "wpsm" page
		register_setting('wpsm', 'wpsm_options');

		// register a new section in the "wpsm" page
		add_settings_section(
		'wpsm_section_developers', __('<hr> Set Email Address', 'wpsm'), array($this, 'wpsm_section_developers_sm'), 'wpsm'
		);

		add_settings_section(
		'wpsm_section_developers_state', __('<hr> Enable/Disable Schedule me button', 'wpsm'), array($this, 'wpsm_section_developers_sm1'), 'wpsm'
		);

		// register a new field in the "wpsm_section_developers" section, inside the "wpsm" page
		add_settings_field(
		'wpsm_field_state', // as of WP 4.6 this value is used only internally
		// use $args' label_for to populate the id inside the callback
		__('State', 'wpsm'), array($this, 'wpsm_field_state_sm'), 'wpsm', 'wpsm_section_developers_state', [
			'label_for' => 'wpsm_field_state',
			'class' => 'wpsm_row',
			'wpsm_custom_data' => 'custom',
		]);
		// register a new field in the "wpsm_section_developers" section, inside the "wpsm" page
		add_settings_field(
		'wpsm_field_email_address', // as of WP 4.6 this value is used only internally
		// use $args' label_for to populate the id inside the callback
		__('Email Address', 'wpsm'), array($this, 'wpsm_field_email_address_sm'), 'wpsm', 'wpsm_section_developers', [
			'label_for' => 'wpsm_field_email_address',
			'class' => 'wpsm_row',
			'wpsm_custom_data' => 'custom',
		]);


		add_meta_box("schedule_details", "Schedule Details", array($this, "schedule_details"), "wpsm_scheduleme", "normal", "low");
		add_meta_box("remarks_meta", "Remarks", array($this, "admin_remarks"), "wpsm_scheduleme", "normal", "low");
	}

	function admin_en_scripts()
	{


		wp_enqueue_style('datetimepicker_custom_css', plugin_dir_url(__FILE__) . '../css/jquery.datetimepicker.min.css');
		wp_enqueue_style('custom_css_admin', plugin_dir_url(__FILE__) . '../css/custom_style_admin.css');

		wp_register_script('datetimepicker_custom', plugin_dir_url(__FILE__) . "../js/jquery.datetimepicker.full.min.js");
		wp_enqueue_script('datetimepicker_custom');

		// Localize the script with new data
		$screen = get_current_screen();

		$all_posts_data = '';
		if ($screen->base == 'schedule-me_page_scheduleme-calenderview' || $screen->id == 'schedule-me_page_scheduleme-calenderview')
		{
			wp_enqueue_style('fullcalender_custom_css', plugin_dir_url(__FILE__) . '../css/fullcalendar.min.css');
			wp_enqueue_style('fullcalender_print_custom_css', plugin_dir_url(__FILE__) . '../css/fullcalendar.print.css', array(), false, 'print');

			wp_register_script('fullcalender_moment_custom', plugin_dir_url(__FILE__) . "../js/moment.min.js");
			wp_enqueue_script('fullcalender_moment_custom');
			wp_register_script('fullcalender_custom', plugin_dir_url(__FILE__) . "../js/fullcalendar.min.js");
			wp_enqueue_script('fullcalender_custom');

			$all_posts_data = $this->get_all_custom_posts();
		}

		wp_register_script('custom_js_admin', plugin_dir_url(__FILE__) . "../js/custom_fn_admin.js", '', '', true);
		wp_localize_script('custom_js_admin', 'all_posts', $all_posts_data);
		wp_enqueue_script('custom_js_admin');
	}

	function get_all_custom_posts($userData = false)
	{
		$query = new WP_Query(array(
			'post_type' => 'wpsm_scheduleme',
			'post_status' => 'publish'
		));
		$posts_data = array();
		$counter = 0;
		while ($query->have_posts())
		{

			$query->the_post();
			$post_id = get_the_ID();
			$post_title = get_the_title();

			$posts_data[$counter]['title'] = $post_title;
			$posts_data[$counter]['pid'] = $post_id;

			$start_time = new DateTime(get_post_meta($post_id, "start_time", true)); //2016-09-09T16:00:00 "2016-11-07/UTC22:00:00"
			$start_time = $start_time->format('Y-m-d H:i:s');

			$end_time = new DateTime(get_post_meta($post_id, "end_time", true)); //2016-09-09T16:00:00
			$end_time = $end_time->format('Y-m-d H:i:s');

			if ($userData)
			{
				$custom_data = get_post_custom($post_id);
				$full_name = $phone = $email_address = "";
				if (isset($custom_data["full_name"][0]))
					$full_name = $custom_data["full_name"][0];

				if (isset($custom_data["phone"][0]))
					$phone = $custom_data["phone"][0];

				if (isset($custom_data["email_address"][0]))
					$email_address = $custom_data["email_address"][0];

				$posts_data[$counter]['email'] = $email_address;
				$posts_data[$counter]['name'] = $full_name;
				$posts_data[$counter]['phone'] = $phone;
			}

			$posts_data[$counter]['start'] = $start_time;
			$posts_data[$counter]['end'] = $end_time;
			$counter ++;
		}
		wp_reset_postdata();
//		wp_reset_query();
//		rewind_posts();

		return $posts_data;
	}

	/**
	 * custom option and settings:
	 * callback functions
	 * developers section cb section callbacks can 
	 * accept an $args parameter, which is an array. 
	 * $args have the following keys defined: title, id, callback.
	 * The values are defined at the add_settings_section() function.

	 */
	function wpsm_section_developers_sm1($args)
	{
		?>
		<p id="<?= esc_attr($args['id']); ?>"><?= esc_html__('Please check to enable the button', 'wpsm'); ?></p>
		<?php
	}

	/**
	 * custom option and settings:
	 * callback functions
	 * developers section cb section callbacks can 
	 * accept an $args parameter, which is an array. 
	 * $args have the following keys defined: title, id, callback.
	 * The values are defined at the add_settings_section() function.

	 */
	function wpsm_section_developers_sm($args)
	{
		?>
		<p id="<?= esc_attr($args['id']); ?>"><?= esc_html__('Please Provide the email Address to receive an email on every schedule', 'wpsm'); ?></p>
		<?php
	}

	/*
	 * field callbacks can accept an $args parameter, which is an array.
	 * $args is defined at the add_settings_field() function. 
	 * wordpress has magic interaction with the following keys: label_for, class.
	 * the "label_for" key value is used for the "for" attribute of the <label>. 
	 * the "class" key value is used for the "class" attribute of the <tr> containing the field. 
	 * you can add custom key value pairs to be used inside your callbacks.
	 */

	function wpsm_field_state_sm($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpsm_options');
		// output the field
		?>
		<input type="checkbox" id="<?= esc_attr($args['label_for']); ?>" 
			   name="wpsm_options[<?= esc_attr($args['label_for']); ?>]" 
			   data-custom="<?= esc_attr($args['wpsm_custom_data']); ?>"
			   <?= isset($options[$args['label_for']]) ? ('checked') : (''); ?>  
			   >&nbsp;&nbsp;<label>Enable/Disable</label>

		<?php
	}

	/*
	 * field callbacks can accept an $args parameter, which is an array.
	 * $args is defined at the add_settings_field() function. 
	 * wordpress has magic interaction with the following keys: label_for, class.
	 * the "label_for" key value is used for the "for" attribute of the <label>. 
	 * the "class" key value is used for the "class" attribute of the <tr> containing the field. 
	 * you can add custom key value pairs to be used inside your callbacks.
	 */

	function wpsm_field_email_address_sm($args)
	{
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpsm_options');
		// output the field
		?>

		<input type="email" id="<?= esc_attr($args['label_for']); ?>" 
			   required="required" name="wpsm_options[<?= esc_attr($args['label_for']); ?>]" 
			   data-custom="<?= esc_attr($args['wpsm_custom_data']); ?>"
			   value="<?= isset($options[$args['label_for']]) ? $options[$args['label_for']] : (''); ?>"  
			   >


		<?php
	}

	/**
	 * 
	 * 
	 */
	function wpsm_users_page_html()
	{

		if ( ! current_user_can('manage_options'))
		{
			return;
		}
		?>
		<div class="wrap">
			<h1><?= esc_html(get_admin_page_title()); ?></h1>
			<div id="wpsm_users_view">
				<table>
					<thead>
					<th>#</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					</thead>
					<tbody>

						<?php
						$all_users_data = $this->get_all_custom_posts(true);
						if ($all_users_data)
						{
							$_unique_users = array();
							foreach ($all_users_data as $v)
							{
								if (isset($_unique_users[$v['email']]))
								{
									// found duplicate
									continue;
								}
								// remember unique item
								$_unique_users[$v['email']] = $v;
							}
							// if you need a zero-based array, otheriwse work with $_data
							$_unique_users = array_values($_unique_users);
							foreach ($_unique_users as $key => $val)
							{
								?>
								<tr>
									<td><?php echo $key; ?></td>
									<td><?php echo $val['name']; ?></td>
									<td><?php echo $val['email']; ?></td>
									<td><?php echo $val['phone']; ?></td>
								</tr>
								<?php
							}
						}
						else
						{
							?>
							<tr><td colspan="4">No User Found.</td></tr>

						<?php } ?>	
					</tbody>

				</table> 
				<hr>
			</div>
		</div>

		<?php
	}

	/**
	 * 
	 * 
	 */
	function wpsm_calender_page_html()
	{

		if ( ! current_user_can('manage_options'))
		{
			return;
		}
		?>
		<div class="wrap">
			<h1><?= esc_html(get_admin_page_title()); ?></h1>
			<div id="wpsm_calendar_view">


			</div>
		</div>

		<?php
	}

	/**
	 * top level menu:
	 * callback functions
	 */
	function wpsm_options_page_html()
	{
		// check user capabilities
		if ( ! current_user_can('manage_options'))
		{
			return;
		}

		// add error/update messages
		// check if the user have submitted the settings
		// wordpress will add the "settings-updated" $_GET parameter to the url
		if (isset($_GET['settings-updated']))
		{
			// add settings saved message with the class of "updated"
			add_settings_error('wpsm_messages', 'wpsm_message', __('Settings Saved', 'wpsm'), 'updated');
		}

		// show error/update messages
		settings_errors('wpsm_messages');
		?>
		<div class="wrap">
			<h1><?= esc_html(get_admin_page_title()); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "wpsm"
				settings_fields('wpsm');
				// output setting sections and their fields
				// (sections are registered for "wpsm", each field is registered to a specific section)
				do_settings_sections('wpsm');
				?><hr>

				<label>Short code for Pages and Post. Please add this short code to any post or any page to show the schedule button.</label> <code>[wpsm_schedule_button]</code>
				<hr>
				<?php
				// output save settings button
				submit_button('Save Settings');
				?>
			</form>
		</div>
		<?php
	}

	function admin_remarks()
	{
		global $post;

		$remarks_meta = get_post_meta($post->ID, "remarks_meta", true);

		$args = array(
			'tinymce' => false,
			'quicktags' => false,
			'media_buttons' => false,
			'editor_height' => 70
		);

		wp_editor($remarks_meta, 'remarks_meta', $args);
	}

	function schedule_details()
	{
		global $post;

		$custom = get_post_custom($post->ID);

		$full_name = $agenda = $start_time = $end_time = $phone = $email_address = '';

		if (isset($custom["agenda"][0]))
			$agenda = $custom["agenda"][0];

		if (isset($custom["full_name"][0]))
			$full_name = $custom["full_name"][0];

		if (isset($custom["start_time"][0]))
			$start_time = $custom["start_time"][0];

		if (isset($custom["end_time"][0]))
			$end_time = $custom["end_time"][0];

		if (isset($custom["phone"][0]))
			$phone = $custom["phone"][0];

		if (isset($custom["email_address"][0]))
			$email_address = $custom["email_address"][0];

		$args = array(
			'tinymce' => false,
			'quicktags' => false,
			'media_buttons' => false,
			'editor_height' => 80
		);
		?>
		<p><label>Meeting Agenda:</label><br />
			<?php wp_editor($agenda, 'agenda', $args); ?></p>
		<p><label>Full Name:</label><br />
			<input name="full_name" id="full_name" value="<?php echo $full_name; ?>" required="required" >
		<p><label>Start Time:</label><br />
			<input name="start_time" id="start_time" value="<?php echo $start_time; ?>" required="required" >
		<p><label>End Time:</label><br />
			<input name="end_time" id="end_time" value="<?php echo $end_time; ?>" required="required" >
		<p><label>Phone:</label><br />
			<input name="phone" value="<?php echo $phone; ?>" required="required" >
		<p><label>Email Address:</label><br />
			<input type="email" name="email_address" value="<?php echo $email_address; ?>" required="required" >


			<script>

				jQuery(document).ready(function(  ) {
					jQuery('#start_time').datetimepicker({
						minDate: new Date(),
						startDate: new Date(),
					});
					jQuery('#end_time').datetimepicker({
						
						minDate: new Date(),
						minTime: new Date().getTime() + 30*60000,

						onShow: function(ct) {
								this.setOptions({
									minDate: jQuery('#start_time').val() ? new Date(jQuery('#start_time').val()) : false,
									startDate: jQuery('#start_time').val() ? new Date(jQuery('#start_time').val()) : false,
									minTime: -1,
								});

						},
					});

				});

			</script>

			<?php
		}

		function save_details()
		{
			global $post;

			if (isset($_POST["remarks_meta"]))
				update_post_meta($post->ID, "remarks_meta", $_POST["remarks_meta"]);
			if (isset($_POST["agenda"]))
				update_post_meta($post->ID, "agenda", $_POST["agenda"]);
			if (isset($_POST["full_name"]))
				update_post_meta($post->ID, "full_name", $_POST["full_name"]);
			if (isset($_POST["start_time"]))
				update_post_meta($post->ID, "start_time", $_POST["start_time"]);
			if (isset($_POST["end_time"]))
				update_post_meta($post->ID, "end_time", $_POST["end_time"]);
			if (isset($_POST["email_address"]))
				update_post_meta($post->ID, "email_address", $_POST["email_address"]);
			if (isset($_POST["phone"]))
				update_post_meta($post->ID, "phone", $_POST["phone"]);
		}

		// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
		function columns_head_only_wpsm($defaults)
		{
			$defaults['user_info'] = 'User Info';
			$defaults['start_time'] = 'Start Time';
			$defaults['end_time'] = 'End Time';

			return $defaults;
		}

		function columns_content_only_wpsm($column_name, $post_ID)
		{
			if ($column_name == 'start_time')
			{
				echo get_post_meta($post_ID, "start_time", true);
			}
			else if ($column_name == 'end_time')
			{
				echo get_post_meta($post_ID, "end_time", true);
			}
			else if ($column_name == 'user_info')
			{
				echo get_post_meta($post_ID, "full_name", true) . "<br>" .
				get_post_meta($post_ID, "email_address", true) . "<br>" .
				get_post_meta($post_ID, "phone", true);
			}
		}

	}

	new WPSM_Admin();
	