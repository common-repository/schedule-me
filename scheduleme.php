<?php

/**
 * Plugin Name: Schedule Me
 * Plugin URI: https://xoho.tech/services/
 * Description: This plugin Provides functionality on each page to schedule a meeting and manage the schedules.
 * Version: 1.0.2
 * Author: Xoho Tech
 * Author URI: https://xoho.tech/
 * Text Domain: wpsm
 * License: GPL2
 */
/**
 * Copyright (c) 2016 Xoho Tech (email: info@xoho.tech). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
// don't call the file directly
if ( ! defined('ABSPATH'))
	exit;

class WP_Schedule_Simple
{

	// Constructor
	function __construct()
	{

		$this->wpsm_file_includes();
		register_activation_hook(__FILE__, array($this, 'wpsm_install'));
		register_deactivation_hook(__FILE__, array($this, 'wpsm_uninstall'));
	}

	function wpsm_file_includes()
	{
		include( dirname(__FILE__) . '/includes/scheduleme-admin.php' );
		include( dirname(__FILE__) . '/includes/scheduleme-frontend.php' );
		include( dirname(__FILE__) . '/includes/scheduleme-widget.php' );
	}

	function createPage($slug = 'form', $the_page_title = " Schedule me", $content = "")
	{
		global $wpdb;

//		$the_page_title = 'Schedule a meeting with us.';
		$the_page_name = 'wpsm_schedule_' . $slug;

		// the menu entry...
		delete_option("wpsm_plugin_" . $slug . "_page_title");
		add_option("wpsm_plugin_" . $slug . "_page_title", $the_page_title, '', 'yes');
		// the slug...
		delete_option("wpsm_plugin_" . $slug . "_page_name");
		add_option("wpsm_plugin_" . $slug . "_page_name", $the_page_name, '', 'yes');
		// the id...
		delete_option("wpsm_plugin_" . $slug . "_page_id");
		add_option("wpsm_plugin_" . $slug . "_page_id", '0', '', 'yes');

		$the_page = get_page_by_title($the_page_title);

		if ( ! $the_page)
		{

			// Create post object
			$_p = array();
			$_p['post_title'] = $the_page_title;
			$_p['post_content'] = "$content";
			$_p['post_status'] = 'publish';
			$_p['post_type'] = 'page';
			$_p['comment_status'] = 'closed';
			$_p['ping_status'] = 'closed';
			$_p['post_category'] = array(1); // the default 'Uncatrgorised'
			// Insert the post into the database
			$the_page_id = wp_insert_post($_p);
		}
		else
		{
			// the plugin may have been previously active and the page may just be trashed...

			$the_page_id = $the_page->ID;

			//make sure the page is not trashed...
			$the_page->post_status = 'publish';
			$the_page_id = wp_update_post($the_page);
		}

		delete_option("wpsm_plugin_" . $slug . "_page_id");
		add_option("wpsm_plugin_" . $slug . "_page_id", $the_page_id);
	}

	function deletePage($slug = 'form')
	{
		global $wpdb;

		$the_page_title = get_option("wpsm_plugin_" . $slug . "_page_title");
		$the_page_name = get_option("wpsm_plugin_" . $slug . "_page_name");

		//  the id of our page...
		$the_page_id = get_option("wpsm_plugin_" . $slug . "_page_id");
		if ($the_page_id)
		{
			wp_delete_post($the_page_id); // this will trash, not delete
		}

		delete_option("wpsm_plugin_" . $slug . "_page_title");
		delete_option("wpsm_plugin_" . $slug . "_page_name");
		delete_option("wpsm_plugin_" . $slug . "_page_id");
	}

	/*
	 * Actions perform on activation of plugin
	 */

	function wpsm_install()
	{

		$this->createPage('form','Schedule a meeting with us.','[wpsm_schedule_form]');
		$this->createPage('ty','Thank you','We have received your meeting request. We will get back to you as soon as we can.');

		
	}

	/*
	 * Actions perform on de-activation of plugin
	 */

	function wpsm_uninstall()
	{
		$this->deletePage('form');
		$this->deletePage('ty');
	}

}

new WP_Schedule_Simple();
