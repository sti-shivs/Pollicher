<?php

class Shivs_Poll_Admin extends Shivs_Poll_Plugin {

	protected function init() {
		$this->add_action( 'init', 'admin_loader' );
		$this->add_action( 'admin_enqueue_scripts', 'my_shivs_poll_button' );
		$this->add_action( 'wpmu_new_blog', 'new_blog', 10, 6 );
		$this->add_action( 'delete_blog', 'delete_blog', 10, 2 );
		register_activation_hook( $this->_config->plugin_file, array( $this, 'shivs_poll_activate' ) );
		register_deactivation_hook( $this->_config->plugin_file, array( $this, 'shivs_poll_deactivate' ) );
		register_uninstall_hook( $this->_config->plugin_file, 'shivs_poll_uninstall' );
		$this->add_action( 'admin_enqueue_scripts', 'load_editor_functions' );
		$this->add_action( 'plugins_loaded', 'db_update' );
	}

	function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		global $wpdb;

		if ( !function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active_for_network( 'shivs-poll/shivs_poll.php' ) ) {

			$old_blog = $wpdb->blogid;

			switch_to_blog( $blog_id );

			$wpdb->shivs_polls                    = $wpdb->prefix . 'shivs_polls';
			$wpdb->shivs_poll_answers             = $wpdb->prefix . 'shivs_poll_answers';
			$wpdb->shivs_poll_templates           = $wpdb->prefix . 'shivs_poll_templates';
			$wpdb->shivs_poll_custom_fields       = $wpdb->prefix . 'shivs_poll_custom_fields';
			$wpdb->shivs_pollmeta                 = $wpdb->prefix . 'shivs_pollmeta';
			$wpdb->shivs_poll_answermeta          = $wpdb->prefix . 'shivs_poll_answermeta';
			$wpdb->shivs_poll_logs                = $wpdb->prefix . 'shivs_poll_logs';
			$wpdb->shivs_poll_voters              = $wpdb->prefix . 'shivs_poll_voters';
			$wpdb->shivs_poll_bans                = $wpdb->prefix . 'shivs_poll_bans';
			$wpdb->shivs_poll_votes_custom_fields = $wpdb->prefix . 'shivs_poll_votes_custom_fields';
			$wpdb->shivs_poll_facebook_users      = $wpdb->prefix . 'shivs_poll_facebook_users';
			$this->activate( NULL );
			switch_to_blog( $old_blog );
		}
	}

	function delete_blog( $blog_id ) {
		
		global $wpdb;

		$old_blog = $wpdb->blogid;

		switch_to_blog( $blog_id );

		$wpdb->query( "DROP TABLE `" . $wpdb->prefix . "shivs_pollmeta`, `" . $wpdb->prefix . "shivs_polls`, `" . $wpdb->prefix . "shivs_poll_answermeta`, `" . $wpdb->prefix . "shivs_poll_answers`, `" . $wpdb->prefix . "shivs_poll_custom_fields`, `" . $wpdb->prefix . "shivs_poll_logs`, `" . $wpdb->prefix . "shivs_poll_voters`, `" . $wpdb->prefix . "shivs_poll_bans`, `" . $wpdb->prefix . "shivs_poll_templates`, `" . $wpdb->prefix . "shivs_poll_votes_custom_fields`, `" . $wpdb->prefix . "shivs_poll_facebook_users`" );
		
		switch_to_blog( $old_blog );
	}

	function shivs_poll_network_propagate( $pfunction, $networkwide ) {
		
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			// run the activation function for each blog id
			if ( $networkwide ) {

				$old_blog = $wpdb->blogid;

				// get all blog ids
				$blogids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

				foreach ( $blogids as $blog_id ) {

					switch_to_blog( $blog_id );

					$wpdb->shivs_polls                    = $wpdb->prefix . 'shivs_polls';
					$wpdb->shivs_poll_answers             = $wpdb->prefix . 'shivs_poll_answers';
					$wpdb->shivs_poll_templates           = $wpdb->prefix . 'shivs_poll_templates';
					$wpdb->shivs_poll_custom_fields       = $wpdb->prefix . 'shivs_poll_custom_fields';
					$wpdb->shivs_pollmeta                 = $wpdb->prefix . 'shivs_pollmeta';
					$wpdb->shivs_poll_answermeta          = $wpdb->prefix . 'shivs_poll_answermeta';
					$wpdb->shivs_poll_logs                = $wpdb->prefix . 'shivs_poll_logs';
					$wpdb->shivs_poll_voters              = $wpdb->prefix . 'shivs_poll_voters';
					$wpdb->shivs_poll_bans                = $wpdb->prefix . 'shivs_poll_bans';
					$wpdb->shivs_poll_votes_custom_fields = $wpdb->prefix . 'shivs_poll_votes_custom_fields';
					$wpdb->shivs_poll_facebook_users      = $wpdb->prefix . 'shivs_poll_facebook_users';

					call_user_func( array( $this, $pfunction ), $networkwide );
				}

				switch_to_blog( $old_blog );

				return;
			}
		}
		call_user_func( array( $this, $pfunction ), $networkwide );
	}

	function shivs_poll_activate( $networkwide ) {
		$this->shivs_poll_network_propagate( 'activate', $networkwide );
	}

	function shivs_poll_deactivate( $networkwide ) {
		$this->shivs_poll_network_propagate( 'deactivate', $networkwide );
	}

	public function db_update() {

		global $wpdb;
		global $current_user;

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$installed_version = get_option( "shivs_poll_version" );

		// update for version 1.5
		if ( version_compare( $installed_version, '1.5', '<=' ) ){
			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['vote_button_label'] ) ){
				$default_options ['vote_button_label'] = 'Vote';
			}
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
			update_option( 'shivs_poll_options', $default_options );
		}

		// update for version 1.6
		if ( version_compare( $installed_version, '1.6', '<=' ) ){
			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['display_other_answers_values'] ) ){
				$default_options ['display_other_answers_values'] = 'no';
			}
			if ( !isset ( $default_options ['percentages_decimals'] ) ){
				$default_options ['percentages_decimals'] = '0';
			}
			if ( !isset ( $default_options ['plural_answer_result_votes_number_label'] ) ){
				$default_options ['singular_answer_result_votes_number_label'] = 'vote';
			}
			if ( !isset ( $default_options ['plural_answer_result_votes_number_label'] ) ){
				$default_options ['plural_answer_result_votes_number_label'] = 'votes';
			}
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
			update_option( 'shivs_poll_options', $default_options );
		}

		// update for version 2.0
		if ( version_compare( $installed_version, '2.0', '<=' ) ){
			$wpdb->query( 'ALTER TABLE `' . $wpdb->shivs_polls . '` CHANGE `total_votes` `total_answers` INT( 11 ) NOT NULL ' );
			$wpdb->query( 'ALTER TABLE `' . $wpdb->shivs_polls . '` CHANGE `total_voters` `total_votes` INT( 11 ) NOT NULL ' );
			$wpdb->query( "
				UPDATE " . $wpdb->shivs_poll_templates . "
				SET
				before_vote_template = REPLACE( before_vote_template, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS'),
				after_vote_template = REPLACE( after_vote_template, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS'),
				before_start_date_template = REPLACE( before_start_date_template, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS'),
				after_end_date_template = REPLACE( after_end_date_template, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS'),
				css = REPLACE( css, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS'),
				js = REPLACE( js, 'POLL-TOTAL-VOTERS', 'POLL-TOTAL-ANSWERS')
			" );
			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['view_total_answers'] ) ){
				$default_options ['view_total_answers'] = $default_options ['view_total_voters'];
			}
			if ( !isset ( $default_options ['view_total_answers_label'] ) ){
				$default_options ['view_total_answers_label'] = 'Total Answers %POLL-TOTAL-ANSWERS%';
			}
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
			update_option( 'shivs_poll_options', $default_options );
		}

		// update for version 3.2
		if ( version_compare( $installed_version, '3.2', '<=' ) ){
			$wpdb->query( "
				UPDATE " . $wpdb->shivs_poll_templates . "
				SET
				js = REPLACE( js, 'findWidest = false ) {\r\n', 'findWidest ) {\r\n findWidest  = typeof findWidest  !== \'undefined\' ? findWidest  : false;\r\n    ')
			" );

			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['auto_generate_poll_page'] ) ){
				$default_options ['auto_generate_poll_page'] = 'no';
			}
			if ( !isset ( $default_options ['has_auto_generate_poll_page'] ) ){
				$default_options ['has_auto_generate_poll_page'] = 'no';
			}
			update_option( 'shivs_poll_options', $default_options );

			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		// update for version 3.3
		if ( version_compare( $installed_version, '3.3', '<=' ) ){
			$shivs_poll_first_install_date = get_option( 'shivs_poll_first_install_date' );
			if ( !$shivs_poll_first_install_date ){
				$oldest_shivs_poll = Shivs_Poll_Model::get_oldest_poll_from_database();
				update_option( "shivs_poll_first_install_date", $oldest_shivs_poll ['date_added'] );
			}
			$shivs_poll_admin_notices_donate = get_option( 'shivs_poll_admin_notices_donate' );
			if ( !$shivs_poll_admin_notices_donate ){
				update_option( "shivs_poll_admin_notices_donate", 'yes' );
			}
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		// update for version 3.7
		if ( version_compare( $installed_version, '3.7', '<=' ) ){
			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['poll_name_html_tags'] ) ){
				$default_options ['poll_name_html_tags'] = 'no';
			}
			if ( !isset ( $default_options ['poll_question_html_tags'] ) ){
				$default_options ['poll_question_html_tags'] = 'no';
			}
			if ( !isset ( $default_options ['poll_answer_html_tags'] ) ){
				$default_options ['poll_answer_html_tags'] = 'no';
			}
			if ( !isset ( $default_options ['poll_custom_field_html_tags'] ) ){
				$default_options ['poll_custom_field_html_tags'] = 'no';
			}
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
			update_option( 'shivs_poll_options', $default_options );
		}

		if ( version_compare( $installed_version, '3.9', '<=' ) ){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( SHIVS_POLL_INC . '/' . 'db_schema.php' );
			Shivs_Poll_DbSchema::create_polls_table();
			Shivs_Poll_DbSchema::create_polls_templates_table();

			wp_get_current_user();
			if ( $current_user->ID > 0 ){
				$wpdb->query( "UPDATE " . $wpdb->shivs_polls . " SET poll_author = " . $current_user->ID . " WHERE poll_author = 0" );
				$wpdb->query( "UPDATE " . $wpdb->shivs_poll_templates . " SET template_author = " . $current_user->ID . " WHERE template_author = 0" );
			}

			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['use_default_loading_image'] ) ){
				$default_options ['use_default_loading_image'] = 'yes';
			}
			if ( !isset ( $default_options ['loading_image_url'] ) ){
				$default_options ['loading_image_url'] = '';
			}
			update_option( 'shivs_poll_options', $default_options );

			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.1', '<=' ) ){

			$this->update_to_4_2();
			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['use_captcha'] ) ){
				$default_options ['use_captcha'] = 'no';
			}

			update_option( 'shivs_poll_options', $default_options );
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.2', '<=' ) ){

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( SHIVS_POLL_INC . '/' . 'db_schema.php' );
			Shivs_Poll_DbSchema::create_poll_facebook_users_table();
			Shivs_Poll_DbSchema::create_poll_logs_table();
			Shivs_Poll_DbSchema::create_poll_votes_custom_fields_table();

			$opt_box_modal_options = get_option( 'shivs_poll_opt_box_modal_options' );
			if ( !isset ( $opt_box_modal_options ['show'] ) ){
				$opt_box_modal_options ['show'] = 'yes';
			}
			if ( !isset ( $opt_box_modal_options ['last_show_date'] ) ){
				$opt_box_modal_options ['last_show_date'] = Shivs_Poll_Model::get_mysql_curent_date();
			}
			if ( !isset ( $opt_box_modal_options ['modal_had_submit'] ) ){
				$opt_box_modal_options ['modal_had_submit'] = 'no';
			}
			if ( !isset ( $opt_box_modal_options ['sidebar_had_submit'] ) ){
				$opt_box_modal_options ['sidebar_had_submit'] = 'no';
			}
			update_option( 'shivs_poll_opt_box_modal_options', $opt_box_modal_options );

			$pro_options = get_option( 'shivs_poll_pro_options' );
			if ( !isset ( $pro_options ['pro_key'] ) ){
				$pro_options ['pro_key'] = '';
			}
			if ( !isset ( $pro_options ['pro_api_key'] ) ){
				$pro_options ['pro_api_key'] = '';
			}
			if ( !isset ( $pro_options ['pro_api_server_url'] ) ){
				$pro_options ['pro_api_server_url'] = 'http://www.shivs-poll.com/pro';
			}
			if ( !isset ( $pro_options ['pro_user'] ) ){
				$pro_options ['pro_user'] = 'no';
			}

			$default_options = get_option( 'shivs_poll_options' );
			if ( !isset ( $default_options ['vote_permisions_facebook'] ) ){
				$default_options ['vote_permisions_facebook'] = 'no';
			}
			if ( !isset ( $default_options ['vote_permisions_facebook_label'] ) ){
				$default_options ['vote_permisions_facebook_label'] = 'Vote as Facebook User';
			}
			if ( !isset ( $default_options ['vote_permisions_wordpress'] ) ){
				$default_options ['vote_permisions_wordpress'] = 'no';
			}
			if ( !isset ( $default_options ['vote_permisions_wordpress_label'] ) ){
				$default_options ['vote_permisions_wordpress_label'] = 'Vote as WordPress User';
			}
			if ( !isset ( $default_options ['vote_permisions_anonymous'] ) ){
				$default_options ['vote_permisions_anonymous'] = 'no';
			}
			if ( !isset ( $default_options ['vote_permisions_anonymous_label'] ) ){
				$default_options ['vote_permisions_anonymous_label'] = 'Vote as Anonymous User';
			}

			if ( !isset ( $default_options ['share_after_vote'] ) ){
				$default_options ['share_after_vote'] = 'no';
			}
			if ( !isset ( $default_options ['share_picture'] ) ){
				$default_options ['share_picture'] = '';
			}
			if ( !isset ( $default_options ['share_name'] ) ){
				$default_options ['share_name'] = '';
			}
			if ( !isset ( $default_options ['share_caption'] ) ){
				$default_options ['share_caption'] = '';
			}
			if ( !isset ( $default_options ['share_description'] ) ){
				$default_options ['share_description'] = '';
			}
			if ( !isset ( $default_options ['redirect_after_vote'] ) ){
				$default_options ['redirect_after_vote'] = 'no';
			}
			if ( !isset ( $default_options ['redirect_after_vote_url'] ) ){
				$default_options ['redirect_after_vote_url'] = '';
			}
			if ( !isset ( $default_options ['allow_multiple_answers_min_number'] ) ){
				$default_options ['allow_multiple_answers_min_number'] = '1';
			}
			if ( !isset ( $default_options ['is_default_answer'] ) ){
				$default_options ['is_default_answer'] = 'no';
			}
			if ( !isset ( $default_options ['template_width'] ) ){
				$default_options ['template_width'] = '200px';
			}
			if ( !isset ( $default_options ['widget_template'] ) ){
				$default_options ['widget_template'] = $default_options['template'];
			}
			if ( !isset ( $default_options ['widget_template_width'] ) ){
				$default_options ['widget_template_width'] = '200px';
			}

			$wpdb->query( "
				UPDATE " . $wpdb->shivs_poll_templates . "
				SET
				css = REPLACE( css, 'width:200px;', 'width:%POLL-WIDTH%;')
			" );

			update_option( 'shivs_poll_options', $default_options );

			update_option( 'shivs_poll_pro_options', $pro_options );

			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.3', '<=' ) ){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( SHIVS_POLL_INC . '/' . 'db_schema.php' );
			Shivs_Poll_DbSchema::create_poll_voters_table();

			$wpdb->query( "
				UPDATE " . $wpdb->shivs_poll_templates . "
				SET
				css = CONCAT( css, '\r\n\r\n#shivs-poll-container-success-%POLL-ID% {\r\n	font-size:12px;\r\n	font-style:italic;\r\n	color:green;\r\n}' )
				WHERE
				css NOT LIKE '%#shivs-poll-container-success-%'
			" );

			$default_options = get_option( 'shivs_poll_options' );

			if ( !isset ( $default_options ['limit_number_of_votes_per_user'] ) ){
				$default_options ['limit_number_of_votes_per_user'] = 'no';
			}

			if ( !isset ( $default_options ['number_of_votes_per_user'] ) ){
				$default_options ['number_of_votes_per_user'] = 1;
			}

			if ( !isset ( $default_options ['message_after_vote'] ) ){
				$default_options ['message_after_vote'] = 'Thank you for your vote!';
			}

			if ( !isset ( $default_options ['start_scheduler'] ) ){
				$default_options ['start_scheduler'] = 'no';
			}
			if ( !isset ( $default_options ['schedule_reset_poll_stats'] ) ){
				$default_options ['schedule_reset_poll_stats'] = 'no';
			}
			if ( !isset ( $default_options ['schedule_reset_poll_date'] ) ){
				$default_options ['schedule_reset_poll_date'] = current_time( 'timestamp' );
			}
			if ( !isset ( $default_options ['schedule_reset_poll_recurring_value'] ) ){
				$default_options ['schedule_reset_poll_recurring_value'] = '9999';
			}
			if ( !isset ( $default_options ['schedule_reset_poll_recurring_unit'] ) ){
				$default_options ['schedule_reset_poll_recurring_unit'] = 'DAY';
			}

			update_option( 'shivs_poll_options', $default_options );
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.4', '<=' ) ){
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( SHIVS_POLL_INC . '/' . 'db_schema.php' );
			Shivs_Poll_DbSchema::create_poll_logs_table();
			Shivs_Poll_DbSchema::create_poll_votes_custom_fields_table();

			$default_options = get_option( 'shivs_poll_options' );

			if ( !isset ( $default_options ['view_results_permissions'] ) ){
				$default_options ['view_results_permissions'] = 'guest-registered';
			}

			if ( !isset ( $default_options ['date_format'] ) ){
				$default_options ['date_format'] = 'd/m/Y H:i:s';
			}

			if ( !isset ( $default_options ['add_other_answers_to_default_answers'] ) ){
				$default_options ['add_other_answers_to_default_answers'] = 'no';
			}

			update_option( 'shivs_poll_options', $default_options );
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.5', '<=' ) ){

			$default_options = get_option( 'shivs_poll_options' );

			if ( !isset ( $default_options ['send_email_notifications'] ) ){
				$default_options ['send_email_notifications'] = 'no';
			}

			if ( !isset ( $default_options ['email_notifications_from_name'] ) ){
				$default_options ['email_notifications_from_name'] = 'Shivs Poll';
			}

			$sitename = strtolower( $_SERVER['SERVER_NAME'] );
			if ( substr( $sitename, 0, 4 ) == 'www.' ){
				$sitename = substr( $sitename, 4 );
			}

			if ( !isset ( $default_options ['email_notifications_from_email'] ) ){
				$default_options ['email_notifications_from_email'] = 'shivs-poll@' . $sitename;
			}

			if ( !isset ( $default_options ['email_notifications_recipients'] ) ){
				$default_options ['email_notifications_recipients'] = '';
			}

			if ( !isset ( $default_options ['email_notifications_subject'] ) ){
				$default_options ['email_notifications_subject'] = 'New Vote';
			}

			if ( !isset ( $default_options ['email_notifications_body'] ) ){
				$default_options ['email_notifications_body'] = '<p>A new vote was registered on [VOTE_DATE] for [POLL_NAME]</p>
				<p>Vote Details:</p>
				<p><b>Question:</b> [QUESTION]</p>
				<p><b>Answers:</b> <br />[ANSWERS]</p>
				<p><b>Custom Fields:</b> <br />[CUSTOM_FIELDS]</p>
				<p><b>Vote ID:</b> <br />[VOTE_ID]</p>';
			}

			update_option( 'shivs_poll_options', $default_options );
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.7', '<=' ) ){
			$this->update_to_4_8();
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.8', '<=' ) ){
			$this->update_to_4_9();
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}

		if ( version_compare( $installed_version, '4.9', '<=' ) ){
			$this->update_to_4_9_1();
			update_option( "shivs_poll_version", $wpdb->shivs_poll_version );
		}
	}

	public function update_to_4_2() {
		global $wpdb;

		$saved_templates = $wpdb->get_results( "
			SELECT id FROM  " . $wpdb->shivs_poll_templates . "
			WHERE
			before_vote_template LIKE '%<div id=\\" . '"' . "shivs-poll-vote-\%POLL-ID\%\\" . '"' . " class=\\" . '"' . "shivs-poll-footer\\" . '"' . ">%' AND
			before_vote_template NOT LIKE '%[CAPTCHA_CONTAINER]%'
			", ARRAY_A );

		$updated_templates = $wpdb->get_results( "
			SELECT id FROM  " . $wpdb->shivs_poll_templates . "
			WHERE
			before_vote_template LIKE '%<div id=\\\\\\\\" . '"' . "shivs-poll-vote-\%POLL-ID\%\\\\\\\\" . '"' . " class=\\\\\\\\" . '"' . "shivs-poll-footer\\\\\\\\" . '"' . ">%'  AND
			before_vote_template NOT LIKE '%[CAPTCHA_CONTAINER]%'
			", ARRAY_A );
		if ( count( $saved_templates ) > 0 ){
			foreach ( $saved_templates as $template ) {
				$wpdb->query( "
					UPDATE " . $wpdb->shivs_poll_templates . " SET
					before_vote_template =	REPLACE( before_vote_template, '<div id=\\" . '"' . "shivs-poll-vote-%POLL-ID%\\" . '"' . " class=\\" . '"' . "shivs-poll-footer\\" . '"' . ">', '[CAPTCHA_CONTAINER]\r\n<div id=\"shivs-poll-captcha-%POLL-ID%\">\r\n    <div class=\"shivs-poll-captcha-image-div\" id=\"shivs-poll-captcha-image-div-%POLL-ID%\">\r\n        %CAPTCHA-IMAGE%\r\n        <div class=\"shivs-poll-captcha-helpers-div\" id=\"shivs-poll-captcha-helpers-div-%POLL-ID%\">%RELOAD-CAPTCHA-IMAGE% </div>\r\n        <div class=\"shivs_poll_clear\"></div>\r\n    </div>\r\n    %CAPTCHA-LABEL%\r\n    <div class=\"shivs-poll-captcha-input-div\" id=\"shivs-poll-captcha-input-div-%POLL-ID%\">%CAPTCHA-INPUT%</div>\r\n</div>\r\n[/CAPTCHA_CONTAINER]\r\n<div id=\\" . '"' . "shivs-poll-vote-%POLL-ID%\\" . '"' . " class=\\" . '"' . "shivs-poll-footer\\" . '"' . ">')
					WHERE
					id = " . $template ['id'] . " AND
					before_vote_template NOT LIKE '%[CAPTCHA_CONTAINER]%'
				" );
			}
		}
		if ( count( $updated_templates ) > 0 ){
			foreach ( $updated_templates as $template ) {
				$wpdb->query( "
					UPDATE " . $wpdb->shivs_poll_templates . " SET
					before_vote_template =	REPLACE( before_vote_template, '<div id=\\\\" . '"' . "shivs-poll-vote-%POLL-ID%\\\\" . '"' . " class=\\\\" . '"' . "shivs-poll-footer\\\\" . '"' . ">', '[CAPTCHA_CONTAINER]\r\n<div id=\"shivs-poll-captcha-%POLL-ID%\">\r\n    <div class=\"shivs-poll-captcha-image-div\" id=\"shivs-poll-captcha-image-div-%POLL-ID%\">\r\n        %CAPTCHA-IMAGE%\r\n        <div class=\"shivs-poll-captcha-helpers-div\" id=\"shivs-poll-captcha-helpers-div-%POLL-ID%\">%RELOAD-CAPTCHA-IMAGE% </div>\r\n        <div class=\"shivs_poll_clear\"></div>\r\n    </div>\r\n    %CAPTCHA-LABEL%\r\n    <div class=\"shivs-poll-captcha-input-div\" id=\"shivs-poll-captcha-input-div-%POLL-ID%\">%CAPTCHA-INPUT%</div>\r\n</div>\r\n[/CAPTCHA_CONTAINER]\r\n<div id=\\" . '"' . "shivs-poll-vote-%POLL-ID%\\" . '"' . " class=\\" . '"' . "shivs-poll-footer\\" . '"' . ">')
					WHERE
					id = " . $template ['id'] . " AND
					before_vote_template NOT LIKE '%[CAPTCHA_CONTAINER]%'
				" );
			}
		}

		$css_templates = $wpdb->get_results( "
			SELECT id FROM  " . $wpdb->shivs_poll_templates . "
			WHERE
			css LIKE '%#shivs-poll-custom-%POLL-ID% ul li input { margin:0px 0px 5px 0px; padding:2\%; width:96\%; text-indent:2\%; font-size:12px; }%' AND
			css NOT LIKE '%shivs-poll-captcha%'
			", ARRAY_A );

		if ( count( $css_templates ) > 0 ){
			foreach ( $css_templates as $template ) {
				$wpdb->query( "
					UPDATE " . $wpdb->shivs_poll_templates . " SET
					css =	REPLACE( css, '#shivs-poll-custom-%POLL-ID% ul li input { margin:0px 0px 5px 0px; padding:2%; width:96%; text-indent:2%; font-size:12px; }', '#shivs-poll-container-%POLL-ID% input[type=\'text\'] { margin:0px 0px 5px 0px; padding:2%; width:96%; text-indent:2%; font-size:12px; }\r\n\r\n#shivs-poll-captcha-input-div-%POLL-ID% {\r\nmargin-top:5px;\r\n}\r\n#shivs-poll-captcha-helpers-div-%POLL-ID% {\r\nwidth:30px;\r\nfloat:left;\r\nmargin-left:5px;\r\nheight:0px;\r\n}\r\n\r\n#shivs-poll-captcha-helpers-div-%POLL-ID% img {\r\nmargin-bottom:2px;\r\n}\r\n\r\n#shivs-poll-captcha-image-div-%POLL-ID% {\r\nmargin-bottom:5px;\r\n}\r\n\r\n#shivs_poll_captcha_image_%POLL-ID% {\r\nfloat:left;\r\n}\r\n\r\n.shivs_poll_clear {\r\nclear:both;\r\n}\r\n\r\n')
					WHERE
					id = " . $template ['id'] . " AND
					css NOT LIKE '%shivs-poll-captcha%'
				" );
			}
		}

		$css_templates_1 = $wpdb->get_results( "
			SELECT id FROM  " . $wpdb->shivs_poll_templates . "
			WHERE
			css LIKE '%#shivs-poll-custom-%POLL-ID% ul li input { margin:0px 0px 5px 0px; padding:2\%; width:95\%; text-indent:2\%; font-size:12px; }%' AND
			css NOT LIKE '%shivs-poll-captcha%'
			", ARRAY_A );

		if ( count( $css_templates_1 ) > 0 ){
			foreach ( $css_templates_1 as $template ) {
				$wpdb->query( "
					UPDATE " . $wpdb->shivs_poll_templates . " SET
					css =	REPLACE( css, '#shivs-poll-custom-%POLL-ID% ul li input { margin:0px 0px 5px 0px; padding:2%; width:95%; text-indent:2%; font-size:12px; }', '#shivs-poll-container-%POLL-ID% input[type=\'text\'] { margin:0px 0px 5px 0px; padding:2%; width:96%; text-indent:2%; font-size:12px; }\r\n\r\n#shivs-poll-captcha-input-div-%POLL-ID% {\r\nmargin-top:5px;\r\n}\r\n#shivs-poll-captcha-helpers-div-%POLL-ID% {\r\nwidth:30px;\r\nfloat:left;\r\nmargin-left:5px;\r\nheight:0px;\r\n}\r\n\r\n#shivs-poll-captcha-helpers-div-%POLL-ID% img {\r\nmargin-bottom:2px;\r\n}\r\n\r\n#shivs-poll-captcha-image-div-%POLL-ID% {\r\nmargin-bottom:5px;\r\n}\r\n\r\n#shivs_poll_captcha_image_%POLL-ID% {\r\nfloat:left;\r\n}\r\n\r\n.shivs_poll_clear {\r\nclear:both;\r\n}\r\n\r\n')
					WHERE
					id = " . $template ['id'] . " AND
					css NOT LIKE '%shivs-poll-captcha%'
				" );
			}
		}
	}

	public function update_to_4_8() {
		global $wpdb;
		$sql               = <<<EOT
	UPDATE $wpdb->shivs_poll_templates
	SET
	js = CONCAT( js, '
		jQuery(document).ready(function(){
			runOnPollStateChange_%POLL-ID%();
			});

		function runOnPollStateChange_%POLL-ID%() {};'
		)
	WHERE js NOT LIKE '%runOnPollStateChange_%'
EOT;
		$updated_templates = $wpdb->query( $sql );
	}

	public function update_to_4_9() {
		global $wpdb;
		$sql = <<<EOT
				UPDATE $wpdb->shivs_poll_templates
				SET `before_vote_template` =
				REPLACE( `before_vote_template` ,
				'<div id="shivs-poll-answers-%POLL-ID"',
				'<div id="shivs-poll-answers-%POLL-ID%"' )
				WHERE `before_vote_template` LIKE '%<div id="shivs-poll-answers-%POLL-ID"%'
EOT;
		$wpdb->query( $sql );
	}

	public function update_to_4_9_1() {
		global $wpdb;
		$sql = <<<EOT
				UPDATE $wpdb->shivs_poll_templates
				SET `before_vote_template` =
				REPLACE( `before_vote_template` ,
				'<div id="shivs-poll-answers-%POLL-ID"',
				'<div id="shivs-poll-answers-%POLL-ID%"' )
				WHERE `before_vote_template` LIKE '%<div id="shivs-poll-answers-\%POLL-ID"%'
EOT;
		$wpdb->query( $sql );

		$sql = <<<EOT
				UPDATE $wpdb->shivs_poll_templates
				SET `before_vote_template` =
				REPLACE( `before_vote_template` ,
				'<li class="shivs-poll-li-answer-%POLL-ID"',
				'<li class="shivs-poll-li-answer-%POLL-ID%"' )
				WHERE `before_vote_template` LIKE '%<li class="shivs-poll-li-answer-\%POLL-ID"%'
EOT;
		$wpdb->query( $sql );
	}

	public function admin_loader() {
		$this->add_action( 'admin_init', 'shivs_poll_options_admin_init', 1 );
		$this->add_action( 'admin_menu', 'admin_menu', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_editor', 'ajax_get_polls_for_editor', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_preview_template', 'ajax_preview_template', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_html_editor', 'ajax_get_polls_for_html_editor', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_edit_add_new_poll', 'ajax_edit_add_new_poll', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_edit_add_new_poll_template', 'ajax_edit_add_new_poll_template', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_reset_poll_template', 'ajax_reset_poll_template', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_opt_box_modal', 'ajax_show_opt_box_modal', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_modal_option_signup', 'ajax_modal_option_signup', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_sidebar_option_signup', 'ajax_sidebar_option_signup', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_change_votes_number_answer', 'ajax_show_change_votes_number_answer', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_do_change_votes_number_answer', 'ajax_do_change_votes_number_answer', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_change_total_number_poll', 'ajax_show_change_total_number_poll', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_do_change_total_number_poll', 'ajax_do_change_total_number_poll', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_change_poll_author', 'ajax_show_change_poll_author', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_do_change_poll_author', 'ajax_do_change_poll_author', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_change_template_author', 'ajax_show_change_template_author', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_do_change_template_author', 'ajax_do_change_template_author', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_do_vote', 'shivs_poll_do_vote', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_do_vote', 'shivs_poll_do_vote', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_view_results', 'shivs_poll_view_results', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_view_results', 'shivs_poll_view_results', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_back_to_vote', 'shivs_poll_back_to_vote', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_back_to_vote', 'shivs_poll_back_to_vote', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_load_css', 'shivs_poll_load_css', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_load_css', 'shivs_poll_load_css', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_load_js', 'shivs_poll_load_js', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_load_js', 'shivs_poll_load_js', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_show_captcha', 'ajax_show_captcha', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_show_captcha', 'ajax_show_captcha', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_play_captcha', 'ajax_play_captcha', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_play_captcha', 'ajax_play_captcha', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_is_wordpress_user', 'ajax_is_wordpress_user', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_is_wordpress_user', 'ajax_is_wordpress_user', 1 );
		$this->add_action( 'wp_ajax_shivs_poll_set_wordpress_vote', 'ajax_set_wordpress_vote', 1 );
		$this->add_action( 'wp_ajax_nopriv_shivs_poll_set_wordpress_vote', 'ajax_set_wordpress_vote', 1 );
	}

	public function activate( $networkwide ) {

		global $wp_version;

		if ( !version_compare( $wp_version, SHIVS_POLL_WP_VERSION, '>=' ) ){
			$error = new WP_Error ( 'WordPress_version_error', sprintf( __( 'You need at least WordPress version %s to use this plugin', 'shivs_poll' ), SHIVS_POLL_WP_VERSION ), __( 'Error: WordPress Version Problem', 'shivs_poll' ) );

			if ( isset ( $error ) && is_wp_error( $error ) && current_user_can( 'manage_options' ) ){
				wp_die( $error->get_error_message(), $error->get_error_data() );
			}
		}
		else {
			if ( !extension_loaded( 'json' ) ){
				$error = new WP_Error ( 'WordPress_json_error', __( 'You need the  json php extension for this plugin', 'shivs_poll' ), __( 'Error: WordPress Extension Problem', 'shivs_poll' ) );

				if ( isset ( $error ) && is_wp_error( $error ) && current_user_can( 'manage_options' ) ){
					wp_die( $error->get_error_message(), $error->get_error_data() );
				}
			}

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			require_once( SHIVS_POLL_INC . '/' . 'db_schema.php' );

			Shivs_Poll_DbSchema::create_poll_database_tables();
		}
	}

	public function deactivate( $networkwide ) {
		
		$poll_archive_page = get_page_by_path( 'shivs-poll-archive', ARRAY_A );

		if ( $poll_archive_page ){
			$poll_archive_page_id = $poll_archive_page ['ID'];
			wp_delete_post( $poll_archive_page_id, true );
		}

		$schedule_timestamp = wp_next_scheduled( 'shivs_poll_hourly_event', array() );

		if ( $schedule_timestamp ){
			wp_unschedule_event( $schedule_timestamp, 'shivs_poll_hourly_event', array() );
		}
	}

	public function admin_menu() {
		if ( is_admin() && $this->current_user_can( 'edit_own_polls' ) ) {
			if ( function_exists( 'add_menu_page' ) ){
				if ( $this->current_user_can( 'edit_own_polls' ) ){
					$page = add_menu_page( __( 'Pollicher', 'shivs_poll' ), __( 'Polls', 'shivs_poll' ), 'read', 'shivs-polls', array( $this, 'manage_pages' ), "dashicons-megaphone", '28.734' );
				}
			}
			if ( $this->current_user_can( 'edit_own_polls' ) ) {
				add_action( "load-$page", array( &$this, 'manage_pages_load' ) );
			}
			if ( function_exists( 'add_submenu_page' ) ){

				if ( $this->current_user_can( 'edit_own_polls' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'All Polls', 'shivs_poll' ), __( 'All Polls', 'shivs_poll' ), 'read', 'shivs-polls', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'edit_own_polls' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Add New', 'shivs_poll' ), __( 'Add New', 'shivs_poll' ), 'read', 'shivs-polls-add-new', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'manage_polls_options' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Options', 'shivs_poll' ), __( 'Options', 'shivs_poll' ), 'read', 'shivs-polls-options', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'edit_own_polls_templates' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Templates', 'shivs_poll' ), __( 'Templates', 'shivs_poll' ), 'read', 'shivs-polls-templates', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'view_own_polls_logs' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Logs', 'shivs_poll' ), __( 'Logs', 'shivs_poll' ), 'read', 'shivs-polls-logs', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'manage_polls_bans' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Bans', 'shivs_poll' ), __( 'Bans', 'shivs_poll' ), 'read', 'shivs-polls-bans', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
				if ( $this->current_user_can( 'become_pro' ) ) {
					$subpage = add_submenu_page( 'shivs-polls', __( 'Become Pro', 'shivs_poll' ), __( 'Become Pro', 'shivs_poll' ), 'read', 'shivs-polls-become-pro', array( &$this, 'manage_pages' ) );
					add_action( "load-$subpage", array( &$this, 'manage_pages_load' ) );
				}
			}
		}
	}

	public function manage_pages() {

		global $page, $action;

		switch ( $page ) {
			case 'shivs-polls' :
				if ( 'custom-fields' == $action ){
					$this->view_poll_custom_fields();
				break;
				}
				if ( 'results' == $action ){
					$this->view_poll_results();
				break;
				}
				else if ( 'edit' == $action ) {
				}
				else {
					$this->view_all_polls();
					break;
				}
			case 'shivs-polls-add-new' :
				$this->view_add_edit_new_poll();
			break;
			case 'shivs-polls-options' :
				$this->view_shivs_poll_options();
			break;
			case 'shivs-polls-logs' :
				$this->view_shivs_poll_logs();
			break;
			case 'shivs-polls-bans' :
				$this->view_shivs_poll_bans();
			break;
			case 'shivs-polls-become-pro' :
				$this->view_shivs_poll_become_pro();
			break;
			case 'shivs-polls-templates' :
				if ( 'add-new' == $action || 'edit' == $action ){
					$this->view_add_edit_poll_template();
				}
				else {
					$this->view_shivs_poll_templates();
				}
			break;
			default :
				$this->view_all_polls();
		}
	}

	public function manage_pages_load() {

		wp_reset_vars( array( 'page', 'action', 'orderby', 'order' ) );

		global $page, $action, $orderby, $order, $shivs_poll_add_new_config;

		$default_options = get_option( 'shivs_poll_options', array() );

		wp_enqueue_style( 'shivs-poll-admin', "{$this->_config->plugin_url}/css/shivs-poll-admin.css", array(), $this->_config->version );
		
		$answers_number      = $this->_config->min_number_of_answers + 1; // total +1
		
		$customfields_number = $this->_config->min_number_of_customfields + 1; // total +1

		wp_enqueue_script( 'shivs-poll-admin', "{$this->_config->plugin_url}/js/shivs-poll-admin.js", array( 'jquery' ), $this->_config->version, true );
		
		$doScroll = 0;
		
		switch ( $page ) {
			case 'shivs-polls' :
				if ( 'results' == $action ){
					wp_enqueue_style( 'shivs-poll-admin-results', "{$this->_config->plugin_url}/css/shivs-poll-admin-results.css", array(), $this->_config->version );
					wp_enqueue_style( 'shivs-poll-timepicker', "{$this->_config->plugin_url}/css/timepicker.css", array(), $this->_config->version );
					wp_enqueue_style( 'shivs-poll-jquery-ui', "{$this->_config->plugin_url}/css/jquery-ui.css", array(), $this->_config->version );
					wp_enqueue_script( 'shivs-poll-jquery-ui-timepicker', "{$this->_config->plugin_url}/js/jquery-ui-timepicker-addon.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), $this->_config->version, true );
					wp_enqueue_style( 'shivs-poll-admin-custom-fields', "{$this->_config->plugin_url}/css/shivs-poll-admin-custom-fields.css", array(), $this->_config->version );
					wp_enqueue_script( 'shivs-poll-admin-custom-fields', "{$this->_config->plugin_url}/js/shivs-poll-admin-custom-fields.js", array( 'jquery', 'shivs-poll-jquery-ui-timepicker' ), $this->_config->version, true );
					$this->shivs_poll_custom_fields_results_operations();
				break;
				}
				if ( 'custom-fields' == $action ){
					wp_enqueue_style( 'shivs-poll-timepicker', "{$this->_config->plugin_url}/css/timepicker.css", array(), $this->_config->version );
					wp_enqueue_style( 'shivs-poll-jquery-ui', "{$this->_config->plugin_url}/css/jquery-ui.css", array(), $this->_config->version );
					wp_enqueue_script( 'shivs-poll-jquery-ui-timepicker', "{$this->_config->plugin_url}/js/jquery-ui-timepicker-addon.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), $this->_config->version, true );
					wp_enqueue_style( 'shivs-poll-admin-custom-fields', "{$this->_config->plugin_url}/css/shivs-poll-admin-custom-fields.css", array(), $this->_config->version );
					wp_enqueue_script( 'shivs-poll-admin-custom-fields', "{$this->_config->plugin_url}/js/shivs-poll-admin-custom-fields.js", array( 'jquery', 'shivs-poll-jquery-ui-timepicker' ), $this->_config->version, true );
					$this->shivs_poll_custom_fields_operations();
				break;
				}
				else if ( 'edit' == $action ) {
					$doScroll = 1;
					require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
					$poll_id             = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
					$shivs_poll_model      = new Shivs_Poll_Model ( $poll_id );
					$answers             = Shivs_Poll_Model::get_poll_answers( $poll_id );
					$answers_number      = count( $answers ) + 1; // total +1
					$custom_fields       = Shivs_Poll_Model::get_poll_customfields( $poll_id );
					$customfields_number = count( $custom_fields ) + 1; // total +1
				}
				else {
					$this->view_all_polls_operations();
					wp_enqueue_script( 'link' );
					wp_enqueue_script( 'xfn' );
					wp_enqueue_script( 'shivs-poll-opt-form', "http://app.getresponse.com/view_webform.js?wid=394041&mg_param1=1", NULL, $this->_config->version, true );
				break;
				}

			case 'shivs-polls-add-new' :
				$doScroll                = 1;
				$shivs_poll_add_new_config = array( 'ajax' => array( 'url' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ), 'action' => 'shivs_poll_edit_add_new_poll', 'beforeSendMessage' => __( 'Please wait a moment while we process your request...', 'shivs_poll' ), 'errorMessage' => __( 'An error has occured...', 'shivs_poll' ) ), 'text_answer' => __( 'Answer', 'shivs_poll' ), 'text_customfield' => __( 'Custom Text Field', 'shivs_poll' ), 'text_requiered_customfield' => __( 'Required', 'shivs_poll' ), 'text_remove_answer' => __( 'Remove', 'shivs_poll' ), 'text_remove_customfield' => __( 'Remove', 'shivs_poll' ), 'text_customize_answer' => __( 'More Options', 'shivs_poll' ), 'text_change_votes_number_answer' => __( 'Change Number Of Votes', 'shivs_poll' ), 'text_change_votes_number_poll' => __( 'Change Number Of Total Votes', 'shivs_poll' ), 'text_change_answers_number_poll' => __( 'Change Number Of Total Answers', 'shivs_poll' ), 'plugin_url' => $this->_config->plugin_url, 'default_number_of_answers' => $answers_number, 'default_number_of_customfields' => $customfields_number, 'text_is_default_answer' => __( 'Make this the default answer', 'shivs_poll' ) . '<br><font size="0">(' . __( 'if "yes", answer will be autoselected when poll is displayed', 'shivs_poll' ) . ')</font>', 'text_poll_bar_style' => array( 'use_template_bar_label' => __( 'Use Template Result Bar', 'shivs_poll' ), 'use_template_bar_yes_label' => __( 'Yes', 'shivs_poll' ), 'use_template_bar_no_label' => __( 'No', 'shivs_poll' ), 'poll_bar_style_label' => __( 'Shivs Poll Bar Style', 'shivs_poll' ), 'poll_bar_preview_label' => __( 'Shivs Poll Bar Preview', 'shivs_poll' ), 'poll_bar_style_background_label' => __( 'Background Color', 'shivs_poll' ), 'poll_bar_style_height_label' => __( 'Height', 'shivs_poll' ), 'poll_bar_style_border_color_label' => __( 'Border Color', 'shivs_poll' ), 'poll_bar_style_border_width_label' => __( 'Border Width', 'shivs_poll' ), 'poll_bar_style_border_style_label' => __( 'Border Style', 'shivs_poll' ) ), 'poll_bar_default_options' => array( 'use_template_bar' => isset ( $default_options ['use_template_bar'] ) ? $default_options ['use_template_bar'] : 'yes', 'height' => isset ( $default_options ['bar_height'] ) ? $default_options ['bar_height'] : 10, 'background_color' => isset ( $default_options ['bar_background'] ) ? $default_options ['bar_background'] : 'd8e1eb', 'border' => isset ( $default_options ['bar_border_style'] ) ? $default_options ['bar_border_style'] : 'solid', 'border_width' => isset ( $default_options ['bar_border_width'] ) ? $default_options ['bar_border_width'] : 1, 'border_color' => isset ( $default_options ['bar_border_color'] ) ? $default_options ['bar_border_color'] : 'c8c8c8' ) );
				wp_enqueue_style( 'shivs-poll-admin-add-new', "{$this->_config->plugin_url}/css/shivs-poll-admin-add-new.css", array(), $this->_config->version );
				wp_enqueue_style( 'shivs-poll-timepicker', "{$this->_config->plugin_url}/css/timepicker.css", array(), $this->_config->version );
				wp_enqueue_style( 'shivs-poll-jquery-ui', "{$this->_config->plugin_url}/css/jquery-ui.css", array(), $this->_config->version );
				wp_enqueue_script( 'shivs-poll-admin-add-new', "{$this->_config->plugin_url}/js/shivs-poll-admin-add-new.js", array( 'jquery', 'shivs-poll-jquery-ui-timepicker' ), $this->_config->version, true );
				wp_enqueue_script( 'shivs-poll-jquery-ui-timepicker', "{$this->_config->plugin_url}/js/jquery-ui-timepicker-addon.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), $this->_config->version, true );
				wp_enqueue_script( 'shivs-poll-modal-box-js', "{$this->_config->plugin_url}/modal/js/jquery.modalbox-1.5.0-min.js", array( 'jquery' ), $this->_config->version, true );
				wp_enqueue_style( 'shivs-poll-modal-box-css', "{$this->_config->plugin_url}/modal/css/jquery.modalbox-skin-precious-white.css", array(), $this->_config->version );
				wp_localize_script( 'shivs-poll-admin-add-new', 'shivs_poll_add_new_config', $shivs_poll_add_new_config );
				wp_enqueue_script( 'link' );
				wp_enqueue_script( 'xfn' );
			break;
			case 'shivs-polls-logs' :
				wp_enqueue_style( 'shivs-poll-timepicker', "{$this->_config->plugin_url}/css/timepicker.css", array(), $this->_config->version );
				wp_enqueue_style( 'shivs-poll-jquery-ui', "{$this->_config->plugin_url}/css/jquery-ui.css", array(), $this->_config->version );
				wp_enqueue_script( 'shivs-poll-jquery-ui-timepicker', "{$this->_config->plugin_url}/js/jquery-ui-timepicker-addon.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), $this->_config->version, true );
				wp_enqueue_script( 'shivs-poll-admin-logs', "{$this->_config->plugin_url}/js/shivs-poll-admin-logs.js", array( 'jquery', 'shivs-poll-jquery-ui-timepicker' ), $this->_config->version, true );
				$this->view_shivs_poll_logs_operations();
			break;
			case 'shivs-polls-bans' :
				wp_enqueue_script( 'shivs-poll-admin-bans', "{$this->_config->plugin_url}/js/shivs-poll-admin-bans.js", array( 'jquery' ), $this->_config->version, true );
				$this->view_shivs_poll_bans_operations();
			break;
			case 'shivs-polls-options' :
				$doScroll = 1;
				wp_enqueue_style( 'shivs-poll-admin-options', "{$this->_config->plugin_url}/css/shivs-poll-admin-options.css", array(), $this->_config->version );
				wp_enqueue_style( 'shivs-poll-timepicker', "{$this->_config->plugin_url}/css/timepicker.css", array(), $this->_config->version );
				wp_enqueue_style( 'shivs-poll-jquery-ui', "{$this->_config->plugin_url}/css/jquery-ui.css", array(), $this->_config->version );
				wp_enqueue_script( 'shivs-poll-admin-options', "{$this->_config->plugin_url}/js/shivs-poll-admin-options.js", array( 'jquery', 'shivs-poll-jquery-ui-timepicker' ), $this->_config->version, true );
				wp_enqueue_script( 'shivs-poll-jquery-ui-timepicker', "{$this->_config->plugin_url}/js/jquery-ui-timepicker-addon.js", array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), $this->_config->version, true );
				wp_enqueue_script( 'link' );
				wp_enqueue_script( 'xfn' );
			break;
			case 'shivs-polls-templates' :
				if ( 'edit' == $action || 'add-new' == $action ){
					$doScroll = 1;
				}
				add_filter( 'user_can_richedit', create_function( '$a', 'return false;' ), 1 );
				wp_enqueue_script( array( 'editor', 'thickbox' ) );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'shivs-poll-admin-templates', "{$this->_config->plugin_url}/js/shivs-poll-admin-templates.js", array( 'jquery' ), $this->_config->version, true );
				$shivs_poll_add_new_template_config = array( 'ajax' => array( 'url' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ), 'action' => 'shivs_poll_edit_add_new_poll_template', 'reset_action' => 'shivs_poll_reset_poll_template', 'beforeSendMessage' => __( 'Please wait a moment while we process your request...', 'shivs_poll' ), 'errorMessage' => __( 'An error has occured...', 'shivs_poll' ) ) );
				wp_enqueue_script( 'shivs-poll-modal-box-js', "{$this->_config->plugin_url}/modal/js/jquery.modalbox-1.5.0-min.js", array( 'jquery' ), $this->_config->version, true );
				wp_enqueue_style( 'shivs-poll-modal-box-css', "{$this->_config->plugin_url}/modal/css/jquery.modalbox-skin-precious-white.css", array(), $this->_config->version );
				wp_localize_script( 'shivs-poll-admin-templates', 'shivs_poll_add_new_template_config', $shivs_poll_add_new_template_config );
				$this->view_shivs_poll_templates_operations();
			break;
			default :
				$this->view_all_polls_operations();
			break;
		}
		wp_localize_script( 'shivs-poll-admin', "shivs_poll_do_scroll", array( 'doScroll' => $doScroll ) );
	}

	public function view_shivs_poll_logs_operations() {
		global $page, $action, $order, $orderby, $current_user;
		if ( '-1' != $action && isset ( $_REQUEST ['shivspolllogscheck'] ) ){
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-logs' );
				$bulklogs = ( array )$_REQUEST ['shivspolllogscheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_deleted = 0;
				foreach ( $bulklogs as $log_id ) {
					$log_id      = ( int )$log_id;
					$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_id( 'poll_id', $log_id );
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
					if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
						Shivs_Poll_Model::delete_poll_log_from_db( $log_id );
					}
					else {
						$total_deleted++;
					}
				}
				wp_redirect( add_query_arg( 'deleted', count( $bulklogs ) - $total_deleted, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'shivspolllogscheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}

			if ( 'delete_group' == $action ){
				check_admin_referer( 'shivs-poll-logs' );
				$bulklogs = ( array )$_REQUEST ['shivspolllogscheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_deleted_group = 0;
				foreach ( $bulklogs as $vote_id ) {
					$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_vote_id( 'poll_id', $vote_id );
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
					if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
						Shivs_Poll_Model::delete_group_poll_log_from_db( $vote_id );
					}
					else {
						$total_deleted_group++;
					}
				}
				wp_redirect( add_query_arg( 'deleted', count( $bulklogs ) - $total_deleted_group, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'action', 'shivspolllogscheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( '-1' != $action && isset ( $_REQUEST ['id'] ) ) {
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-logs-delete' );
				$log_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_id( 'poll_id', $log_id );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
				if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
					Shivs_Poll_Model::delete_poll_log_from_db( $log_id );
				}
				else {
					wp_die( __( 'You are not allowed to delete this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'deleted', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'delete_group' == $action ){
				check_admin_referer( 'shivs-poll-logs-delete' );
				$vote_id = $_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_vote_id( 'poll_id', $vote_id );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
				if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
					Shivs_Poll_Model::delete_group_poll_log_from_db( $vote_id );
				}
				else {
					wp_die( __( 'You are not allowed to delete this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'deleted', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( isset ( $_REQUEST ['export'] ) ) {
			global $wpdb;
			if ( isset( $_REQUEST ['a'] ) && __( 'Export', 'shivs_poll' ) == $_REQUEST ['a'] ){
				check_admin_referer( 'shivs-poll-logs' );
				$per_page  = ( isset ( $_GET ['per_page'] ) ? intval( $_GET ['per_page'] ) : 100 );
				$page_no   = isset ( $_REQUEST ['page_no'] ) ? ( int )$_REQUEST ['page_no'] : 1;
				$orderby   = ( empty ( $orderby ) ) ? 'name' : $orderby;
				$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? ( int )$_REQUEST ['poll_id'] : NULL;
				$log_sdate = ( isset ( $_GET ['log_sdate'] ) ? $_GET ['log_sdate'] : '' );
				$log_edate = ( isset ( $_GET ['log_edate'] ) ? $_GET ['log_edate'] : '' );
				$group_by  = ( isset ( $_GET ['group_by'] ) ? $_GET ['group_by'] : 'vote' );

				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
				if ( $this->current_user_can( 'view_own_polls_logs' ) && $poll_id ){
					if ( $poll_author != $current_user->ID && !$this->current_user_can( 'view_polls_logs' ) ){
						wp_die( __( 'You are not allowed to access this section.', 'shivs_poll' ) );
					}
				}
				elseif ( !$this->current_user_can( 'view_polls_logs' ) ) {
					wp_die( __( 'You are not allowed to access this section.', 'shivs_poll' ) );
				}

				$shivs_polls = Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
				if ( $group_by == 'vote' ){
					$search = array( 'fields' => array( 'name', 'ip', 'user_nicename', 'user_email', 'user_from', 'tr_id' ), 'value' => isset ( $_REQUEST ['s'] ) ? trim( $_REQUEST ['s'] ) : '' );
				}
				else {
					$search = array( 'fields' => array( 'name', 'answer', 'ip', 'other_answer_value', 'user_nicename', 'user_from', 'user_email', 'tr_id' ), 'value' => isset ( $_REQUEST ['s'] ) ? trim( $_REQUEST ['s'] ) : '' );
				}
				$filter = array( 'field' => NULL, 'value' => NULL, 'operator' => '=' );
				if ( 'all' == $_REQUEST ['export'] ){
					if ( $group_by == 'vote' ){
						$logs = Shivs_Poll_Model::get_group_logs_filter_search( $orderby, $order, $search, $poll_id, 0, 99999999, $log_sdate, $log_edate );
					}
					else {
						$logs = Shivs_Poll_Model::get_logs_filter_search( $orderby, $order, $search, $poll_id, 0, 99999999, $log_sdate, $log_edate );
					}
				}
				if ( 'page' == $_REQUEST ['export'] ){
					if ( $group_by == 'vote' ){
						$logs = Shivs_Poll_Model::get_group_logs_filter_search( $orderby, $order, $search, $poll_id, ( $page_no - 1 ) * $per_page, $per_page, $log_sdate, $log_edate );
					}
					else {
						$logs = Shivs_Poll_Model::get_logs_filter_search( $orderby, $order, $search, $poll_id, ( $page_no - 1 ) * $per_page, $per_page, $log_sdate, $log_edate );
					}
				}

				$csv_file_name    = 'logs_export.' . date( 'YmdHis' ) . '.csv';
				$csv_header_array = array( __( '#', 'shivs_poll' ), __( 'Vote ID', 'shivs_poll' ), __( 'POLL Name', 'shivs_poll' ), __( 'Answer', 'shivs_poll' ), __( 'User Type', 'shivs_poll' ), __( 'User', 'shivs_poll' ), __( 'User Email', 'shivs_poll' ), __( 'Tracking ID', 'shivs_poll' ), __( 'IP', 'shivs_poll' ), __( 'Vote Date', 'shivs_poll' ) );

				header( 'Content-type: application/csv' );
				header( 'Content-Disposition: attachment; filename="' . $csv_file_name . '"' );
				ob_start();
				$f = fopen( 'php://output', 'w' ) or show_error( __( "Can't open php://output!", 'shivs_poll' ) );

				if ( !fputcsv( $f, $csv_header_array ) ){
					_e( "Can't write header!", 'shivs_poll' );
				}

				if ( count( $logs ) > 0 ){
					$index = 1;
					foreach ( $logs as $log ) {
						$logs_data = array( $index, $log ['vote_id'], stripslashes( $log ['name'] ), ( 'Other' == $log ['answer'] ) ? 'Other - ' . stripslashes( $log ['other_answer_value'] ) : stripslashes( $log ['answer'] ), stripslashes( $log ['user_from'] ), stripslashes( $log ['user_nicename'] ), stripslashes( $log ['user_email'] ), stripslashes( $log ['tr_id'] ), stripslashes( $log ['ip'] ), stripslashes( $log ['vote_date'] ) );
						if ( !fputcsv( $f, $logs_data ) ){
							_e( "Can't write header!", 'shivs_poll' );
						}
						$index++;
					}
				}

				fclose( $f ) or show_error( __( "Can't close php://output!", 'shivs_poll' ) );
				$csvStr = ob_get_contents();
				ob_end_clean();

				echo $csvStr;
				exit ();
			}

			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'export', 'a' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function shivs_poll_custom_fields_operations() {
		global $page, $action;
		if ( isset ( $_REQUEST ['export'] ) ){
			check_admin_referer( 'shivs-poll-custom-fields' );
			if ( '' != $_REQUEST ['export'] ){
				$per_page = ( isset ( $_GET ['per_page'] ) ? intval( $_GET ['per_page'] ) : 100 );
				$page_no  = ( isset ( $_REQUEST ['page_no'] ) ? ( int )$_REQUEST ['page_no'] : 1 );
				$poll_id  = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
				$sdate    = ( isset ( $_GET ['sdate'] ) ? $_GET ['sdate'] : '' );
				$edate    = ( isset ( $_GET ['edate'] ) ? $_GET ['edate'] : '' );
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_details                   = Shivs_Poll_Model::get_poll_from_database_by_id( $poll_id );
				$poll_custom_fields             = Shivs_Poll_Model::get_poll_customfields( $poll_id );
				$custom_fields_number           = count( $poll_custom_fields );
				$column_custom_fields_ids       = array();
				$total_custom_fields_logs       = Shivs_Poll_Model::get_poll_total_customfields_logs( $poll_id, $sdate, $edate );
				$total_custom_fields_logs_pages = ceil( $total_custom_fields_logs / $per_page );
				if ( intval( $page_no ) > intval( $total_custom_fields_logs_pages ) ){
					$page_no = 1;
				}

				if ( 'all' == $_REQUEST ['export'] ){
					$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', 0, 99999999, $sdate, $edate );
				}
				if ( 'page' == $_REQUEST ['export'] ){
					$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', ( $page_no - 1 ) * $per_page, $per_page, $sdate, $edate );
				}

				$csv_file_name    = 'custom_fields_export.' . date( 'YmdHis' ) . '.csv';
				$csv_header_array = array( __( '#', 'shivs_poll' ) );
				foreach ( $poll_custom_fields as $custom_field ) {
					$column_custom_fields_ids [] = $custom_field ['id'];
					$csv_header_array []         = ucfirst( $custom_field ['custom_field'] );
				}
				$csv_header_array [] = __( 'Vote Date', 'shivs_poll' );

				header( 'Content-type: application/csv' );
				header( 'Content-Disposition: attachment; filename="' . $csv_file_name . '"' );
				ob_start();
				$f = fopen( 'php://output', 'w' ) or show_error( __( "Can't open php://output!", 'shivs_poll' ) );
				$n = 0;
				if ( isset ( $csv_header_array ) ){
					if ( !fputcsv( $f, $csv_header_array ) ){
						_e( "Can't write header!", 'shivs_poll' );
					}
				}

				if ( count( $custom_fields_logs ) > 0 ){
					$index = 1;
					foreach ( $custom_fields_logs as $logs ) {
						$column_custom_fields_values = array( $index );
						foreach ( $column_custom_fields_ids as $custom_field_id ) {
							$vote_log_values = array();
							$vote_logs       = explode( '<#!,>', $logs ['vote_log'] );
							if ( count( $vote_logs ) > 0 ){
								foreach ( $vote_logs as $vote_log ) {
									$temp                        = explode( '<#!->', $vote_log );
									$vote_log_values [$temp [1]] = stripslashes( $temp [0] );
								}
							}
							$column_custom_fields_values [] = isset ( $vote_log_values [$custom_field_id] ) ? $vote_log_values [$custom_field_id] : '';
						}
						$column_custom_fields_values [] = $logs ['vote_date'];
						if ( !fputcsv( $f, $column_custom_fields_values ) ){
							_e( "Can't write record!", 'shivs_poll' );
						}
						$index++;
					}
				}
				fclose( $f ) or show_error( __( "Can't close php://output!", 'shivs_poll' ) );
				$csvStr = ob_get_contents();
				ob_end_clean();

				echo $csvStr;
				exit ();
			}
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'export', 'a' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'a' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function shivs_poll_custom_fields_results_operations() {
		global $page, $action;
		if ( isset ( $_REQUEST ['export'] ) ){
			check_admin_referer( 'shivs-poll-custom-fields' );
			if ( __( 'Export', 'shivs_poll' ) == $_REQUEST ['a'] ){
				$cf_per_page = ( isset ( $_GET ['cf_per_page'] ) ? intval( $_GET ['cf_per_page'] ) : 100 );
				$cf_page_no  = ( isset ( $_REQUEST ['cf_page_no'] ) ? ( int )$_REQUEST ['cf_page_no'] : 1 );
				$poll_id     = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
				$cf_sdate    = ( isset ( $_GET ['cf_sdate'] ) ? $_GET ['cf_sdate'] : '' );
				$cf_edate    = ( isset ( $_GET ['cf_edate'] ) ? $_GET ['cf_edate'] : '' );
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_details                   = Shivs_Poll_Model::get_poll_from_database_by_id( $poll_id );
				$poll_custom_fields             = Shivs_Poll_Model::get_poll_customfields( $poll_id );
				$custom_fields_number           = count( $poll_custom_fields );
				$column_custom_fields_ids       = array();
				$total_custom_fields_logs       = Shivs_Poll_Model::get_poll_total_customfields_logs( $poll_id, $cf_sdate, $cf_edate );
				$total_custom_fields_logs_pages = ceil( $total_custom_fields_logs / $cf_per_page );
				if ( intval( $cf_page_no ) > intval( $total_custom_fields_logs_pages ) ){
					$cf_page_no = 1;
				}

				if ( 'all' == $_REQUEST ['export'] ){
					$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', 0, 99999999, $cf_sdate, $cf_edate );
				}
				if ( 'page' == $_REQUEST ['export'] ){
					$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', ( $cf_page_no - 1 ) * $cf_per_page, $cf_per_page, $cf_sdate, $cf_edate );
				}

				$csv_file_name    = 'custom_fields_export.' . date( 'YmdHis' ) . '.csv';
				$csv_header_array = array( __( '#', 'shivs_poll' ) );
				foreach ( $poll_custom_fields as $custom_field ) {
					$column_custom_fields_ids [] = $custom_field ['id'];
					$csv_header_array []         = ucfirst( $custom_field ['custom_field'] );
				}
				$csv_header_array [] = __( 'Vote ID', 'shivs_poll' );
				$csv_header_array [] = __( 'Traking ID', 'shivs_poll' );
				$csv_header_array [] = __( 'Vote Date', 'shivs_poll' );

				header( 'Content-type: application/csv' );
				header( 'Content-Disposition: attachment; filename="' . $csv_file_name . '"' );
				ob_start();
				$f = fopen( 'php://output', 'w' ) or show_error( __( "Can't open php://output!", 'shivs_poll' ) );
				$n = 0;
				if ( isset ( $csv_header_array ) ){
					if ( !fputcsv( $f, $csv_header_array ) ){
						_e( "Can't write header!", 'shivs_poll' );
					}
				}

				if ( count( $custom_fields_logs ) > 0 ){
					$index = 1;
					foreach ( $custom_fields_logs as $logs ) {
						$column_custom_fields_values = array( $index );
						foreach ( $column_custom_fields_ids as $custom_field_id ) {
							$vote_log_values = array();
							$vote_logs       = explode( '<#!,>', $logs ['vote_log'] );
							if ( count( $vote_logs ) > 0 ){
								foreach ( $vote_logs as $vote_log ) {
									$temp                        = explode( '<#!->', $vote_log );
									$vote_log_values [$temp [1]] = stripslashes( $temp [0] );
								}
							}
							$column_custom_fields_values [] = isset ( $vote_log_values [$custom_field_id] ) ? $vote_log_values [$custom_field_id] : '';
						}
						$column_custom_fields_values [] = $logs ['vote_id'];
						$column_custom_fields_values [] = $logs ['tr_id'];
						$column_custom_fields_values [] = $logs ['vote_date'];
						if ( !fputcsv( $f, $column_custom_fields_values ) ){
							_e( "Can't write record!", 'shivs_poll' );
						}
						$index++;
					}
				}
				fclose( $f ) or show_error( __( "Can't close php://output!", 'shivs_poll' ) );
				$csvStr = ob_get_contents();
				ob_end_clean();

				echo $csvStr;
				exit ();
			}
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'export', 'a' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'a' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function view_shivs_poll_bans_operations() {
		global $page, $action;
		if ( '-1' != $action && isset ( $_REQUEST ['shivspollbanscheck'] ) ){
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-bans' );
				$bulkbans = ( array )$_REQUEST ['shivspollbanscheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				foreach ( $bulkbans as $ban_id ) {
					$ban_id = ( int )$ban_id;
					Shivs_Poll_Model::delete_poll_ban_from_db( $ban_id );
				}
				wp_redirect( add_query_arg( 'deleted', count( $bulkbans ), remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'shivspollbanscheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( '-1' != $action && isset ( $_REQUEST ['id'] ) ) {
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-bans-delete' );
				$ban_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				Shivs_Poll_Model::delete_poll_ban_from_db( $ban_id );
				wp_redirect( add_query_arg( 'deleted', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( 'add-ban' == $action ) {
			check_admin_referer( 'shivs-poll-add-ban' );
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$bans = Shivs_Poll_Model::add_bans( $_REQUEST );
			if ( $bans ['error'] != '' ){
				wp_redirect( add_query_arg( 'bans-error', urlencode( $bans ['error'] ), remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			else {
				wp_redirect( add_query_arg( 'bans-added', urlencode( ( int )$bans ['success'] ), remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function view_shivs_poll_templates_operations() {
		global $page, $action, $shivs_poll_add_new_config, $current_user;
		if ( '-1' != $action && isset ( $_REQUEST ['templatecheck'] ) ){
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-templates' );
				$bulktemplates = ( array )$_REQUEST ['templatecheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_deleted = 0;
				foreach ( $bulktemplates as $template_id ) {
					$template_id     = ( int )$template_id;
					$template_author = Shivs_Poll_Model::get_poll_template_field_from_database_by_id( 'template_author', $template_id );
					if ( ( $this->current_user_can( 'delete_own_polls_templates' ) && $template_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_templates' ) ) ){
						Shivs_Poll_Model::delete_poll_template_from_db( $template_id );
					}
					else {
						$total_deleted++;
					}
				}
				wp_redirect( add_query_arg( 'deleted', count( $bulktemplates ) - $total_deleted, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'templatecheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'clone' == $action ){
				check_admin_referer( 'shivs-poll-templates' );
				$bulktemplates = ( array )$_REQUEST ['templatecheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_cloned = 0;
				foreach ( $bulktemplates as $template_id ) {
					$template_id     = ( int )$template_id;
					$template_author = Shivs_Poll_Model::get_poll_template_field_from_database_by_id( 'template_author', $template_id );
					if ( ( $this->current_user_can( 'clone_own_polls_templates' ) && $template_author == $current_user->ID ) || ( $this->current_user_can( 'clone_polls_templates' ) ) ){
						Shivs_Poll_Model::clone_poll_template( $template_id );
					}
					else {
						$total_cloned++;
					}
				}
				wp_redirect( add_query_arg( 'cloned', count( $bulktemplates ) - $total_cloned, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'templatecheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( '-1' != $action && isset ( $_REQUEST ['id'] ) ) {
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-templates' );
				$template_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$template_author = Shivs_Poll_Model::get_poll_template_field_from_database_by_id( 'template_author', $template_id );
				if ( ( $this->current_user_can( 'delete_own_polls_templates' ) && $template_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_templates' ) ) ){
					Shivs_Poll_Model::delete_poll_template_from_db( $template_id );
				}
				else {
					wp_die( __( 'You are not allowed to delete this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'deleted', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'clone' == $action ){
				check_admin_referer( 'shivs-poll-templates' );
				$template_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$template_author = Shivs_Poll_Model::get_poll_template_field_from_database_by_id( 'template_author', $template_id );
				if ( ( $this->current_user_can( 'clone_own_polls_templates' ) && $template_author == $current_user->ID ) || ( $this->current_user_can( 'clone_polls_templates' ) ) ){
					Shivs_Poll_Model::clone_poll_template( $template_id );
				}
				else {
					wp_die( __( 'You are not allowed to clone this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'cloned', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function view_all_polls_operations() {
		global $page, $action, $shivs_poll_add_new_config, $current_user;
		if ( '-1' != $action && isset ( $_REQUEST ['shivspollcheck'] ) ){
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-view' );
				$bulkshivspolls = ( array )$_REQUEST ['shivspollcheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_undeleted = 0;
				foreach ( $bulkshivspolls as $shivspoll_id ) {
					$shivspoll_id  = ( int )$shivspoll_id;
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
					if ( ( $this->current_user_can( 'delete_own_polls' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls' ) ) ){
						Shivs_Poll_Model::delete_poll_from_db( $shivspoll_id );
					}
					else {
						$total_undeleted++;
					}
				}
				wp_redirect( add_query_arg( 'deleted', count( $bulkshivspolls ) - $total_undeleted, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'shivspollcheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'clone' == $action ){
				check_admin_referer( 'shivs-poll-view' );
				$bulkshivspolls = ( array )$_REQUEST ['shivspollcheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_uncloned = 0;
				foreach ( $bulkshivspolls as $shivspoll_id ) {
					$shivspoll_id  = ( int )$shivspoll_id;
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
					if ( ( $this->current_user_can( 'clone_own_polls' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'clone_polls' ) ) ){
						Shivs_Poll_Model::clone_poll( $shivspoll_id );
					}
					else {
						$total_uncloned++;
					}
				}
				wp_redirect( add_query_arg( 'cloned', count( $bulkshivspolls ) - $total_uncloned, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'shivspollcheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'reset_votes' == $action ){
				check_admin_referer( 'shivs-poll-view' );
				$bulkshivspolls = ( array )$_REQUEST ['shivspollcheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_unreseted = 0;
				foreach ( $bulkshivspolls as $shivspoll_id ) {
					$shivspoll_id  = ( int )$shivspoll_id;
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
					if ( ( $this->current_user_can( 'reset_own_polls_stats' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'reset_polls_stats' ) ) ){
						Shivs_Poll_Model::reset_votes_for_poll( $shivspoll_id );
					}
					else {
						$total_unreseted++;
					}
				}
				wp_redirect( add_query_arg( 'reseted_votes', count( $bulkshivspolls ) - $total_unreseted, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'shivspollcheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
			if ( 'delete_logs' == $action ){
				check_admin_referer( 'shivs-poll-view' );
				$bulkshivspolls = ( array )$_REQUEST ['shivspollcheck'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$total_undeleted_logs = 0;
				foreach ( $bulkshivspolls as $shivspoll_id ) {
					$shivspoll_id  = ( int )$shivspoll_id;
					$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
					if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
						Shivs_Poll_Model::delete_all_poll_logs( $shivspoll_id );
					}
					else {
						$total_undeleted_logs++;
					}
				}
				wp_redirect( add_query_arg( 'deleted_logs', count( $bulkshivspolls ) - $total_undeleted_logs, remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'shivspollcheck' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( '-1' != $action && isset ( $_REQUEST ['id'] ) ) {
			if ( 'delete' == $action ){
				check_admin_referer( 'shivs-poll-delete' );
				$shivspoll_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
				if ( ( $this->current_user_can( 'delete_own_polls' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls' ) ) ){
					Shivs_Poll_Model::delete_poll_from_db( $shivspoll_id );
				}
				else {
					wp_die( __( 'You are not allowed to delete this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'deleted', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}

			if ( 'clone' == $action ){
				check_admin_referer( 'shivs-poll-clone' );
				$shivspoll_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
				if ( ( $this->current_user_can( 'clone_own_polls' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'clone_polls' ) ) ){
					Shivs_Poll_Model::clone_poll( $shivspoll_id );
				}
				else {
					wp_die( __( 'You are not allowed to clone this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'cloned', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}

			if ( 'reset_votes' == $action ){
				check_admin_referer( 'shivs-poll-reset-votes' );
				$shivspoll_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
				if ( ( $this->current_user_can( 'reset_own_polls_stats' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'reset_polls_stats' ) ) ){
					Shivs_Poll_Model::reset_votes_for_poll( $shivspoll_id );
				}
				else {
					wp_die( __( 'You are not allowed to reset stats for this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'reseted_votes', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}

			if ( 'delete_logs' == $action ){
				check_admin_referer( 'shivs-poll-delete-logs' );
				$shivspoll_id = ( int )$_REQUEST ['id'];
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $shivspoll_id );
				if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
					Shivs_Poll_Model::delete_all_poll_logs( $shivspoll_id );
				}
				else {
					wp_die( __( 'You are not allowed to delete logs for this item.', 'shivs_poll' ) );
				}
				wp_redirect( add_query_arg( 'deleted_logs', 1, remove_query_arg( array( '_wpnonce', 'id', 'action' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) ) );
				exit ();
			}
		}
		elseif ( !empty ( $_GET ['_wp_http_referer'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), stripslashes( $_SERVER ['REQUEST_URI'] ) ) );
			exit ();
		}
	}

	public function view_all_polls() {
		global $page, $action, $orderby, $order, $current_user;
		$orderby                    = ( empty ( $orderby ) ) ? 'name' : $orderby;
		$order_direction            = array( 'id' => 'asc', 'name' => 'asc', 'question' => 'asc', 'start_date' => 'asc', 'end_date' => 'asc', 'total_votes' => 'asc', 'total_answers' => 'asc' );
		$order_direction [$orderby] = ( 'desc' == $order ) ? 'asc' : 'desc';

		$order_direction_reverse            = array( 'id' => 'desc', 'name' => 'desc', 'question' => 'desc', 'start_date' => 'desc', 'end_date' => 'desc', 'total_votes' => 'desc', 'total_answers' => 'desc' );
		$order_direction_reverse [$orderby] = ( 'desc' == $order ) ? 'desc' : 'asc';

		$order_sortable            = array( 'id' => 'sortable', 'name' => 'sortable', 'question' => 'sortable', 'start_date' => 'sortable', 'end_date' => 'sortable', 'total_votes' => 'sortable', 'total_answers' => 'sortable' );
		$order_sortable [$orderby] = 'sorted';
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$filter = array( 'field' => NULL, 'value' => NULL, 'operator' => '=' );
		if ( isset ( $_REQUEST ['filters'] ) ){
			switch ( $_REQUEST ['filters'] ) {
				case 'started' :
					$filter = array( 'field' => 'start_date', 'value' => Shivs_Poll_Model::get_mysql_curent_date(), 'operator' => '<=' );
					break;
				case 'not_started' :
					$filter = array( 'field' => 'start_date', 'value' => Shivs_Poll_Model::get_mysql_curent_date(), 'operator' => '>=' );
					break;
				case 'never_expire' :
					$filter = array( 'field' => 'end_date', 'value' => '9999-12-31 23:59:59', 'operator' => '=' );
					break;
				case 'expired' :
					$filter = array( 'field' => 'end_date', 'value' => Shivs_Poll_Model::get_mysql_curent_date(), 'operator' => '<=' );
					break;
			}
		}
		$search                  = array(
			'fields' => array( 'name', 'question' ),
			'value'  => isset ( $_REQUEST ['s'] ) ? $_REQUEST ['s'] : ''
		);
		$shivs_polls               = Shivs_Poll_Model::get_shivs_polls_filter_search( $orderby, $order, $filter, $search );
		$opt_box_modal_options = get_option( 'shivs_poll_opt_box_modal_options' );
		$opt_box_modal_query   = admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) );
		$opt_box_modal_query   = add_query_arg( 'action', 'shivs_poll_show_opt_box_modal', $opt_box_modal_query );
	?>
	<?php if ( $opt_box_modal_options['show'] == 'yes' ){ ?>
		<a id="shivs-poll-show-modal-box"
			href="<?php echo $opt_box_modal_query; ?>" style="display: none;"></a>
		<?php } ?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Polls', 'shivs_poll' ); ?><a class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-add-new', 'id' => false, 'deleted' => false, 'cloned' => false ) ) ); ?>"><?php _e( 'Add New', 'shivs_poll' ); ?></a>
		</h2>
		<?php
			if ( isset ( $_REQUEST ['deleted'] ) ){
				echo '<div id="message" class="updated"><p>';
				$deleted = ( int )$_REQUEST ['deleted'];
				printf( _n( '%s Poll deleted.', '%s Polls deleted.', $deleted ), $deleted );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<?php
			if ( isset ( $_REQUEST ['cloned'] ) ){
				echo '<div id="message" class="updated"><p>';
				$cloned = ( int )$_REQUEST ['cloned'];
				printf( _n( '%s Poll cloned.', '%s Polls cloned.', $cloned ), $cloned );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'cloned' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<?php
			if ( isset ( $_REQUEST ['reseted_votes'] ) ){
				echo '<div id="message" class="updated"><p>';
				$reseted_votes = ( int )$_REQUEST ['reseted_votes'];
				printf( _n( 'Vote reseted for %s Poll.', 'Votes reseted for %s Poll.', $reseted_votes ), $reseted_votes );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'reseted_votes' ), $_SERVER ['REQUEST_URI'] );
			}
		?>

		<?php
			if ( isset ( $_REQUEST ['deleted_logs'] ) ){
				echo '<div id="message" class="updated"><p>';
				$deleted_logs = ( int )$_REQUEST ['deleted_logs'];
				printf( _n( 'Log deleted for %s Poll.', 'Log deleted for %s Polls.', $deleted_logs ), $deleted_logs );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'deleted_logs' ), $_SERVER ['REQUEST_URI'] );
			}
		?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder">
				<form action="" method="get">
					<div id="post-body-content">
						<?php wp_nonce_field( 'shivs-poll-view' ); ?>
						<input type="hidden" name="order" value="<?php echo $order ?>"/>
						<input type="hidden" name="orderby" value="<?php echo $orderby ?>"/>
						<input type="hidden" name="page" value="shivs-polls"/>
						<p class="search-box">
							<label class="screen-reader-text" for="shivs-poll-search-input"><?php _e( 'Search Polls', 'shivs_poll' ) ?></label>
							<input id="shivs-poll-search-input" type="search"
								value="<?php if ( isset( $_REQUEST['s'] ) ): echo esc_html( stripslashes( $_REQUEST['s'] ) ); endif; ?>"
								name="s"/> <input id="search-submit" class="button"
								type="submit" value="<?php _e( 'Search Polls', 'shivs_poll' ); ?>"
								name=""/>
						</p>
						<div class="tablenav top">
							<div class="alignleft actions">
								<select name="action">
									<option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'shivs_poll' ); ?></option>
									<option value="delete"><?php _e( 'Delete', 'shivs_poll' ); ?></option>
									<option value="clone"><?php _e( 'Clone', 'shivs_poll' ); ?></option>
									<option value="reset_votes"><?php _e( 'Reset Votes', 'shivs_poll' ); ?></option>
									<option value="delete_logs"><?php _e( 'Delete Logs', 'shivs_poll' ); ?></option>
								</select>
								<input type="submit"
									value="<?php _e( 'Apply', 'shivs_poll' ); ?>"
									class="button-secondary action" id="doaction" name="">
							</div>
							<div class="alignleft actions">
								<select name="filters">
									<option value="0"><?php _e( 'View All Polls', 'shivs_poll' ); ?></option>
									<option
										<?php echo isset( $_REQUEST['filters'] ) ? ( 'never_expire' == $_REQUEST['filters'] ? 'selected="selected"' : '' ) : '' ?>
										value="never_expire"><?php _e( 'No end date', 'shivs_poll' ); ?></option>
									<option
										<?php echo isset( $_REQUEST['filters'] ) ? ( 'expired' == $_REQUEST['filters'] ? 'selected="selected"' : '' ) : '' ?>
										value="expired"><?php _e( 'Expired', 'shivs_poll' ); ?></option>
									<option
										<?php echo isset( $_REQUEST['filters'] ) ? ( 'started' == $_REQUEST['filters'] ? 'selected="selected"' : '' ) : '' ?>
										value="started"><?php _e( 'Started', 'shivs_poll' ); ?></option>
									<option
										<?php echo isset( $_REQUEST['filters'] ) ? ( 'not_started' == $_REQUEST['filters'] ? 'selected="selected"' : '' ) : '' ?>
										value="not_started"><?php _e( 'Not Started', 'shivs_poll' ); ?></option>
								</select>
								<input type="submit"
									value="<?php _e( 'Filter', 'shivs_poll' ); ?>"
									class="button-secondary" id="post-query-submit" name="">
							</div>
							<br class="clear">
						</div>
						<table class="wp-list-table widefat fixed" cellspacing="0">
							<thead>
								<tr>
									<th id="cb" class="manage-column column-cb check-column"
										style="width: 3%;" scope="col"><input type="checkbox"></th>
									<th id="name"
										class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
										style="width: 30%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
											<span><?php _e( 'Name', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="total_votes"
										class="manage-column <?php echo $order_sortable['total_votes'] ?> <?php echo $order_direction_reverse['total_votes'] ?>"
										style="width: 6%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'total_votes', 'order' => $order_direction['total_votes'] ) ) ); ?>">
											<span><?php _e( 'Total Votes', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="total_answers"
										class="manage-column <?php echo $order_sortable['total_answers'] ?> <?php echo $order_direction_reverse['total_answers'] ?>"
										style="width: 7%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'total_answers', 'order' => $order_direction['total_answers'] ) ) ); ?>">
											<span><?php _e( 'Total Answers', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="question"
										class="manage-column <?php echo $order_sortable['question'] ?> <?php echo $order_direction_reverse['question'] ?>"
										style="width: 24%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'question', 'order' => $order_direction['question'] ) ) ); ?>">
											<span><?php _e( 'Question', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="poll_author" class="manage-column" style="width: 8%"
										scope="col"><span><?php _e( 'Author', 'shivs_poll' ); ?></span></th>
									<th id="start-date"
										class="manage-column <?php echo $order_sortable['start_date'] ?> <?php echo $order_direction_reverse['start_date'] ?>"
										style="width: 10%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'start_date', 'order' => $order_direction['start_date'] ) ) ); ?>">
											<span><?php _e( 'Start Date', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="end-date"
										class="manage-column <?php echo $order_sortable['end_date'] ?> <?php echo $order_direction_reverse['end_date'] ?>"
										style="width: 10%" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'end_date', 'order' => $order_direction['end_date'] ) ) ); ?>">
											<span><?php _e( 'End Date', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
								</tr>
							</thead>
							<?php
								if ( count( $shivs_polls ) > 0 ){
									foreach ( $shivs_polls as $shivs_poll ) {
									?>
									<tbody id="the-list">
										<tr valign="middle" class="alternate"
											id="shivs-poll-<?php echo $shivs_poll['id']; ?>">
											<th class="check-column" scope="row">
												<?php if ( ( $this->current_user_can( 'delete_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'delete_polls' ) ) ){ ?>
													<input type="checkbox"
														value="<?php echo $shivs_poll['id']; ?>" name="shivspollcheck[]">
													<?php } ?>
											</th>
											<td><strong>
													<?php if (( $this->current_user_can( 'edit_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls' ) )) { ?>
														<a
															title="<?php echo esc_html( stripslashes( $shivs_poll['name'] ) ); ?>"
															href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $shivs_poll['id'] ) ) ); ?>"
															class="row-title">
															<?php } ?>
														<?php echo esc_html( stripslashes( $shivs_poll['name'] ) ); ?>
														<?php if (( $this->current_user_can( 'edit_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls' ) )) { ?>
														</a>
														<?php } ?>
												</strong><br>
												<div class="row-actions">
													<?php if ( ( $this->current_user_can( 'edit_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls' ) ) ){ ?>
														<span class="edit"><a
																href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $shivs_poll['id'] ) ) ); ?>"><?php _e( 'Edit', 'shivs_poll' ) ?></a>
															| </span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'view_own_polls_logs' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'view_polls_logs' ) ) ){ ?>
														<span class="edit"><a
																href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-logs', 'poll_id' => $shivs_poll['id'] ) ) ); ?>"><?php _e( 'Logs', 'shivs_poll' ) ?></a>
															| </span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'delete_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'delete_polls' ) ) ){ ?>
														<span class="delete"><a
																onclick="if ( confirm( '<?php echo __( "You are about to delete this poll", 'shivs_poll' ) . ": \'" . esc_html( $shivs_poll['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to delete', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
																href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $shivs_poll['id'] ) ), 'shivs-poll-delete' ); ?>"
																class="submitdelete"><?php _e( 'Delete', 'shivs_poll' ) ?></a>
															| </span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'clone_own_polls' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'clone_polls' ) ) ){ ?>
														<span class="clone"><a
																onclick="if ( confirm( '<?php echo __( "You are about to clone this poll", 'shivs_poll' ) . ": \'" . esc_html( $shivs_poll['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to clone', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
																href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'clone', 'id' => $shivs_poll['id'] ) ), 'shivs-poll-clone' ); ?>"
																class="submitclone"><?php _e( 'Clone', 'shivs_poll' ) ?></a> |
														</span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'view_own_polls_results' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'view_polls_results' ) ) ){ ?>
														<span class="results"><a
																href="<?php echo esc_url( add_query_arg( array( 'action' => 'results', 'id' => $shivs_poll['id'] ) ) ); ?>"><?php _e( 'Results', 'shivs_poll' ) ?></a>
															| </span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'reset_own_polls_stats' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'reset_polls_stats' ) ) ){ ?>
														<span class="delete"><a
																onclick="if ( confirm( '<?php echo __( "You are about to reset votes for this poll", 'shivs_poll' ) . ": \'" . esc_html( $shivs_poll['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to reset votes', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
																href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'reset_votes', 'id' => $shivs_poll['id'] ) ), 'shivs-poll-reset-votes' ); ?>"
																class="submitresetvotes"><?php _e( 'Reset Stats', 'shivs_poll' ) ?></a>
															| </span>
														<?php } ?>
													<?php if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $shivs_poll['poll_author'] == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){ ?>
														<span class="delete"><a
																onclick="if ( confirm( '<?php echo __( "You are about to delete logs for this poll", 'shivs_poll' ) . ": \'" . esc_html( $shivs_poll['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to delete logs', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
																href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete_logs', 'id' => $shivs_poll['id'] ) ), 'shivs-poll-delete-logs' ); ?>"
																class="submitresetvotes"><?php _e( 'Delete Logs', 'shivs_poll' ) ?></a></span>
														<?php } ?>
												</div></td>
											<td>
												<?php echo esc_html( stripslashes( $shivs_poll['total_votes'] ) ); ?>
											</td>
											<td>
												<?php echo esc_html( stripslashes( $shivs_poll['total_answers'] ) ); ?>
											</td>
											<td>
												<?php echo esc_html( stripslashes( $shivs_poll['question'] ) ); ?>
											</td>
											<td>
												<?php
													$user_info = get_userdata( $shivs_poll ['poll_author'] );
													if ( $user_info ){
														echo esc_html( stripslashes( $user_info->user_login ) );
													}
													else {
														echo '';
													}
												?>
											</td>
											<td>
												<?php echo esc_html( stripslashes( $shivs_poll['start_date'] ) ); ?>
											</td>
											<td>
												<?php
													if ( Shivs_Poll_Model::get_mysql_curent_date() > $shivs_poll ['end_date'] ){
														echo '<font style="color:#CC0000;"><b>';
													}
													echo ( '9999-12-31 23:59:59' == $shivs_poll ['end_date'] ) ? __( 'No end date', 'shivs_poll' ) : esc_html( stripslashes( $shivs_poll ['end_date'] ) );
													if ( Shivs_Poll_Model::get_mysql_curent_date() > $shivs_poll ['end_date'] ){
														echo '</b></font>';
												}?>
											</td>
										</tr>
									</tbody>
									<?php
									}
								}
								else {
								?>
								<tbody id="the-list">
									<tr valign="middle" class="alternate" id="shivs-poll-<?php ?>">
										<th colspan="8">
											<?php _e( 'No poll found!', 'shivs_poll' ); ?>
										</th>
									</tr>
								</tbody>
								<?php
								}
							?>

							<tfoot>
								<tr>
									<th id="cb" class="manage-column column-cb check-column"
										style="" scope="col"><input type="checkbox"></th>
									<th id="name"
										class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
											<span><?php _e( 'Name', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="total_votes"
										class="manage-column <?php echo $order_sortable['total_votes'] ?> <?php echo $order_direction_reverse['total_votes'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'total_votes', 'order' => $order_direction['total_votes'] ) ) ); ?>">
											<span><?php _e( 'Total Votes', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="total_answers"
										class="manage-column <?php echo $order_sortable['total_answers'] ?> <?php echo $order_direction_reverse['total_answers'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'total_answers', 'order' => $order_direction['total_answers'] ) ) ); ?>">
											<span><?php _e( 'Total Answers', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="question"
										class="manage-column <?php echo $order_sortable['question'] ?> <?php echo $order_direction_reverse['question'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'question', 'order' => $order_direction['question'] ) ) ); ?>">
											<span><?php _e( 'Question', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="poll_author" class="manage-column" style="width: 5%"
										scope="col"><span><?php _e( 'Author', 'shivs_poll' ); ?></span></th>
									<th id="start-date"
										class="manage-column <?php echo $order_sortable['start_date'] ?> <?php echo $order_direction_reverse['start_date'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'start_date', 'order' => $order_direction['start_date'] ) ) ); ?>">
											<span><?php _e( 'Start Date', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
									<th id="end-date"
										class="manage-column <?php echo $order_sortable['end_date'] ?> <?php echo $order_direction_reverse['end_date'] ?>"
										style="" scope="col"><a
											href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'end_date', 'order' => $order_direction['end_date'] ) ) ); ?>">
											<span><?php _e( 'End Date', 'shivs_poll' ); ?></span> <span
												class="sorting-indicator"></span>
										</a></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</form>
			</div>
			<br class="clear">
		</div>
	</div>
	<?php
	}

	public function view_poll_results() {
		global $page, $action, $current_user;
		$poll_id          = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
		$results_order_by = ( isset ( $_GET ['results_order_by'] ) ? $_GET ['results_order_by'] : 'id' );
		$results_order    = ( isset ( $_GET ['results_order'] ) ? $_GET ['results_order'] : 'ASC' );
		$soav             = ( isset ( $_GET ['soav'] ) ? $_GET ['soav'] : 'no' );
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
		if ( ( !$this->current_user_can( 'view_own_polls_results' ) || $poll_author != $current_user->ID ) && ( !$this->current_user_can( 'view_polls_results' ) ) ){
			wp_die( __( 'You are not allowed to view results for this item.', 'shivs_poll' ) );
		}
		$poll_details = Shivs_Poll_Model::get_poll_from_database_by_id( $poll_id );
		if ( 'yes' == $soav ){
			$display_other_answers_values = true;
		}
		else {
			$display_other_answers_values = false;
		}
		$poll_answers      = Shivs_Poll_Model::get_poll_answers( $poll_id, array( 'default', 'other' ), $results_order_by, $results_order, $display_other_answers_values );
		$poll_other_answer = Shivs_Poll_Model::get_poll_answers( $poll_id, array( 'other' ) );

		// other-answers
		$oa_per_page                    = ( isset ( $_GET ['oa_per_page'] ) ? intval( $_GET ['oa_per_page'] ) : 100 );
		$oa_page_no                     = ( isset ( $_REQUEST ['oa_page_no'] ) ? ( int )$_REQUEST ['oa_page_no'] : 1 );
		$total_logs_other_answers       = count( Shivs_Poll_Model::get_other_answers_votes( isset ( $poll_other_answer [0] ['id'] ) ? $poll_other_answer [0] ['id'] : 0 ) );
		$total_logs_other_answers_pages = ceil( $total_logs_other_answers / $oa_per_page );
		if ( intval( $oa_page_no ) > intval( $total_logs_other_answers_pages ) ){
			$oa_page_no = 1;
		}
		$logs_other_answers = Shivs_Poll_Model::get_other_answers_votes( isset ( $poll_other_answer [0] ['id'] ) ? $poll_other_answer [0] ['id'] : 0, ( $oa_page_no - 1 ) * $oa_per_page, $oa_per_page );

		$oa_args       = array( 'base' => remove_query_arg( 'oa_page_no', $_SERVER ['REQUEST_URI'] ) . '%_%', 'format' => '&oa_page_no=%#%', 'total' => $total_logs_other_answers_pages, 'current' => max( 1, $oa_page_no ), 'prev_next' => true, 'prev_text' => __( '&laquo; Previous' ), 'next_text' => __( 'Next &raquo;' ) );
		$oa_pagination = paginate_links( $oa_args );
		// other-answers

		// custom-fields
		$cf_per_page                    = ( isset ( $_GET ['cf_per_page'] ) ? intval( $_GET ['cf_per_page'] ) : 100 );
		$cf_page_no                     = ( isset ( $_REQUEST ['cf_page_no'] ) ? ( int )$_REQUEST ['cf_page_no'] : 1 );
		$cf_sdate                       = ( isset ( $_GET ['cf_sdate'] ) ? $_GET ['cf_sdate'] : '' );
		$cf_edate                       = ( isset ( $_GET ['cf_edate'] ) ? $_GET ['cf_edate'] : '' );
		$poll_custom_fields             = Shivs_Poll_Model::get_poll_customfields( $poll_id );
		$custom_fields_number           = count( $poll_custom_fields );
		$total_custom_fields_logs       = Shivs_Poll_Model::get_poll_total_customfields_logs( $poll_id, $cf_sdate, $cf_edate );
		$total_custom_fields_logs_pages = ceil( $total_custom_fields_logs / $cf_per_page );
		if ( intval( $cf_page_no ) > intval( $total_custom_fields_logs_pages ) ){
			$cf_page_no = 1;
		}
		$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', ( $cf_page_no - 1 ) * $cf_per_page, $cf_per_page, $cf_sdate, $cf_edate );

		$column_custom_fields_ids = array();
		$cf_args                  = array( 'base' => remove_query_arg( 'cf_page_no', $_SERVER ['REQUEST_URI'] ) . '%_%', 'format' => '&cf_page_no=%#%', 'total' => $total_custom_fields_logs_pages, 'current' => max( 1, $cf_page_no ), 'prev_next' => true, 'prev_text' => __( '&laquo; Previous' ), 'next_text' => __( 'Next &raquo;' ) );
		$cf_pagination            = paginate_links( $cf_args );
		// custom-fields
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Shivs Poll Results', 'shivs_poll' ); ?><a class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls' ), remove_query_arg( array( 'action', 'id' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) ) ); ?>"><?php _e( 'All Shivs Polls', 'shivs_poll' ); ?></a>
		</h2>
		<?php
			if ( $poll_details ){
			?>
			<h3>Name: <?php echo esc_html( stripslashes( $poll_details['name'] ) ) ?></h3>
			<h4>Question: <?php echo esc_html( stripslashes( $poll_details['question'] ) ) ?></h4>
			<form method="get">
				<input type="hidden" name="page" value="shivs-polls"/>
				<input type="hidden" name="action" value="results"/>
				<input type="hidden" name="id" value="<?php echo $poll_id; ?>"/>
				<input type="hidden" name="oa_page_no"
					value="<?php echo $oa_page_no; ?>"/>
				<input type="hidden" name="cf_page_no"
					value="<?php echo $cf_page_no; ?>"/>
				<input type="hidden" name="oa_per_page"
					value="<?php echo $oa_per_page; ?>"/>

				<div class="tablenav top">
					<div class="alignleft actions">
						<div style="display:inline; float:left; margin:7px;"><?php _e( 'Order By', 'shivs_poll' ); ?></div>
						<select name="results_order_by">
							<option <?php selected( $results_order_by, 'id' ) ?> value="id"><?php _e( 'Answer ID', 'shivs_poll' ); ?></option>
							<option <?php selected( $results_order_by, 'answer' ) ?> value="answer"><?php _e( 'Answer Value', 'shivs_poll' ); ?></option>
							<option <?php selected( $results_order_by, 'votes' ) ?> value="votes"><?php _e( 'Votes', 'shivs_poll' ); ?></option>
						</select>
						<select name="results_order">
							<option <?php selected( $results_order, 'ASC' ) ?> value="ASC"><?php _e( 'ASC', 'shivs_poll' ); ?></option>
							<option <?php selected( $results_order, 'DESC' ) ?> value="DESC"><?php _e( 'DESC', 'shivs_poll' ); ?></option>
						</select>
						&nbsp;| &nbsp;
						<input type="checkbox" value="yes" <?php checked( $soav, 'yes' ); ?> name="soav" id="shivs-poll-show_other_answers_values"/>
						<label for="shivs-poll-show_other_answers_values"><?php _e( 'Show Other Answers Values', 'shivs_poll' ); ?></label>
						<input type="submit"
							value="<?php _e( 'Filter', 'shivs_poll' ); ?>"
							class="button-secondary action" id="doaction" name="a">
					</div>
					<br class="clear">
				</div>
			</form>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="" class="column-answer" style="width: 40%;" scope="col"><?php _e( 'Answer', 'shivs_poll' ); ?></th>
						<th id="" class="column-votes" style="width: 5%;" scope="col"><?php _e( 'Votes', 'shivs_poll' ); ?></th>
						<th id="" class="column-percent" style="width: 5%;" scope="col"><?php _e( 'Percent', 'shivs_poll' ); ?></th>
						<th id="" class="column-bar" style="width: 45%;" scope="col"></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if ( count( $poll_answers ) > 0 ){
							foreach ( $poll_answers as $answer ) {
							?>
							<tr>
								<th><?php echo esc_html( stripslashes( $answer['answer'] ) ); ?></th>
								<td><?php echo esc_html( stripslashes( $answer['votes'] ) ); ?></td>
								<td><?php echo esc_html( stripslashes( $answer['procentes'] ) ); ?>%</td>
								<td><span class="shivs-poll-admin-result-bar" style="width: <?php echo esc_html( stripslashes( $answer['procentes'] ) ); ?>%;">
									</span></td>
							</tr>
							<?php
							}
						}
						else {
						?>
						<tr>
							<th colspan="4"><?php _e( 'No answers defined!', 'shivs_poll' ); ?></th>
						</tr>
						<?php
						}
					?>
				</tbody>
			</table>
			<br> <br>
			<div style="width: 30%; float: left;">
				<h3><?php _e( 'Poll Other Answers', 'shivs_poll' ); ?></h3>
				<form method="get">
					<input type="hidden" name="page" value="shivs-polls"/>
					<input type="hidden" name="action" value="results"/>
					<input type="hidden" name="id" value="<?php echo $poll_id; ?>"/>
					<input type="hidden" name="cf_page_no"
						value="<?php echo $cf_page_no; ?>"/>
					<input type="hidden" name="oa_page_no"
						value="<?php echo $oa_page_no; ?>"/>
					<input type="hidden" name="cf_per_page"
						value="<?php echo $cf_per_page; ?>"/>
					<input type="hidden" name="results_order_by" value="<?php echo $results_order_by; ?>"/>
					<input type="hidden" name="results_order" value="<?php echo $results_order; ?>"/>
					<input type="hidden" name="soav" value="<?php echo $soav; ?>"/>
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<label for="shivs-poll-oa-items-per-page" class="displaying-num"><?php _e( 'Items Per Page', 'shivs_poll' ); ?>
								:</label><input
								id="shivs-poll-oa-items-per-page" type="text" name="oa_per_page"
								value="<?php echo $oa_per_page; ?>"/> <input name="a"
								value="<?php _e( 'Set', 'shivs_poll' ); ?>" type="submit"/>&nbsp;&nbsp;<span
								class="displaying-num"><?php echo count( $logs_other_answers ); ?>
								/ <?php echo $total_logs_other_answers; ?> items</span>
							<?php print $oa_pagination; ?>
						</div>
						<br class="clear">
					</div>
					<table class="wp-list-table widefat fixed" cellspacing="0">
						<thead>
							<tr>
								<th id="" class="column-answer" style="width: 40%;" scope="col"><?php _e( 'Other Answers', 'shivs_poll' ); ?></th>
								<th id="" class="column-votes" style="width: 5%;" scope="col"><?php _e( 'Votes', 'shivs_poll' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								if ( count( $logs_other_answers ) > 0 ){
									foreach ( $logs_other_answers as $answer ) {
									?>
									<tr>
										<td><?php echo esc_html( stripslashes( $answer['other_answer_value'] ) ); ?></td>
										<td><?php echo esc_html( stripslashes( $answer['votes'] ) ); ?></td>
									</tr>
									<?php
									}
								}
								else {
								?>
								<tr>
									<td colspan="2"><?php _e( 'No other answers defined!', 'shivs_poll' ); ?></td>
								</tr>
								<?php
								}
							?>
						</tbody>
					</table>
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<?php print $oa_pagination; ?>
						</div>
					</div>
					<br class="clear">
				</form>
			</div>
			<div style="width: 69%; float: right;">
				<h3><?php _e( 'Custom Fields', 'shivs_poll' ); ?></h3>
				<form method="get">
					<?php wp_nonce_field( 'shivs-poll-custom-fields' ); ?>
					<input type="hidden" name="page" value="shivs-polls"/>
					<input type="hidden" name="action" value="results"/>
					<input type="hidden" name="id" value="<?php echo $poll_id; ?>"/>
					<input type="hidden" name="oa_page_no"
						value="<?php echo $oa_page_no; ?>"/>
					<input type="hidden" name="cf_page_no"
						value="<?php echo $cf_page_no; ?>"/>
					<input type="hidden" name="oa_per_page"
						value="<?php echo $oa_per_page; ?>"/>
					<input type="hidden" name="results_order_by" value="<?php echo $results_order_by; ?>"/>
					<input type="hidden" name="results_order" value="<?php echo $results_order; ?>"/>
					<input type="hidden" name="soav" value="<?php echo $soav; ?>"/>

					<div class="tablenav top">
						<div class="alignleft actions">
							<select name="export">
								<option value="page"><?php _e( 'This Page', 'shivs_poll' ); ?></option>
								<option value="all"><?php _e( 'All Pages', 'shivs_poll' ); ?></option>
							</select> <input type="submit"
								value="<?php _e( 'Export', 'shivs_poll' ); ?>"
								class="button-secondary action" id="doaction" name="a">
							&nbsp;&nbsp;&nbsp; <label
								for="shivs-poll-custom-field-start-date-input"><?php _e( 'Start Date', 'shivs_poll' ); ?>
								:</label>
							<input id="shivs-poll-custom-field-start-date-input" type="text"
								name="cf_sdate" value="<?php echo $cf_sdate; ?>"/>&nbsp;&nbsp; <label
								for="shivs-poll-custom-field-end-date-input"><?php _e( 'End Date', 'shivs_poll' ); ?>
								:</label>
							<input id="shivs-poll-custom-field-end-date-input" type="text"
								name="cf_edate" value="<?php echo $cf_edate; ?>"/>&nbsp;&nbsp; <input
								value="<?php _e( 'Filter', 'shivs_poll' ); ?>" type="submit"
								name="a"/>
						</div>
						<div class="tablenav-pages one-page">
							<label for="shivs-poll-items-per-page" class="displaying-num"><?php _e( 'Items Per Page', 'shivs_poll' ); ?>
								:</label><input
								id="shivs-poll-items-per-page" type="text" name="cf_per_page"
								value="<?php echo $cf_per_page; ?>"/> <input name="a"
								value="<?php _e( 'Set', 'shivs_poll' ); ?>" type="submit"/>&nbsp;&nbsp;<span
								class="displaying-num"><?php echo count( $custom_fields_logs ); ?>
								/ <?php echo $total_custom_fields_logs; ?> items</span>
							<?php print $cf_pagination; ?>
						</div>
						<br class="clear">
					</div>
					<table class="wp-list-table widefat fixed" cellspacing="0">
						<thead>
							<tr>
								<th id="" class="column-answer" style="width: 5%" scope="col"><?php _e( '#', 'shivs_poll' ); ?></th>
								<?php
									foreach ( $poll_custom_fields as $custom_field ) {
										$column_custom_fields_ids [] = $custom_field ['id'];
									?>
									<th id="custom_field_<?php echo $custom_field['id']; ?>" class="column-custom-field" style="width:<?php echo intval( 80 / intval( $custom_fields_number ) ); ?>%" scope="col"><?php echo ucfirst( $custom_field['custom_field'] ); ?></th>
									<?php
									}
								?>
								<th id="" class="column-vote-id" style="width:20%"
									scope="col"><?php _e( 'Vote ID', 'shivs_poll' ); ?></th>
								<th id="" class="column-tr-id" style="width:15%"
									scope="col"><?php _e( 'Tracking ID', 'shivs_poll' ); ?></th>
								<th id="" class="column-vote-date" style="width:15%"
									scope="col"><?php _e( 'Vote Date', 'shivs_poll' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								if ( count( $custom_fields_logs ) > 0 ){
									$index = ( $cf_page_no - 1 ) * $cf_per_page + 1;
									foreach ( $custom_fields_logs as $logs ) {
									?>
									<tr>
										<td><?php echo $index; ?></td>
										<?php
											foreach ( $column_custom_fields_ids as $custom_field_id ) {
												$vote_log_values = array();
												$vote_logs       = explode( '<#!,>', $logs ['vote_log'] );
												if ( count( $vote_logs ) > 0 ){
													foreach ( $vote_logs as $vote_log ) {
														$temp                        = explode( '<#!->', $vote_log );
														$vote_log_values [$temp [1]] = stripslashes( $temp [0] );
													}
												}
											?>
											<td><?php echo isset( $vote_log_values[$custom_field_id] ) ? $vote_log_values[$custom_field_id] : ''; ?></td>
											<?php
											}
										?>
										<td><?php echo $logs['vote_id']; ?></td>
										<td><?php echo $logs['tr_id']; ?></td>
										<td><?php echo $logs['vote_date']; ?></td>
									</tr>
									<?php
										$index++;
									}
								}
							?>
						</tbody>
					</table>
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<?php print $cf_pagination; ?>
						</div>
						<br class="clear">
					</div>
				</form>
			</div>
			<div style="clear: both;"></div>
		</div>
		<?php
		}
		else {
		?>
		<h3><?php _e( 'Your poll doesn`t exist!', 'shivs_poll' ); ?></h3>
		<?php
		}
	}

	public function view_poll_custom_fields() {
		global $page, $action;
		$per_page = ( isset ( $_GET ['per_page'] ) ? intval( $_GET ['per_page'] ) : 100 );
		$page_no  = ( isset ( $_REQUEST ['page_no'] ) ? ( int )$_REQUEST ['page_no'] : 1 );
		$poll_id  = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
		$sdate    = ( isset ( $_GET ['sdate'] ) ? $_GET ['sdate'] : '' );
		$edate    = ( isset ( $_GET ['edate'] ) ? $_GET ['edate'] : '' );
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$poll_details                   = Shivs_Poll_Model::get_poll_from_database_by_id( $poll_id );
		$poll_custom_fields             = Shivs_Poll_Model::get_poll_customfields( $poll_id );
		$custom_fields_number           = count( $poll_custom_fields );
		$total_custom_fields_logs       = Shivs_Poll_Model::get_poll_total_customfields_logs( $poll_id, $sdate, $edate );
		$total_custom_fields_logs_pages = ceil( $total_custom_fields_logs / $per_page );
		if ( intval( $page_no ) > intval( $total_custom_fields_logs_pages ) ){
			$page_no = 1;
		}
		$custom_fields_logs = Shivs_Poll_Model::get_poll_customfields_logs( $poll_id, 'vote_id', 'asc', ( $page_no - 1 ) * $per_page, $per_page, $sdate, $edate );

		$column_custom_fields_ids = array();
		$args                     = array( 'base' => remove_query_arg( 'page_no', $_SERVER ['REQUEST_URI'] ) . '%_%', 'format' => '&page_no=%#%', 'total' => $total_custom_fields_logs_pages, 'current' => max( 1, $page_no ), 'prev_next' => true, 'prev_text' => __( '&laquo; Previous' ), 'next_text' => __( 'Next &raquo;' ) );
		$pagination               = paginate_links( $args );
		$_SERVER ['REQUEST_URI']  = remove_query_arg( array( 'action' ), $_SERVER ['REQUEST_URI'] );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Custom Fields', 'shivs_poll' ); ?><a class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls' ), remove_query_arg( array( 'action', 'id' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) ) ); ?>"><?php _e( 'All Shivs Polls', 'shivs_poll' ); ?></a>
		</h2>
		<?php
			if ( $poll_details ){
				if ( $poll_custom_fields ){
				?>
				<h3>Name: <?php echo esc_html( stripslashes( $poll_details['name'] ) ) ?></h3>
				<h4>Question: <?php echo esc_html( stripslashes( $poll_details['question'] ) ) ?></h4>
				<form method="get">
					<?php wp_nonce_field( 'shivs-poll-custom-fields' ); ?>
					<input type="hidden" name="page" value="shivs-polls"/>
					<input type="hidden" name="action" value="custom-fields"/>
					<input type="hidden" name="id" value="<?php echo $poll_id; ?>"/>
					<input type="hidden" name="page_no" value="<?php echo $page_no; ?>"/>
					<table cellspacing="5" align=" center">
						<tbody>
							<tr>
								<th><label for="shivs-poll-custom-field-start-date-input"><?php _e( 'Start Date', 'shivs_poll' ); ?>
										:</label>
								</th>
								<td><input id="shivs-poll-custom-field-start-date-input" type="text"
										name="sdate" value="<?php echo $sdate; ?>"/></td>
							</tr>
							<tr>
								<th><label for="shivs-poll-custom-field-end-date-input"><?php _e( 'End Date', 'shivs_poll' ); ?>
										:</label>
								</th>
								<td><input id="shivs-poll-custom-field-end-date-input" type="text"
										name="edate" value="<?php echo $edate; ?>"/></td>
							</tr>
							<tr>
								<th colspan="2"><input value="<?php _e( 'Filter', 'shivs_poll' ); ?>"
										type="submit" name="a"/></th>
							</tr>
						</tbody>
					</table>
					<div class="tablenav top">
						<div class="alignleft actions">
							<select name="export">
								<option selected="selected" value=""><?php _e( 'Do Not Export', 'shivs_poll' ); ?></option>
								<option value="page"><?php _e( 'This Page', 'shivs_poll' ); ?></option>
								<option value="all"><?php _e( 'All Pages', 'shivs_poll' ); ?></option>
							</select> <input type="submit"
								value="<?php _e( 'Export', 'shivs_poll' ); ?>"
								class="button-secondary action" id="doaction" name="a">
						</div>
						<div class="tablenav-pages one-page">
							<label for="shivs-poll-items-per-page" class="displaying-num"><?php _e( 'Items Per Page', 'shivs_poll' ); ?>
								:</label><input
								id="shivs-poll-items-per-page" type="text" name="per_page"
								value="<?php echo $per_page; ?>"/> <input name="a"
								value="<?php _e( 'Set', 'shivs_poll' ); ?>" type="submit"/>&nbsp;&nbsp;<span
								class="displaying-num"><?php echo count( $custom_fields_logs ); ?>
								/ <?php echo $total_custom_fields_logs; ?> items</span>
							<?php print $pagination; ?>
						</div>
						<br class="clear">
					</div>
					<table class="wp-list-table widefat fixed" cellspacing="0">
						<thead>
							<tr>
								<th id="" class="column-answer" style="width: 5%" scope="col"><?php _e( '#', 'shivs_poll' ); ?></th>
								<?php
									foreach ( $poll_custom_fields as $custom_field ) {
										$column_custom_fields_ids [] = $custom_field ['id'];
									?>
									<th id="custom_field_<?php echo $custom_field['id']; ?>" class="column-custom-field" style="width:<?php echo intval( 80 / intval( $custom_fields_number ) ); ?>%" scope="col"><?php echo ucfirst( $custom_field['custom_field'] ); ?></th>
									<?php
									}
								?>
								<th id="" class="column-vote-date" style="width: 15%"
									scope="col"><?php _e( 'Vote Date', 'shivs_poll' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
								if ( count( $custom_fields_logs ) > 0 ){
									$index = ( $page_no - 1 ) * $per_page + 1;
									foreach ( $custom_fields_logs as $logs ) {
									?>
									<tr>
										<td><?php echo $index; ?></td>
										<?php
											foreach ( $column_custom_fields_ids as $custom_field_id ) {
												$vote_log_values = array();
												$vote_logs       = explode( '<#!,>', $logs ['vote_log'] );
												if ( count( $vote_logs ) > 0 ){
													foreach ( $vote_logs as $vote_log ) {
														$temp                        = explode( '<#!->', $vote_log );
														$vote_log_values [$temp [1]] = stripslashes( $temp [0] );
													}
												}
											?>
											<td><?php echo isset( $vote_log_values[$custom_field_id] ) ? $vote_log_values[$custom_field_id] : ''; ?></td>
											<?php
											}
										?>
										<td><?php echo $logs['vote_date']; ?></td>
									</tr>
									<?php
										$index++;
									}
								}
							?>
						</tbody>
					</table>
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<?php print $pagination; ?>
						</div>
						<br class="clear">
					</div>
				</form>
			</div>
			<?php
			}
			else {
			?>
			<h3><?php _e( 'This poll doesn\'t have set custom fields!', 'shivs_poll' ); ?></h3>
			<?php
			}
		}
		else {
		?>
		<h3><?php _e( 'Your Poll doesn`t exist!', 'shivs_poll' ); ?></h3>
		<?php
		}
	}

	public function view_shivs_poll_templates() {
		global $page, $action, $orderby, $order, $current_user;
		$orderby                    = ( empty ( $orderby ) ) ? 'last_modified' : $orderby;
		$order                      = ( empty ( $order ) ) ? 'desc' : $order;
		$order_direction            = array( 'id' => 'asc', 'name' => 'asc', 'last_modified' => 'desc' );
		$order_direction [$orderby] = ( 'desc' == $order ) ? 'asc' : 'desc';

		$order_direction_reverse            = array( 'id' => 'desc', 'name' => 'desc', 'last_modified' => 'desc' );
		$order_direction_reverse [$orderby] = ( 'desc' == $order ) ? 'desc' : 'asc';

		$order_sortable            = array( 'id' => 'sortable', 'name' => 'sortable', 'last_modified' => 'sortable' );
		$order_sortable [$orderby] = 'sorted';
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$search             = array( 'fields' => array( 'name', 'last_modified' ), 'value' => isset ( $_REQUEST ['s'] ) ? $_REQUEST ['s'] : '' );
		$shivs_poll_templates = Shivs_Poll_Model::get_shivs_poll_templates_search( $orderby, $order, $search );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Poll Templates', 'shivs_poll' ); ?><a
				class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-templates', 'action' => 'add-new', 'id' => false, 'deleted' => false, 'cloned' => false ) ) ); ?>"><?php _e( 'Add New', 'shivs_poll' ); ?></a>
		</h2>
		<?php
			if ( isset ( $_REQUEST ['deleted'] ) ){
				echo '<div id="message" class="updated"><p>';
				$deleted = ( int )$_REQUEST ['deleted'];
				printf( _n( '%s Poll template deleted.', '%s Poll templates deleted.', $deleted ), $deleted );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<?php
			if ( isset ( $_REQUEST ['cloned'] ) ){
				echo '<div id="message" class="updated"><p>';
				$cloned = ( int )$_REQUEST ['cloned'];
				printf( _n( '%s Poll template cloned.', '%s Poll templates cloned.', $cloned ), $cloned );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'cloned' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<form action="" method="get">
			<?php wp_nonce_field( 'shivs-poll-templates' ); ?>
			<input type="hidden" name="order" value="<?php echo $order ?>"/>
			<input type="hidden" name="orderby" value="<?php echo $orderby ?>"/>
			<input type="hidden" name="page" value="shivs-polls-templates"/>
			<p class="search-box">
				<label class="screen-reader-text" for="shivs-poll-search-input"><?php _e( 'Search Polls', 'shivs_poll' ) ?></label>
				<input id="shivs-poll-search-input" type="search"
					value="<?php if ( isset( $_REQUEST['s'] ) ): echo esc_html( stripslashes( $_REQUEST['s'] ) ); endif; ?>"
					name="s"/> <input id="search-submit" class="button" type="submit"
					value="<?php _e( 'Search Polls', 'shivs_poll' ); ?>" name=""/>
			</p>
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="action">
						<option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'shivs_poll' ); ?></option>
						<option value="delete"><?php _e( 'Delete', 'shivs_poll' ); ?></option>
						<option value="clone"><?php _e( 'Clone', 'shivs_poll' ); ?></option>
					</select> <input type="submit"
						value="<?php _e( 'Apply', 'shivs_poll' ); ?>"
						class="button-secondary action" id="doaction" name="">
				</div>
				<br class="clear">
			</div>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="cb" class="manage-column column-cb check-column"
							scope="col" style="width: 2%;"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['id'] ?> <?php echo $order_direction_reverse['id'] ?>"
							style="width: 10%;" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'id', 'order' => $order_direction['id'] ) ) ); ?>">
								<span><?php _e( 'ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="width: 38%;" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="template_author" class="manage-column" style="width: 10%;"
							scope="col"><span><?php _e( 'Author', 'shivs_poll' ); ?></span></th>
						<th id="last_modified"
							class="manage-column <?php echo $order_sortable['last_modified'] ?> <?php echo $order_direction_reverse['last_modified'] ?>"
							style="width: 40%;" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'last_modified', 'order' => $order_direction['last_modified'] ) ) ); ?>">
								<span><?php _e( 'Last Modified', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</thead>
				<?php
					if ( count( $shivs_poll_templates ) > 0 ){
						foreach ( $shivs_poll_templates as $template ) {
						?>
						<tbody id="the-list">
							<tr valign="middle" class="alternate"
								id="shivs-poll-<?php echo $template['id']; ?>">
								<th class="check-column" scope="row">
									<?php if ( ( $this->current_user_can( 'delete_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_templates' ) ) ){ ?>
										<input type="checkbox" value="<?php echo $template['id']; ?>"
											name="templatecheck[]">
										<?php } ?>
								</th>
								<td><strong>
										<?php if (( $this->current_user_can( 'edit_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls_templates' ) )) { ?>
											<a title="<?php echo $template['id']; ?>"
												href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $template['id'] ) ) ); ?>"
												class="row-title">
												<?php } ?>
											<?php echo $template['id']; ?>
											<?php if (( $this->current_user_can( 'edit_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls_templates' ) )) { ?>
											</a>
											<?php } ?>
									</strong><br>
									<div class="row-actions">
										<?php if ( ( $this->current_user_can( 'edit_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls_templates' ) ) ){ ?>
											<span class="edit"><a
													href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $template['id'] ) ) ); ?>"><?php _e( 'Edit', 'shivs_poll' ) ?></a>
												| </span>
											<?php } ?>
										<?php if ( ( $this->current_user_can( 'delete_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_templates' ) ) ){ ?>
											<span class="delete"><a
													onclick="if ( confirm( '<?php echo __( "You are about to delete this poll template", 'shivs_poll' ) . ": \'" . esc_html( $template['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to delete', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
													href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $template['id'] ) ), 'shivs-poll-templates' ); ?>"
													class="submitdelete"><?php _e( 'Delete', 'shivs_poll' ) ?></a> | </span>
											<?php } ?>
										<?php if ( ( $this->current_user_can( 'clone_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'clone_polls_templates' ) ) ){ ?>
											<span class="clone"><a
													onclick="if ( confirm( '<?php echo __( "You are about to clone this poll template", 'shivs_poll' ) . ": \'" . esc_html( $template['name'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to clone', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
													href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'clone', 'id' => $template['id'] ) ), 'shivs-poll-templates' ); ?>"
													class="submitdelete"><?php _e( 'Clone', 'shivs_poll' ) ?></a></span>
											<?php } ?>
									</div></td>
								<td><strong>
										<?php if (( $this->current_user_can( 'edit_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls_templates' ) )) { ?>
											<a
												title="<?php echo esc_html( stripslashes( $template['name'] ) ); ?>"
												href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $template['id'] ) ) ); ?>"
												class="row-title">
												<?php } ?>
											<?php echo esc_html( stripslashes( $template['name'] ) ); ?>
											<?php if (( $this->current_user_can( 'edit_own_polls_templates' ) && $template['template_author'] == $current_user->ID ) || ( $this->current_user_can( 'edit_polls_templates' ) )) { ?>
											</a>
											<?php } ?>
									</strong><br></td>
								<td>
									<?php
										$user_info = get_userdata( $template ['template_author'] );
										if ( $user_info ){
											echo esc_html( stripslashes( $user_info->user_login ) );
										}
										else {
											echo '';
										}
									?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $template['last_modified'] ) ); ?>
								</td>
							</tr>
						</tbody>
						<?php
						}
					}
					else {
					?>
					<tbody id="the-list">
						<tr valign="middle" class="alternate" id="shivs-poll-<?php ?>">
							<td id="empty-set" colspan="5">
								<h3 style="margin-bottom: 0px;"><?php _e( " You haven't used our template editor to create any shivs poll templates!", 'shivs_poll' ); ?> </h3>
								<p style="margin-bottom: 20px;"><?php _e( "Please create your poll template first.", 'shivs_poll' ); ?></p>
								<a class="button-primary"
									href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-templates', 'action' => 'add-new', 'id' => false, 'deleted' => false, 'cloned' => false ) ) ); ?>"><?php _e( "Create a poll template now", 'shivs_poll' ); ?></a>
								<br/> <br/>
							</td>
						</tr>
					</tbody>
					<?php
					}
				?>

				<tfoot>
					<tr>
						<th id="cb" class="manage-column column-cb check-column" style=""
							scope="col"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['id'] ?> <?php echo $order_direction_reverse['id'] ?>"
							style="" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'id', 'order' => $order_direction['id'] ) ) ); ?>">
								<span><?php _e( 'ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="template_author" class="manage-column" style="width: 10%;"
							scope="col"><span><?php _e( 'Author', 'shivs_poll' ); ?></span></th>
						<th id="question"
							class="manage-column <?php echo $order_sortable['last_modified'] ?> <?php echo $order_direction_reverse['last_modified'] ?>"
							style="" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'last_modified', 'order' => $order_direction['last_modified'] ) ) ); ?>">
								<span><?php _e( 'Last Modified', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
	<?php
	}

	public function view_shivs_poll_logs() {
		global $wpdb, $page, $action, $orderby, $order, $current_user;
		$per_page                   = ( isset ( $_GET ['per_page'] ) ? intval( $_GET ['per_page'] ) : 100 );
		$page_no                    = isset ( $_REQUEST ['page_no'] ) ? ( int )$_REQUEST ['page_no'] : 1;
		$orderby                    = ( empty ( $orderby ) ) ? 'name' : $orderby;
		$order_direction            = array( 'vote_id' => 'asc', 'name' => 'asc', 'answer' => 'asc', 'user_nicename' => 'asc', 'user_email' => 'asc', 'user_from' => 'asc', 'tr_id' => 'asc', 'ip' => 'asc', 'vote_date' => 'asc' );
		$order_direction [$orderby] = ( 'desc' == $order ) ? 'asc' : 'desc';

		$order_direction_reverse            = array( 'vote_id' => 'desc', 'name' => 'desc', 'answer' => 'desc', 'user_nicename' => 'desc', 'user_email' => 'desc', 'user_from' => 'desc', 'tr_id' => 'desc', 'ip' => 'desc', 'vote_date' => 'desc' );
		$order_direction_reverse [$orderby] = ( 'desc' == $order ) ? 'desc' : 'asc';

		$order_sortable            = array( 'vote_id' => 'sortable', 'name' => 'sortable', 'answer' => 'sortable', 'user_nicename' => 'sortable', 'user_email' => 'sortable', 'user_from' => 'sortable', 'tr_id' => 'sortable', 'ip' => 'sortable', 'vote_date' => 'sortable' );
		$order_sortable [$orderby] = 'sorted';
		$poll_id                   = isset ( $_REQUEST ['poll_id'] ) ? ( int )$_REQUEST ['poll_id'] : NULL;

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );

		$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
		if ( $this->current_user_can( 'view_own_polls_logs' ) && $poll_id ){
			if ( $poll_author != $current_user->ID && !$this->current_user_can( 'view_polls_logs' ) ){
				wp_die( __( 'You are not allowed to access this section.', 'shivs_poll' ) );
			}
		}
		elseif ( !$this->current_user_can( 'view_polls_logs' ) ) {
			wp_die( __( 'You are not allowed to access this section.', 'shivs_poll' ) );
		}
		$log_sdate = ( isset ( $_GET ['log_sdate'] ) ? $_GET ['log_sdate'] : '' );
		$log_edate = ( isset ( $_GET ['log_edate'] ) ? $_GET ['log_edate'] : '' );
		$group_by  = ( isset ( $_GET ['group_by'] ) ? $_GET ['group_by'] : 'vote' );

		$shivs_polls = Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
		if ( $group_by == 'vote' ){
			$search = array( 'fields' => array( 'name', 'ip', 'user_nicename', 'user_email', 'user_from', 'tr_id' ), 'value' => isset ( $_REQUEST ['s'] ) ? trim( $_REQUEST ['s'] ) : '' );
		}
		else {
			$search = array( 'fields' => array( 'name', 'answer', 'ip', 'other_answer_value', 'user_nicename', 'user_from', 'tr_id', 'user_email' ), 'value' => isset ( $_REQUEST ['s'] ) ? trim( $_REQUEST ['s'] ) : '' );
		}
		$filter = array( 'field' => NULL, 'value' => NULL, 'operator' => '=' );
		if ( $group_by == 'vote' ){
			$total_logs = Shivs_Poll_Model::get_total_group_logs_filter_search( $search, $poll_id, $log_sdate, $log_edate );
		}
		else {
			$total_logs = Shivs_Poll_Model::get_total_logs_filter_search( $search, $poll_id, $log_sdate, $log_edate );
		}
		$total_logs_pages = ceil( $total_logs / $per_page );
		if ( intval( $page_no ) > intval( $total_logs_pages ) ){
			$page_no = 1;
		}
		if ( $group_by == 'vote' ){
			$logs = Shivs_Poll_Model::get_group_logs_filter_search( $orderby, $order, $search, $poll_id, ( $page_no - 1 ) * $per_page, $per_page, $log_sdate, $log_edate );
		}
		else {
			$logs = Shivs_Poll_Model::get_logs_filter_search( $orderby, $order, $search, $poll_id, ( $page_no - 1 ) * $per_page, $per_page, $log_sdate, $log_edate );
		}

		$args                    = array( 'base' => remove_query_arg( 'page_no', $_SERVER ['REQUEST_URI'] ) . '%_%', 'format' => '&page_no=%#%', 'total' => $total_logs_pages, 'current' => max( 1, $page_no ), 'prev_next' => true, 'prev_text' => __( '&laquo; Previous' ), 'next_text' => __( 'Next &raquo;' ) );
		$pagination              = paginate_links( $args );
		$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'action' ), $_SERVER ['REQUEST_URI'] );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Poll Logs', 'shivs_poll' ); ?></h2>
		<?php
			if ( isset ( $_REQUEST ['deleted'] ) ){
				echo '<div id="message" class="updated"><p>';
				$deleted = ( int )$_REQUEST ['deleted'];
				printf( _n( '%s Poll Log deleted.', '%s Poll Logs deleted.', $deleted ), $deleted );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<form method="get">
			<?php wp_nonce_field( 'shivs-poll-logs' ); ?>
			<input type="hidden" name="order" value="<?php echo $order ?>"/>
			<input type="hidden" name="orderby" value="<?php echo $orderby ?>"/>
			<input type="hidden" name="page" value="shivs-polls-logs"/>
			<p class="search-box">
				<label class="screen-reader-text" for="shivs-poll-search-input"><?php _e( 'Search Poll Logs', 'shivs_poll' ) ?></label>
				<input id="shivs-poll-search-input" type="search"
					value="<?php if ( isset( $_REQUEST['s'] ) ): echo esc_html( stripslashes( $_REQUEST['s'] ) ); endif; ?>"
					name="s"/> <input id="search-submit" class="button" type="submit"
					value="<?php _e( 'Search Poll Logs', 'shivs_poll' ); ?>" name=""/>
			</p>
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="group_by">
						<option <?php echo selected( $group_by, 'answer' ); ?>
							value="answer"><?php _e( 'Group Logs By Answer', 'shivs_poll' ); ?></option>
						<option <?php echo selected( $group_by, 'vote' ); ?> value="vote"><?php _e( 'Group Logs By Vote', 'shivs_poll' ); ?></option>
					</select> <input type="submit"
						value="<?php _e( 'Group', 'shivs_poll' ); ?>"
						class="button-secondary action" id="doaction" name=""/>
				</div>
			</div>
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="action">
						<option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'shivs_poll' ); ?></option>
						<?php if ( $group_by == 'vote' ){ ?>
							<option value="delete_group"><?php _e( 'Delete', 'shivs_poll' ); ?></option>
							<?php

							}
							else {
							?>
							<option value="delete"><?php _e( 'Delete', 'shivs_poll' ); ?></option>
							<?php } ?>
					</select> <input type="submit"
						value="<?php _e( 'Apply', 'shivs_poll' ); ?>"
						class="button-secondary action" id="doaction" name="">&nbsp;|&nbsp;
				</div>
				<div class="alignleft actions">
					<select name="poll_id">
						<option value=""><?php _e( 'All Logs', 'shivs_poll' ); ?></option>
						<?php
							if ( count( $shivs_polls ) > 0 ){
								foreach ( $shivs_polls as $shivs_poll ) {
								?>
								<option <?php echo selected( $poll_id, $shivs_poll['id'] ); ?>
									value="<?php echo $shivs_poll['id'] ?>"><?php echo $shivs_poll['name'] ?></option>
								<?php
								}
							}
						?>
					</select> <label for="shivs-poll-logs-start-date-input"><?php _e( 'Start Date', 'shivs_poll' ); ?>
						:</label>
					<input id="shivs-poll-logs-start-date-input" type="text"
						name="log_sdate" value="<?php echo $log_sdate; ?>"/>&nbsp;&nbsp; <label
						for="shivs-poll-logs-end-date-input"><?php _e( 'End Date', 'shivs_poll' ); ?>:</label>
					<input id="shivs-poll-logs-end-date-input" type="text"
						name="log_edate" value="<?php echo $log_edate; ?>"/>&nbsp;&nbsp; <input
						type="submit" value="<?php _e( 'Filter', 'shivs_poll' ); ?>"
						class="button-secondary" id="post-query-submit" name="">&nbsp;|&nbsp;
				</div>
				<div class="alignleft actions">
					<select name="export">
						<option value="page"><?php _e( 'This Page', 'shivs_poll' ); ?></option>
						<option value="all"><?php _e( 'All Pages', 'shivs_poll' ); ?></option>
					</select> <input type="submit"
						value="<?php _e( 'Export', 'shivs_poll' ); ?>"
						class="button-secondary action" id="doaction" name="a">
					&nbsp;&nbsp;&nbsp;

				</div>
				<div class="tablenav-pages one-page">
					<label for="shivs-poll-items-per-page" class="displaying-num"><?php _e( 'Items Per Page', 'shivs_poll' ); ?>
						:</label>
					<input id="shivs-poll-items-per-page" type="text" name="per_page"
						value="<?php echo $per_page; ?>"/> <input name="a"
						value="<?php _e( 'Set', 'shivs_poll' ); ?>" type="submit"/>&nbsp;&nbsp;
					<span class="displaying-num"><?php echo count( $logs ); ?> / <?php echo $total_logs; ?>
						logs</span>
					<?php print $pagination; ?>
				</div>
				<br class="clear">
			</div>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="cb" class="manage-column column-cb check-column"
							style="width: 2%;" scope="col"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['vote_id'] ?> <?php echo $order_direction_reverse['vote_id'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'vote_id', 'order' => $order_direction['vote_id'] ) ) ); ?>">
								<span><?php _e( 'Vote ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="width: 20%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Poll Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="total_votes"
							class="manage-column <?php echo $order_sortable['answer'] ?> <?php echo $order_direction_reverse['answer'] ?>"
							style="width: 18%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'answer', 'order' => $order_direction['answer'] ) ) ); ?>">
								<span><?php _e( 'Answer', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="user_from"
							class="manage-column <?php echo $order_sortable['user_from'] ?> <?php echo $order_direction_reverse['user_from'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_from', 'order' => $order_direction['user_from'] ) ) ); ?>">
								<span><?php _e( 'User Type', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="user_nicename"
							class="manage-column <?php echo $order_sortable['user_nicename'] ?> <?php echo $order_direction_reverse['user_nicename'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_nicename', 'order' => $order_direction['user_nicename'] ) ) ); ?>">
								<span><?php _e( 'User', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="user_email"
							class="manage-column <?php echo $order_sortable['user_email'] ?> <?php echo $order_direction_reverse['user_email'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_email', 'order' => $order_direction['user_email'] ) ) ); ?>">
								<span><?php _e( 'User Email', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="tr_id"
							class="manage-column <?php echo $order_sortable['tr_id'] ?> <?php echo $order_direction_reverse['tr_id'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'tr_id', 'order' => $order_direction['tr_id'] ) ) ); ?>">
								<span><?php _e( 'Tracking ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="ip"
							class="manage-column <?php echo $order_sortable['ip'] ?> <?php echo $order_direction_reverse['ip'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'ip', 'order' => $order_direction['ip'] ) ) ); ?>">
								<span><?php _e( 'Ip', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="vote_date"
							class="manage-column <?php echo $order_sortable['vote_date'] ?> <?php echo $order_direction_reverse['vote_date'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'vote_date', 'order' => $order_direction['vote_date'] ) ) ); ?>">
								<span><?php _e( 'Vote Date', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th id="footer_cb" class="manage-column column-cb check-column"
							style="width: 2%;" scope="col"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['vote_id'] ?> <?php echo $order_direction_reverse['vote_id'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'vote_id', 'order' => $order_direction['vote_id'] ) ) ); ?>">
								<span><?php _e( 'Vote ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="width: 20%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Poll Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_answer"
							class="manage-column <?php echo $order_sortable['answer'] ?> <?php echo $order_direction_reverse['answer'] ?>"
							style="width: 18%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'answer', 'order' => $order_direction['answer'] ) ) ); ?>">
								<span><?php _e( 'Answer', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_user_from"
							class="manage-column <?php echo $order_sortable['user_from'] ?> <?php echo $order_direction_reverse['user_from'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_from', 'order' => $order_direction['user_from'] ) ) ); ?>">
								<span><?php _e( 'User Type', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_user_nicename"
							class="manage-column <?php echo $order_sortable['user_nicename'] ?> <?php echo $order_direction_reverse['user_nicename'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_nicename', 'order' => $order_direction['user_nicename'] ) ) ); ?>">
								<span><?php _e( 'User', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_user_email"
							class="manage-column <?php echo $order_sortable['user_email'] ?> <?php echo $order_direction_reverse['user_email'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'user_email', 'order' => $order_direction['user_email'] ) ) ); ?>">
								<span><?php _e( 'User Email', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_tr_id"
							class="manage-column <?php echo $order_sortable['tr_id'] ?> <?php echo $order_direction_reverse['tr_id'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'tr_id', 'order' => $order_direction['tr_id'] ) ) ); ?>">
								<span><?php _e( 'Tracking ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_ip"
							class="manage-column <?php echo $order_sortable['ip'] ?> <?php echo $order_direction_reverse['ip'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'ip', 'order' => $order_direction['ip'] ) ) ); ?>">
								<span><?php _e( 'Ip', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="footer_vote_date"
							class="manage-column <?php echo $order_sortable['vote_date'] ?> <?php echo $order_direction_reverse['vote_date'] ?>"
							style="width: 10%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'vote_date', 'order' => $order_direction['vote_date'] ) ) ); ?>">
								<span><?php _e( 'Vote Date', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</tfoot>
				<?php
					if ( count( $logs ) > 0 ){
						foreach ( $logs as $log ) {
						?>
						<tbody id="the-list">
							<tr valign="middle" class="alternate"
								id="shivs-poll-log<?php echo $log['id']; ?>">
								<th class="check-column" scope="row">
									<?php if ( $group_by == 'vote' ){ ?>
										<?php
											$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_vote_id( 'poll_id', $log ['vote_id'] );
											$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
											if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
											?>
											<input type="checkbox"
												value="<?php echo $log['vote_id']; ?>" name="shivspolllogscheck[]">
											<?php } ?>
										<?php

										}
										else {
										?>
										<?php
											$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_id( 'poll_id', $log ['id'] );
											$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
											if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
											?>
											<input type="checkbox" value="<?php echo $log['id']; ?>"
												name="shivspolllogscheck[]">
											<?php } ?>
										<?php } ?>
								</th>
								<td><strong><?php echo $log['vote_id']; ?></strong><br></td>
								<td><strong><?php if ( $log['name'] != '' ){
												echo esc_html( stripslashes( $log['name'] ) );
											}
											else {
												echo esc_html( stripslashes( Shivs_Poll_Model::get_poll_field_from_database_by_id( 'name', $poll_id ) ) );
									} ?></strong><br>
									<div class="row-actions">
										<?php if ( $group_by == 'vote' ){ ?>
											<?php
												$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_vote_id( 'poll_id', $log ['vote_id'] );
												$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
												if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
												?>
												<span class="delete"><a
														onclick="if ( confirm( '<?php echo __( "You are about to delete this vote log", 'shivs_poll' ) . " \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to delete', 'shivs_poll' ); ?>'  ) ) { return true;}return false;"
														href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete_group', 'id' => $log['vote_id'] ) ), 'shivs-poll-logs-delete' ); ?>"
														class="submitdelete"><?php _e( 'Delete', 'shivs_poll' ) ?></a></span>
												<?php } ?>
											<?php

											}
											else {
											?>
											<?php
												$poll_id     = Shivs_Poll_Model::get_poll_log_field_from_database_by_id( 'poll_id', $log ['id'] );
												$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
												if ( ( $this->current_user_can( 'delete_own_polls_logs' ) && $poll_author == $current_user->ID ) || ( $this->current_user_can( 'delete_polls_logs' ) ) ){
												?>
												<span class="delete"><a
														onclick="if ( confirm( '<?php echo __( "You are about to delete this poll log", 'shivs_poll' ) . ": \'" . esc_html( $log['id'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to delete', 'shivs_poll' ); ?>'  ) ) { return true;}return false;"
														href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $log['id'] ) ), 'shivs-poll-logs-delete' ); ?>"
														class="submitdelete"><?php _e( 'Delete', 'shivs_poll' ) ?></a></span>
												<?php } ?>
											<?php } ?>
									</div></td>
								<td>
									<?php echo ( 'Other' == $log['answer'] ) ? 'Other - ' . esc_html( stripslashes( $log['other_answer_value'] ) ) : esc_html( stripslashes( $log['answer'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['user_from'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['user_nicename'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['user_email'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['tr_id'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['ip'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $log['vote_date'] ) ); ?>
								</td>
							</tr>
						</tbody>
						<?php
						}
					}
				?>
			</table>
			<div class="tablenav bottom">
				<div class="tablenav-pages one-page">
					<span class="displaying-num"><?php echo count( $logs ); ?> / <?php echo $total_logs; ?>
						logs</span>
					<?php print $pagination; ?>
				</div>
			</div>
		</form>
	</div>
	<?php
	}

	public function view_shivs_poll_bans() {
		global $wpdb, $page, $action, $orderby, $order;
		$per_page                   = ( isset ( $_GET ['per_page'] ) ? intval( $_GET ['per_page'] ) : 100 );
		$page_no                    = isset ( $_REQUEST ['page_no'] ) ? ( int )$_REQUEST ['page_no'] : 1;
		$orderby                    = ( empty ( $orderby ) ) ? 'name' : $orderby;
		$order_direction            = array( 'id' => 'asc', 'name' => 'asc', 'type' => 'asc', 'value' => 'asc' );
		$order_direction [$orderby] = ( 'desc' == $order ) ? 'asc' : 'desc';

		$order_direction_reverse            = array( 'id' => 'desc', 'name' => 'desc', 'type' => 'desc', 'value' => 'desc' );
		$order_direction_reverse [$orderby] = ( 'desc' == $order ) ? 'desc' : 'asc';

		$order_sortable            = array( 'id' => 'sortable', 'name' => 'sortable', 'type' => 'sortable', 'value' => 'sortable' );
		$order_sortable [$orderby] = 'sorted';
		$poll_id                   = isset ( $_REQUEST ['poll_id'] ) ? ( int )$_REQUEST ['poll_id'] : NULL;
		$type                      = isset ( $_REQUEST ['type'] ) ? $_REQUEST ['type'] : NULL;
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$shivs_polls        = Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
		$search           = array( 'fields' => array( $wpdb->shivs_poll_bans . '.value' ), 'value' => isset ( $_REQUEST ['s'] ) ? trim( $_REQUEST ['s'] ) : '' );
		$total_bans       = count( Shivs_Poll_Model::get_bans_filter_search( $orderby, $order, $search, $type, $poll_id ) );
		$total_bans_pages = ceil( $total_bans / $per_page );
		if ( intval( $page_no ) > intval( $total_bans_pages ) ){
			$page_no = 1;
		}
		$bans = Shivs_Poll_Model::get_bans_filter_search( $orderby, $order, $search, $type, $poll_id, ( $page_no - 1 ) * $per_page, $per_page );

		$args                    = array( 'base' => remove_query_arg( 'page_no', $_SERVER ['REQUEST_URI'] ) . '%_%', 'format' => '&page_no=%#%', 'total' => $total_bans_pages, 'current' => max( 1, $page_no ), 'prev_next' => true, 'prev_text' => __( '&laquo; Previous' ), 'next_text' => __( 'Next &raquo;' ) );
		$pagination              = paginate_links( $args );
		$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'action' ), $_SERVER ['REQUEST_URI'] );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Bans', 'shivs_poll' ); ?> <a
				href="javascript:void(0);" class="add-new-h2"
				id="shivs-poll-add-new-ban"><?php _e( 'Add New', 'shivs_poll' ); ?></a>
		</h2>
		<?php
			if ( isset ( $_REQUEST ['deleted'] ) ){
				echo '<div id="message" class="updated"><p>';
				$deleted = ( int )$_REQUEST ['deleted'];
				printf( _n( '%s Poll Ban deleted!', '%s Poll Bans deleted!', $deleted ), $deleted );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<?php
			if ( isset ( $_REQUEST ['bans-added'] ) ){
				echo '<div id="message" class="updated"><p>';
				$added = ( int )$_REQUEST ['bans-added'];
				printf( _n( '%s Poll Ban added!', '%s Poll Bans added!', $added ), $added );
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'bans-added' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<?php
			if ( isset ( $_REQUEST ['bans-error'] ) ){
				echo '<div id="message" class="error"><p>';
				print $_REQUEST ['bans-error'];
				echo '</p></div>';
				$_SERVER ['REQUEST_URI'] = remove_query_arg( array( 'bans-error' ), $_SERVER ['REQUEST_URI'] );
			}
		?>
		<div id='shivs-poll-add-ban-div' style="display: none;">
			<p><?php _e( 'Ban IP, Username or Email', 'shivs_poll' ); ?></p>
			<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
				<input type="hidden" name="page" value="shivs-polls-bans"/>
				<input type="hidden" name="action" value="add-ban"/>
				<?php wp_nonce_field( 'shivs-poll-add-ban' ); ?>
				<table class="form-table">
					<tbody>
						<tr class="form-field form-required">
							<th scope="row"><label for="ban-poll-id"><?php _e( 'Poll', 'shivs_poll' ); ?> <span
										class="description">(required)</span></label></th>
							<td><select id="ban-poll-id" name="ban_poll_id">
									<option value="0"><?php _e( 'Bans For All Polls', 'shivs_poll' ); ?></option>
									<?php
										if ( count( $shivs_polls ) > 0 ){
											foreach ( $shivs_polls as $shivs_poll ) {
											?>
											<option value="<?php echo $shivs_poll['id'] ?>"><?php echo $shivs_poll['name'] ?></option>
											<?php
											}
										}
									?>
								</select></td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="shivs-poll-ban-type"><?php _e( 'Type', 'shivs_poll' ); ?> <span
										class="description">(required)</span></label></th>
							<td><select id="shivs-poll-ban-type" name="ban_type">
								<option value=""><?php _e( 'Choose Ban Type', 'shivs_poll' ); ?></option>
								<option value="ip"><?php _e( 'IP', 'shivs_poll' ); ?></option>
								<option value="username"><?php _e( 'Username', 'shivs_poll' ); ?></option>
								<option value="email"><?php _e( 'Email', 'shivs_poll' ); ?></option></td>
						</tr>
						<tr class="form-field form-required">
							<th scope="row"><label for="shivs-poll-ban-value"><?php _e( 'Value', 'shivs_poll' ); ?>
									<span
										class="description">(required)</span><br> <small><i><?php _e( 'One Value Per Line', 'shivs_poll' ); ?></i></small></label></th>
							<td><textarea rows="5" cols="20" id="shivs-poll-ban-value"
									name="ban_value"></textarea></td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Add Ban', 'shivs_poll' ); ?> "
						class="button-primary"> <input id="shivs-poll-add-ban-close"
						type="button" value="<?php _e( 'Close', 'shivs_poll' ); ?> "
						class="button-primary">

				</p>
			</form>
		</div>
		<br/>
		<form method="get">
			<?php wp_nonce_field( 'shivs-poll-bans' ); ?>
			<input type="hidden" name="order" value="<?php echo $order ?>"/>
			<input type="hidden" name="orderby" value="<?php echo $orderby ?>"/>
			<input type="hidden" name="page" value="shivs-polls-bans"/>
			<p class="search-box">
				<label class="screen-reader-text" for="shivs-poll-search-input"><?php _e( 'Search Poll Bans', 'shivs_poll' ) ?></label>
				<input id="shivs-poll-search-input" type="search"
					value="<?php if ( isset( $_REQUEST['s'] ) ): echo esc_html( stripslashes( $_REQUEST['s'] ) ); endif; ?>"
					name="s"/> <input id="search-submit" class="button" type="submit"
					value="<?php _e( 'Search Poll Bans', 'shivs_poll' ); ?>" name=""/>
			</p>
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="action">
						<option selected="selected" value="-1"><?php _e( 'Bulk Actions', 'shivs_poll' ); ?></option>
						<option value="delete"><?php _e( 'Delete', 'shivs_poll' ); ?></option>
					</select> <input type="submit"
						value="<?php _e( 'Apply', 'shivs_poll' ); ?>"
						class="button-secondary action" id="doaction" name="">
				</div>
				<div class="alignleft actions">
					<select name="poll_id">
						<option value=""><?php _e( 'All Polls', 'shivs_poll' ); ?></option>
						<?php
							if ( count( $shivs_polls ) > 0 ){
								foreach ( $shivs_polls as $shivs_poll ) {
								?>
								<option <?php echo selected( $poll_id, $shivs_poll['id'] ); ?>
									value="<?php echo $shivs_poll['id'] ?>"><?php echo $shivs_poll['name'] ?></option>
								<?php
								}
							}
						?>
					</select>
				</div>
				<div class="alignleft actions">
					<select name="type">
						<option value=""><?php _e( 'All Ban Types', 'shivs_poll' ); ?></option>
						<option <?php echo selected( 'ip', $type ); ?> value="ip"><?php _e( 'IP', 'shivs_poll' ); ?></option>
						<option <?php echo selected( 'username', $type ); ?> value="username"><?php _e( 'Username', 'shivs_poll' ); ?></option>
						<option <?php echo selected( 'email', $type ); ?> value="email"><?php _e( 'Email', 'shivs_poll' ); ?></option>
					</select> <input type="submit"
						value="<?php _e( 'Filter', 'shivs_poll' ); ?>"
						class="button-secondary" id="post-query-submit" name="">
				</div>
				<div class="tablenav-pages one-page">
					<label for="shivs-poll-items-per-page" class="displaying-num"><?php _e( 'Items Per Page', 'shivs_poll' ); ?>
						:</label>
					<input id="shivs-poll-items-per-page" type="text" name="per_page"
						value="<?php echo $per_page; ?>"/> <input name="a"
						value="<?php _e( 'Set', 'shivs_poll' ); ?>" type="submit"/>&nbsp;&nbsp;
					<span class="displaying-num"><?php echo count( $bans ); ?> / <?php echo $total_bans;
						_e( 'Bans', 'shivs_poll' ) ?> </span>
					<?php print $pagination; ?>
				</div>
				<br class="clear">
			</div>
			<table class="wp-list-table widefat fixed" cellspacing="0">
				<thead>
					<tr>
						<th id="cb" class="manage-column column-cb check-column"
							style="width: 2%;" scope="col"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['id'] ?> <?php echo $order_direction_reverse['id'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'id', 'order' => $order_direction['id'] ) ) ); ?>">
								<span><?php _e( 'ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="width: 25%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Poll Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="type"
							class="manage-column <?php echo $order_sortable['type'] ?> <?php echo $order_direction_reverse['type'] ?>"
							style="width: 25%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'type', 'order' => $order_direction['type'] ) ) ); ?>">
								<span><?php _e( 'Ban Type', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="value"
							class="manage-column <?php echo $order_sortable['value'] ?> <?php echo $order_direction_reverse['value'] ?>"
							style="width: 15%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'value', 'order' => $order_direction['value'] ) ) ); ?>">
								<span><?php _e( 'Ban Value', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th id="cb" class="manage-column column-cb check-column"
							style="width: 2%;" scope="col"><input type="checkbox"></th>
						<th id="id"
							class="manage-column <?php echo $order_sortable['id'] ?> <?php echo $order_direction_reverse['id'] ?>"
							style="width: 5%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'id', 'order' => $order_direction['id'] ) ) ); ?>">
								<span><?php _e( 'ID', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="name"
							class="manage-column <?php echo $order_sortable['name'] ?> <?php echo $order_direction_reverse['name'] ?>"
							style="width: 25%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'name', 'order' => $order_direction['name'] ) ) ); ?>">
								<span><?php _e( 'Poll Name', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="type"
							class="manage-column <?php echo $order_sortable['type'] ?> <?php echo $order_direction_reverse['type'] ?>"
							style="width: 25%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'type', 'order' => $order_direction['type'] ) ) ); ?>">
								<span><?php _e( 'Ban Type', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
						<th id="value"
							class="manage-column <?php echo $order_sortable['value'] ?> <?php echo $order_direction_reverse['value'] ?>"
							style="width: 15%" scope="col"><a
								href="<?php echo esc_url( add_query_arg( array( 'orderby' => 'value', 'order' => $order_direction['value'] ) ) ); ?>">
								<span><?php _e( 'Ban Value', 'shivs_poll' ); ?></span> <span
									class="sorting-indicator"></span>
							</a></th>
					</tr>
				</tfoot>
				<?php
					if ( count( $bans ) > 0 ){
						foreach ( $bans as $ban ) {
						?>
						<tbody id="the-list">
							<tr valign="middle" class="alternate"
								id="shivs-poll-log<?php echo $ban['id']; ?>">
								<th class="check-column" scope="row"><input type="checkbox"
										value="<?php echo $ban['id']; ?>" name="shivspollbanscheck[]"></th>
								<td><strong><?php echo $ban['id']; ?></strong><br></td>
								<td><strong><?php echo esc_html( stripslashes( $ban['name'] ) ); ?></strong><br>
									<div class="row-actions">
										<span class="delete"><a
												onclick="if ( confirm( '<?php echo __( "You are about to remove this poll ban", 'shivs_poll' ) . ": \'" . esc_html( $log['id'] ) . "\' \\n  \'" . __( "Cancel", 'shivs_poll' ) . "\' " . __( 'to stop', 'shivs_poll' ) . ", \'" . __( 'OK', 'shivs_poll' ) . "\' " . __( 'to remove', 'shivs_poll' ); ?>' ) ) { return true;}return false;"
												href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $ban['id'] ) ), 'shivs-poll-bans-delete' ); ?>"
												class="submitdelete"><?php _e( 'Remove', 'shivs_poll' ) ?></a></span>
									</div></td>
								<td>
									<?php echo esc_html( stripslashes( $ban['type'] ) ); ?>
								</td>
								<td>
									<?php echo esc_html( stripslashes( $ban['value'] ) ); ?>
								</td>
							</tr>
						</tbody>
						<?php
						}
					}
				?>
			</table>
			<div class="tablenav bottom">
				<div class="tablenav-pages one-page">
					<span class="displaying-num"><?php echo count( $bans ); ?> / <?php echo $total_bans;
						_e( 'Bans', 'shivs_poll' ) ?> </span>
					<?php print $pagination; ?>
				</div>
			</div>
		</form>
	</div>
	<?php
	}

	public function view_shivs_poll_become_pro() {
		global $wpdb, $page, $action;
		require_once( ABSPATH . '/wp-admin/options-head.php' );
		$pro_options = get_option( 'shivs_poll_pro_options', array() );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Become Pro', 'shivs_poll' ); ?></h2>
		<div id="message"></div>
		<form action="options.php" method="post">
			<?php settings_fields( 'shivs_poll_pro_options' ); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="pro_key">Your Pro Key</label></th>
						<td><input id="pro_key" class="regular-text" type="text"
							value="<?php echo $pro_options['pro_key']; ?>"
							name="shivs_poll_pro_options[pro_key]">
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
	}

	public function shivs_poll_options_admin_init() {
		register_setting( 'shivs_poll_options', 'shivs_poll_options', array( &$this, 'shivs_poll_options_validate' ) );
		register_setting( 'shivs_poll_pro_options', 'shivs_poll_pro_options', array( &$this, 'shivs_poll_pro_options_validate' ) );
	}

	public function shivs_poll_pro_options_validate( $input ) {
		$pro_options       = get_option( 'shivs_poll_pro_options', array() );
		$newinput          = $pro_options;
		$errors            = '';
		$updated           = '';
		$message_delimiter = '<br>';
		// set api key
		if ( isset ( $input ['pro_key'] ) ){
			if ( $input ['pro_key'] != '' ){
				require_once( $this->_config->plugin_inc_dir . '/pro_member_model.php' );
				$shivs_poll_pro_member = Shivs_Poll_Pro_Member_Model::getInstance();
				if ( $shivs_poll_pro_member->register_pro_member( $input ['pro_key'] ) ){
					$newinput ['pro_key']     = trim( $input ['pro_key'] );
					$newinput ['pro_api_key'] = $shivs_poll_pro_member->api_return_data['apy_key'];
					$newinput ['pro_user']    = 'yes';
					$updated .= __( 'Your Pro Key Saved!', 'shivs_poll' ) . $message_delimiter;
				}
				else {
					$newinput ['pro_key']     = trim( $input ['pro_key'] );
					$newinput ['pro_api_key'] = '';
					$newinput ['pro_user']    = 'no';
					$errors .= __( 'Pro Key Error: ', 'shivs_poll' ) . $shivs_poll_pro_member->error . $message_delimiter;
				}
			}
			else {
				$newinput ['pro_key']     = $pro_options ['pro_key'];
				$newinput ['pro_api_key'] = $pro_options ['pro_api_key'];
				$newinput ['pro_user']    = $pro_options ['pro_user'];
				$errors .= __( 'Pro Key Is Empty!', 'shivs_poll' ) . $message_delimiter;
			}
		}
		else {
			$newinput ['pro_key']     = $pro_options ['pro_key'];
			$newinput ['pro_api_key'] = $pro_options ['pro_api_key'];
			$newinput ['pro_user']    = $pro_options ['pro_user'];
			$errors .= __( 'An Error Has Occured!', 'shivs_poll' ) . $message_delimiter;
		}

		if ( '' != $errors )
			add_settings_error( 'general', 'shivs-poll-errors', $errors, 'error' );
		if ( '' != $updated )
			add_settings_error( 'general', 'shivs-poll-updates', $updated, 'updated' );

		return $newinput;
	}

	public function shivs_poll_options_validate( $input ) {
		$default_options   = get_option( 'shivs_poll_options', array() );
		$newinput          = $default_options;
		$errors            = '';
		$updated           = '';
		$message_delimiter = '<br>';
		if ( $this->current_user_can( 'manage_polls_options' ) ){
			// allow_other_answers
			if ( isset ( $input ['allow_other_answers'] ) ){
				if ( in_array( $input ['allow_other_answers'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['allow_other_answers'] != trim( $input ['allow_other_answers'] ) ){
						$newinput ['allow_other_answers'] = trim( $input ['allow_other_answers'] );
						$updated .= __( 'Option "Allow Other Answer" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['allow_other_answers'] = $default_options ['allow_other_answers'];
					$errors .= __( 'Option "Allow Other Answer" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
				}

				if ( 'yes' == $input ['allow_other_answers'] ){
					// other_answers_label
					if ( isset ( $input ['other_answers_label'] ) ){
						if ( $default_options ['other_answers_label'] != trim( $input ['other_answers_label'] ) ){
							$newinput ['other_answers_label'] = trim( $input ['other_answers_label'] );
							$updated .= __( 'Option "Other Answer Label" Updated!', 'shivs_poll' ) . $message_delimiter;
						}
					}

					//add_other_answers_to_default_answers

					if ( isset ( $input ['add_other_answers_to_default_answers'] ) ){
						if ( in_array( $input ['add_other_answers_to_default_answers'], array( 'yes', 'no' ) ) ){
							if ( $default_options ['add_other_answers_to_default_answers'] != trim( $input ['add_other_answers_to_default_answers'] ) ){
								$newinput ['add_other_answers_to_default_answers'] = trim( $input ['add_other_answers_to_default_answers'] );
								$updated .= __( 'Option "Add the values submitted in \'Other\' as answers" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						else {
							$newinput ['add_other_answers_to_default_answers'] = $default_options ['add_other_answers_to_default_answers'];
							$errors .= __( 'Option "Add the values submitted in \'Other\' as answers" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
						}
					}

					if ( isset ( $input ['display_other_answers_values'] ) ){
						if ( in_array( $input ['display_other_answers_values'], array( 'yes', 'no' ) ) ){
							if ( $default_options ['display_other_answers_values'] != trim( $input ['display_other_answers_values'] ) ){
								$newinput ['display_other_answers_values'] = trim( $input ['display_other_answers_values'] );
								$updated .= __( 'Option "Display Other Answers Values" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						else {
							$newinput ['display_other_answers_values'] = $default_options ['display_other_answers_values'];
							$errors .= __( 'Option "Display Other Answers Values" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}
			}

			// allow_multiple_answers
			if ( isset ( $input ['allow_multiple_answers'] ) ){
				if ( in_array( $input ['allow_multiple_answers'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['allow_multiple_answers'] != trim( $input ['allow_multiple_answers'] ) ){
						$newinput ['allow_multiple_answers'] = trim( $input ['allow_multiple_answers'] );
						$updated .= __( 'Option "Allow Multiple Answers" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					// allow_multiple_answers_number
					if ( 'yes' == $input ['allow_multiple_answers'] ){
						if ( isset ( $input ['allow_multiple_answers_number'] ) ){
							if ( ctype_digit( $input ['allow_multiple_answers_number'] ) ){
								if ( $default_options ['allow_multiple_answers_number'] != trim( $input ['allow_multiple_answers_number'] ) ){
									$newinput ['allow_multiple_answers_number'] = trim( $input ['allow_multiple_answers_number'] );
									$updated .= __( 'Option "Max Number of allowed answers" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['allow_multiple_answers_number'] = $default_options ['allow_multiple_answers_number'];
								$errors .= __( 'Option "Max Number of allowed answers" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						if ( isset ( $input ['allow_multiple_answers_min_number'] ) ){
							if ( ctype_digit( $input ['allow_multiple_answers_min_number'] ) ){
								if ( $default_options ['allow_multiple_answers_min_number'] != trim( $input ['allow_multiple_answers_min_number'] ) ){
									$newinput ['allow_multiple_answers_min_number'] = trim( $input ['allow_multiple_answers_min_number'] );
									$updated .= __( 'Option "Min Number of allowed answers" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['allow_multiple_answers_min_number'] = $default_options ['allow_multiple_answers_min_number'];
								$errors .= __( 'Option "Min Number of allowed answers" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['allow_multiple_answers'] = $default_options ['allow_multiple_answers'];
					$errors .= __( 'Option "Allow Multiple Answers" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			if ( isset ( $input ['use_captcha'] ) ){
				if ( in_array( $input ['use_captcha'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['use_captcha'] != trim( $input ['use_captcha'] ) ){
						$newinput ['use_captcha'] = trim( $input ['use_captcha'] );
						$updated .= __( 'Option "Use CAPTCHA" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['use_captcha'] = $default_options ['use_captcha'];
					$errors .= __( 'Option "Use CAPTCHA" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// display_answers
			if ( isset ( $input ['display_answers'] ) ){
				if ( in_array( $input ['display_answers'], array( 'vertical', 'orizontal', 'tabulated' ) ) ){
					if ( $default_options ['display_answers'] != trim( $input ['display_answers'] ) ){
						$newinput ['display_answers'] = trim( $input ['display_answers'] );
						$updated .= __( 'Option "Display Answers" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'tabulated' == $input ['display_answers'] ){
						// display_answers_tabulated_cols
						if ( isset ( $input ['display_answers_tabulated_cols'] ) ){
							if ( ctype_digit( $input ['display_answers_tabulated_cols'] ) ){
								if ( $default_options ['display_answers_tabulated_cols'] != trim( $input ['display_answers_tabulated_cols'] ) ){
									$newinput ['display_answers_tabulated_cols'] = trim( $input ['display_answers_tabulated_cols'] );
									$updated .= __( 'Option "Columns for Tabulated Display Answers" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['display_answers_tabulated_cols'] = $default_options ['display_answers_tabulated_cols'];
								$errors .= __( 'Option "Columns for Tabulated Display Answers" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['display_answers'] = $default_options ['display_answers'];
					$errors .= __( 'Option "Display Answers" Not Updated! you must choose between \'vertical\', \'horizontal\' or \'tabulated\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// display_results
			if ( isset ( $input ['display_results'] ) ){
				if ( in_array( $input ['display_results'], array( 'vertical', 'orizontal', 'tabulated' ) ) ){
					if ( $default_options ['display_results'] != trim( $input ['display_results'] ) ){
						$newinput ['display_results'] = trim( $input ['display_results'] );
						$updated .= __( 'Option "Display Results" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'tabulated' == $input ['display_results'] ){
						// display_results_tabulated_cols
						if ( isset ( $input ['display_results_tabulated_cols'] ) ){
							if ( ctype_digit( $input ['display_results_tabulated_cols'] ) ){
								if ( $default_options ['display_results_tabulated_cols'] != trim( $input ['display_results_tabulated_cols'] ) ){
									$newinput ['display_results_tabulated_cols'] = trim( $input ['display_results_tabulated_cols'] );
									$updated .= __( 'Option "Columns for Tabulated Display Results" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['display_results_tabulated_cols'] = $default_options ['display_results_tabulated_cols'];
								$errors .= __( 'Option "Columns for Tabulated Display Results" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['display_results'] = $default_options ['display_results'];
					$errors .= __( 'Option "Display Results" Not Updated! Choose the display layout: \'vertical\', \'horizontal\' or \'tabulated\'', 'shivs_poll' ) . $message_delimiter;
				}
			}
			//template_width
			if ( isset ( $input ['template_width'] ) ){
				if ( '' != trim( $input ['template_width'] ) ){
					if ( $default_options ['template_width'] != trim( $input ['template_width'] ) ){
						$newinput ['template_width'] = trim( $input ['template_width'] );
						$updated .= __( 'Option "Poll Template Width" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['template_width'] = $default_options ['template_width'];
					$errors .= __( 'Option "Poll Template Width" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			//widget_template_width
			if ( isset ( $input ['widget_template_width'] ) ){
				if ( '' != trim( $input ['widget_template_width'] ) ){
					if ( $default_options ['widget_template_width'] != trim( $input ['widget_template_width'] ) ){
						$newinput ['widget_template_width'] = trim( $input ['widget_template_width'] );
						$updated .= __( 'Option "Widget Template Width" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['widget_template_width'] = $default_options ['widget_template_width'];
					$errors .= __( 'Option "Widget Template Width" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// use_template_bar
			if ( isset ( $input ['use_template_bar'] ) ){
				if ( in_array( $input ['use_template_bar'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['use_template_bar'] != trim( $input ['use_template_bar'] ) ){
						$newinput ['use_template_bar'] = trim( $input ['use_template_bar'] );
						$updated .= __( 'Option "Use Template Result Bar" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'no' == $input ['use_template_bar'] ){
						// bar_background
						if ( isset ( $input ['bar_background'] ) ){
							if ( ctype_alnum( $input ['bar_background'] ) ){
								if ( $default_options ['bar_background'] != trim( $input ['bar_background'] ) ){
									$newinput ['bar_background'] = trim( $input ['bar_background'] );
									$updated .= __( 'Option "Result Bar Background Color" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['bar_background'] = $default_options ['bar_background'];
								$errors .= __( 'Option "Result Bar Background Color" Not Updated! Fill in an alphanumeric value!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// bar_height
						if ( isset ( $input ['bar_height'] ) ){
							if ( ctype_digit( $input ['bar_height'] ) ){
								if ( $default_options ['bar_height'] != trim( $input ['bar_height'] ) ){
									$newinput ['bar_height'] = trim( $input ['bar_height'] );
									$updated .= __( 'Option "Result Bar Height" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['bar_height'] = $default_options ['bar_height'];
								$errors .= __( 'Option "Result Bar Height" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// bar_border_color
						if ( isset ( $input ['bar_border_color'] ) ){
							if ( ctype_alnum( $input ['bar_border_color'] ) ){
								if ( $default_options ['bar_border_color'] != trim( $input ['bar_border_color'] ) ){
									$newinput ['bar_border_color'] = trim( $input ['bar_border_color'] );
									$updated .= __( 'Option "Result Bar Border Color" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['bar_border_color'] = $default_options ['bar_border_color'];
								$errors .= __( 'Option "Result Bar Border Color" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// bar_border_width
						if ( isset ( $input ['bar_border_width'] ) ){
							if ( ctype_digit( $input ['bar_border_width'] ) ){
								if ( $default_options ['bar_border_width'] != trim( $input ['bar_border_width'] ) ){
									$newinput ['bar_border_width'] = trim( $input ['bar_border_width'] );
									$updated .= __( 'Option "Result Bar Border Width" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['bar_border_width'] = $default_options ['bar_border_width'];
								$errors .= __( 'Option "Result Bar Border Width" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// bar_border_style
						if ( isset ( $input ['bar_border_style'] ) ){
							if ( ctype_alpha( $input ['bar_border_style'] ) ){
								if ( $default_options ['bar_border_style'] != trim( $input ['bar_border_style'] ) ){
									$newinput ['bar_border_style'] = trim( $input ['bar_border_style'] );
									$updated .= __( 'Option "Result Bar Border Style" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['bar_border_style'] = $default_options ['bar_border_style'];
								$errors .= __( 'Option "Result Bar Border Style" Not Updated! Fill in an alphanumeric value!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['use_template_bar'] = $default_options ['use_template_bar'];
					$errors .= __( 'Option "Use Template Result Bar" Not Updated! Choose "yes" or "no"!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// sorting_answers
			if ( isset ( $input ['sorting_answers'] ) ){
				if ( in_array( $input ['sorting_answers'], array( 'exact', 'alphabetical', 'random', 'votes' ) ) ){
					if ( $default_options ['sorting_answers'] != trim( $input ['sorting_answers'] ) ){
						$newinput ['sorting_answers'] = trim( $input ['sorting_answers'] );
						$updated .= __( 'Option "Sort Answers" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					// sorting_answers_direction
					if ( isset ( $input ['sorting_answers_direction'] ) ){
						if ( in_array( $input ['sorting_answers_direction'], array( 'asc', 'desc' ) ) ){
							if ( $default_options ['sorting_answers_direction'] != trim( $input ['sorting_answers_direction'] ) ){
								$newinput ['sorting_answers_direction'] = trim( $input ['sorting_answers_direction'] );
								$updated .= __( 'Option "Sort Answers Direction" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						else {
							$newinput ['sorting_answers_direction'] = $default_options ['sorting_answers_direction'];
							$errors .= __( 'Option "Sort Answers Direction" Not Updated! Please choose between \'Ascending\' or \'Descending\'', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}
				else {
					$newinput ['sorting_answers'] = $default_options ['sorting_answers'];
					$errors .= __( 'Option "Sort Answers" Not Updated! Please choose between: \'exact\', \'alphabetical\', \'random\' or \'votes\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// sorting_results
			if ( isset ( $input ['sorting_answers'] ) ){
				if ( in_array( $input ['sorting_results'], array( 'exact', 'alphabetical', 'random', 'votes' ) ) ){
					if ( $default_options ['sorting_results'] != trim( $input ['sorting_results'] ) ){
						$newinput ['sorting_results'] = trim( $input ['sorting_results'] );
						$updated .= __( 'Option "Sort Results" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					// sorting_results_direction
					if ( isset ( $input ['sorting_results_direction'] ) ){
						if ( in_array( $input ['sorting_results_direction'], array( 'asc', 'desc' ) ) ){
							if ( $default_options ['sorting_results_direction'] != trim( $input ['sorting_results_direction'] ) ){
								$newinput ['sorting_results_direction'] = trim( $input ['sorting_results_direction'] );
								$updated .= __( 'Option "Sort Results Direction" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						else {
							$newinput ['sorting_results_direction'] = $default_options ['sorting_results_direction'];
							$errors .= __( 'Option "Sort Results Direction" Not Updated! Please choose between \'Ascending\' or \'Descending\'', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}
				else {
					$newinput ['sorting_results'] = $default_options ['sorting_results'];
					$errors .= __( 'Option "Sort Results" Not Updated! Please choose between: \'exact\', \'alphabetical\', \'random\' or \'votes\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// start_date
			if ( isset ( $input ['start_date'] ) ){
				if ( '' != trim( $input ['start_date'] ) ){
					if ( $default_options ['start_date'] != trim( $input ['start_date'] ) ){
						$newinput ['start_date'] = trim( $input ['start_date'] );
						$updated .= __( 'Option "Poll Start Date" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['start_date'] = $default_options ['start_date'];
					$errors .= __( 'Option "Poll Start Date" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// never_expire
			if ( !isset ( $input ['never_expire'] ) ){
				$input ['never_expire'] = 'no';
			}
			if ( 'yes' == $input ['never_expire'] ){
				if ( $default_options ['never_expire'] != trim( $input ['never_expire'] ) ){
					$newinput ['never_expire'] = trim( $input ['never_expire'] );
					$newinput ['end_date']     = '9999-12-31 23:59:59';
					$updated .= __( 'Option "Poll End Date" Updated!', 'shivs_poll' ) . $message_delimiter;
				}
			}
			else {
				if ( isset ( $input ['end_date'] ) ){
					if ( '' != $input ['end_date'] ){
						if ( $default_options ['end_date'] != trim( $input ['end_date'] ) ){
							$newinput ['end_date']     = $input ['end_date'];
							$newinput ['never_expire'] = 'no';
							$updated .= __( 'Option "Poll End Date" Updated!', 'shivs_poll' ) . $message_delimiter;
						}
					}
					else {
						$errors .= __( 'Option "Poll End Date" Not Updated! The field is empty! ', 'shivs_poll' ) . $message_delimiter;
					}
				}
			}

			// view_results
			if ( isset ( $input ['view_results'] ) ){
				if ( in_array( $input ['view_results'], array( 'before', 'after', 'after-poll-end-date', 'never', 'custom-date' ) ) ){
					if ( $default_options ['view_results'] != trim( $input ['view_results'] ) ){
						$newinput ['view_results'] = trim( $input ['view_results'] );
						$updated .= __( 'Option "View Results" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'custom-date' == $newinput ['view_results'] ){
						// view_results_start_date
						if ( isset ( $input ['view_results_start_date'] ) ){
							if ( $default_options ['view_results_start_date'] != trim( $input ['view_results_start_date'] ) ){
								$newinput ['view_results_start_date'] = $input ['view_results_start_date'];
								$updated .= __( 'Option "View Results Custom Date" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['view_results'] = $default_options ['view_results'];
					$errors .= __( 'Option "View Results" Not Updated! Please choose between: \'Before\', \'After\', \'After Poll End Date\', \'Never\' or \'Custom Date\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// vote_permisions
			if ( isset ( $input ['view_results_permissions'] ) ){
				if ( in_array( $input ['view_results_permissions'], array( 'quest-only', 'registered-only', 'guest-registered' ) ) ){
					if ( $default_options ['view_results_permissions'] != trim( $input ['view_results_permissions'] ) ){
						$newinput ['view_results_permissions'] = trim( $input ['view_results_permissions'] );
						$updated .= __( 'Option "View Results Permissions" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['view_results_permissions'] = $default_options ['view_results_permissions'];
					$errors .= __( 'Option "View Results Permissions" Not Updated! Please choose between \'Quest Only\', \'Registered Only\', \'Guest & Registered Users\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_results_type
			if ( isset ( $input ['view_results_type'] ) ){
				if ( in_array( $input ['view_results_type'], array( 'votes-number', 'percentages', 'votes-number-and-percentages' ) ) ){
					if ( $default_options ['view_results_type'] != trim( $input ['view_results_type'] ) ){
						$newinput ['view_results_type'] = trim( $input ['view_results_type'] );
						$updated .= __( 'Option "View Results Type" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['view_results_type'] = $default_options ['view_results_type'];
					$errors .= __( 'Option "View Results Type" Not Updated! Please choose between: \'Votes number\', \'Percentages\' or \'Votes number and percentages\' ', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// answer_result_label
			if ( isset ( $input ['answer_result_label'] ) ){
				if ( 'votes-number' == $input ['view_results_type'] ){
					if ( stripos( $input ['answer_result_label'], '%POLL-ANSWER-RESULT-VOTES%' ) === false ){
						$newinput ['answer_result_label'] = $default_options ['answer_result_label'];
						$errors .= __( 'Option "Poll Answer Result Label" Not Updated! You must use %POLL-ANSWER-RESULT-VOTES%!', 'shivs_poll' ) . $message_delimiter;
					}
					else {
						if ( $default_options ['answer_result_label'] != trim( $input ['answer_result_label'] ) ){
							$newinput ['answer_result_label'] = trim( $input ['answer_result_label'] );
							$updated .= __( 'Option "Poll Answer Result Label" Updated!', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}

				if ( 'percentages' == $input ['view_results_type'] ){
					if ( stripos( $input ['answer_result_label'], '%POLL-ANSWER-RESULT-PERCENTAGES%' ) === false ){
						$newinput ['answer_result_label'] = $default_options ['answer_result_label'];
						$errors .= __( 'Option "Poll Answer Result Label" Not Updated! You must use %POLL-ANSWER-RESULT-PERCENTAGES%!', 'shivs_poll' ) . $message_delimiter;
					}
					else {
						if ( $default_options ['answer_result_label'] != trim( $input ['answer_result_label'] ) ){
							$newinput ['answer_result_label'] = trim( $input ['answer_result_label'] );
							$updated .= __( 'Option "Poll Answer Result Label" Updated!', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}

				if ( 'votes-number-and-percentages' == $input ['view_results_type'] ){
					if ( stripos( $input ['answer_result_label'], '%POLL-ANSWER-RESULT-PERCENTAGES%' ) === false ){
						$newinput ['answer_result_label'] = $default_options ['answer_result_label'];
						$errors .= __( 'Option "Poll Answer Result Label" Not Updated! You must use %POLL-ANSWER-RESULT-VOTES% and %POLL-ANSWER-RESULT-PERCENTAGES%!', 'shivs_poll' ) . $message_delimiter;
					}
					elseif ( stripos( $input ['answer_result_label'], '%POLL-ANSWER-RESULT-VOTES%' ) === false ) {
						$newinput ['answer_result_label'] = $default_options ['answer_result_label'];
						$errors .= __( 'Option "Poll Answer Result Label" Not Updated! You must use %POLL-ANSWER-RESULT-VOTES% and %POLL-ANSWER-RESULT-PERCENTAGES%!', 'shivs_poll' ) . $message_delimiter;
					}
					else {
						if ( $default_options ['answer_result_label'] != trim( $input ['answer_result_label'] ) ){
							$newinput ['answer_result_label'] = trim( $input ['answer_result_label'] );
							$updated .= __( 'Option "Poll Answer Result Label" Updated!', 'shivs_poll' ) . $message_delimiter;
						}
					}
				}
			}

			// singular_answer_result_votes_number_label
			if ( isset ( $input ['singular_answer_result_votes_number_label'] ) ){
				if ( '' != $input ['singular_answer_result_votes_number_label'] ){
					if ( $default_options ['singular_answer_result_votes_number_label'] != trim( $input ['singular_answer_result_votes_number_label'] ) ){
						$newinput ['singular_answer_result_votes_number_label'] = trim( $input ['singular_answer_result_votes_number_label'] );
						$updated .= __( 'Option "Poll Answer Result Votes Number Singular Label" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['singular_answer_result_votes_number_label'] = $default_options ['singular_answer_result_votes_number_label'];
					$errors .= __( 'Option "Poll Answer Result Votes Number Singular Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// plural_answer_result_votes_number_label
			if ( isset ( $input ['plural_answer_result_votes_number_label'] ) ){
				if ( '' != $input ['singular_answer_result_votes_number_label'] ){
					if ( $default_options ['plural_answer_result_votes_number_label'] != trim( $input ['plural_answer_result_votes_number_label'] ) ){
						$newinput ['plural_answer_result_votes_number_label'] = trim( $input ['plural_answer_result_votes_number_label'] );
						$updated .= __( 'Option "Poll Answer Result Votes Number Plural Label" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['plural_answer_result_votes_number_label'] = $default_options ['plural_answer_result_votes_number_label'];
					$errors .= __( 'Option "Poll Answer Result Votes Number Plural Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// vote_button_label
			if ( isset ( $input ['vote_button_label'] ) ){
				if ( '' != $input ['vote_button_label'] ){
					if ( $default_options ['vote_button_label'] != trim( $input ['vote_button_label'] ) ){
						$newinput ['vote_button_label'] = trim( $input ['vote_button_label'] );
						$updated .= __( 'Option "Vote Button Label" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['vote_button_label'] = $default_options ['vote_button_label'];
					$errors .= __( 'Option "Vote Button Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_results_link
			if ( isset ( $input ['view_results_link'] ) ){
				if ( in_array( $input ['view_results_link'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['view_results_link'] != trim( $input ['view_results_link'] ) ){
						$newinput ['view_results_link'] = trim( $input ['view_results_link'] );
						$updated .= __( 'Option "View Results Link" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['view_results_link'] ){
						// view_results_link_label
						if ( isset ( $input ['view_results_link_label'] ) ){
							if ( '' != $input ['view_results_link_label'] ){
								if ( $default_options ['view_results_link_label'] != trim( $input ['view_results_link_label'] ) ){
									$newinput ['view_results_link_label'] = trim( $input ['view_results_link_label'] );
									$updated .= __( 'Option "View Results Link Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['view_results_link_label'] = $default_options ['view_results_link_label'];
								$errors .= __( 'Option "View Results Link Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['view_results_link'] = $default_options ['view_results_link'];
					$errors .= __( 'Option "View Results Link" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_back_to_vote_link
			if ( isset ( $input ['view_back_to_vote_link'] ) ){
				if ( in_array( $input ['view_back_to_vote_link'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['view_back_to_vote_link'] != trim( $input ['view_back_to_vote_link'] ) ){
						$newinput ['view_back_to_vote_link'] = trim( $input ['view_back_to_vote_link'] );
						$updated .= __( 'Option "View Back To Vote Link" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['view_back_to_vote_link'] ){
						// view_results_link_label
						if ( isset ( $input ['view_back_to_vote_link_label'] ) ){
							if ( '' != $input ['view_back_to_vote_link_label'] ){
								if ( $default_options ['view_back_to_vote_link_label'] != trim( $input ['view_back_to_vote_link_label'] ) ){
									$newinput ['view_back_to_vote_link_label'] = trim( $input ['view_back_to_vote_link_label'] );
									$updated .= __( 'Option "View Back to Vote Link Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['view_back_to_vote_link_label'] = $default_options ['view_back_to_vote_link_label'];
								$errors .= __( 'Option "View Back to Vote Link Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['view_back_to_vote_link'] = $default_options ['view_back_to_vote_link'];
					$errors .= __( 'Option "View Back to Vote Link" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_total_votes
			if ( isset ( $input ['view_total_votes'] ) ){
				if ( in_array( $input ['view_total_votes'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['view_total_votes'] != trim( $input ['view_total_votes'] ) ){
						$newinput ['view_total_votes'] = trim( $input ['view_total_votes'] );
						$updated .= __( 'Option "View Total Votes" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					// view_total_votes
					if ( 'yes' == $input ['view_total_votes'] ){
						if ( isset ( $input ['view_total_votes_label'] ) ){
							if ( stripos( $input ['view_total_votes_label'], '%POLL-TOTAL-VOTES%' ) === false ){
								$newinput ['view_total_votes_label'] = $default_options ['view_total_votes_label'];
								$errors .= __( 'You must use %POLL-TOTAL-VOTES% to define your Total Votes label!', 'shivs_poll' ) . $message_delimiter;
							}
							else {
								if ( $default_options ['view_total_votes_label'] != trim( $input ['view_total_votes_label'] ) ){
									$newinput ['view_total_votes_label'] = trim( $input ['view_total_votes_label'] );
									$updated .= __( 'Option "View Total Votes Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
					}
				}
				else {
					$newinput ['view_total_votes'] = $default_options ['view_total_votes'];
					$errors .= __( 'Option "View Total Votes" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_total_answers
			if ( isset ( $input ['view_total_answers'] ) ){
				if ( in_array( $input ['view_total_answers'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['view_total_answers'] != trim( $input ['view_total_answers'] ) ){
						$newinput ['view_total_answers'] = trim( $input ['view_total_answers'] );
						$updated .= __( 'Option "View Total Answers" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					// view_total_answers
					if ( 'yes' == $input ['view_total_answers'] ){
						if ( isset ( $input ['view_total_answers_label'] ) ){
							if ( stripos( $input ['view_total_answers_label'], '%POLL-TOTAL-ANSWERS%' ) === false ){
								$newinput ['view_total_answers_label'] = $default_options ['view_total_answers_label'];
								$errors .= __( 'You must use %POLL-TOTAL-ANSWERS% to define your Total Answers label!', 'shivs_poll' ) . $message_delimiter;
							}
							else {
								if ( $default_options ['view_total_answers_label'] != trim( $input ['view_total_answers_label'] ) ){
									$newinput ['view_total_answers_label'] = trim( $input ['view_total_answers_label'] );
									$updated .= __( 'Option "View Total Answers Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
					}
				}
				else {
					$newinput ['view_total_answers'] = $default_options ['view_total_answers'];
					$errors .= __( 'Option "View Total Answers" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			if ( isset ( $input ['message_after_vote'] ) ){
				if ( $default_options ['message_after_vote'] != trim( $input ['message_after_vote'] ) ){
					$newinput ['message_after_vote'] = trim( $input ['message_after_vote'] );
					$updated .= __( 'Option "Message After Vote" Updated!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// use_default_loading_image
			if ( isset ( $input ['use_default_loading_image'] ) ){
				if ( in_array( $input ['use_default_loading_image'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['use_default_loading_image'] != trim( $input ['use_default_loading_image'] ) ){
						$newinput ['use_default_loading_image'] = trim( $input ['use_default_loading_image'] );
						$updated .= __( 'Option "Use Default Loading Image" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'no' == $input ['use_default_loading_image'] ){
						if ( isset ( $input ['loading_image_url'] ) ){
							if ( stripos( $input ['loading_image_url'], 'http' ) === false ){
								$newinput ['loading_image_url'] = $default_options ['loading_image_url'];
								$errors .= __( 'You must use a url like "http://.." to define your Loading Image Url!', 'shivs_poll' ) . $message_delimiter;
							}
							else {
								if ( $default_options ['loading_image_url'] != trim( $input ['loading_image_url'] ) ){
									$newinput ['loading_image_url'] = trim( $input ['loading_image_url'] );
									$updated .= __( 'Option "Loading Image Url" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
					}
				}
				else {
					$newinput ['use_default_loading_image'] = $default_options ['use_default_loading_image'];
					$errors .= __( 'Option "Use Default Loading Image" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// vote_permisions
			if ( isset ( $input ['vote_permisions'] ) ){
				if ( in_array( $input ['vote_permisions'], array( 'quest-only', 'registered-only', 'guest-registered' ) ) ){
					if ( $default_options ['vote_permisions'] != trim( $input ['vote_permisions'] ) ){
						$newinput ['vote_permisions'] = trim( $input ['vote_permisions'] );
						$updated .= __( 'Option "Vote Permissions" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( in_array( $input ['vote_permisions'], array( 'registered-only', 'guest-registered' ) ) ){

						if ( isset( $input['vote_permisions_facebook'] ) && in_array( $input['vote_permisions_facebook'], array( 'yes', 'no' ) ) ){
							if ( $default_options ['vote_permisions_facebook'] != trim( $input ['vote_permisions_facebook'] ) ){
								$newinput ['vote_permisions_facebook'] = trim( $input ['vote_permisions_facebook'] );
								$updated .= __( 'Option "Vote as Facebook User" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
							if ( 'yes' == $input['vote_permisions_facebook'] ){
								if ( $default_options ['vote_permisions_facebook_label'] != trim( $input ['vote_permisions_facebook_label'] ) ){
									$newinput ['vote_permisions_facebook_label'] = trim( $input ['vote_permisions_facebook_label'] );
									$updated .= __( 'Option "Vote as Facebook User Buton Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
						if ( isset( $input['vote_permisions_wordpress'] ) && in_array( $input['vote_permisions_wordpress'], array( 'yes', 'no' ) ) ){
							if ( $default_options ['vote_permisions_wordpress'] != trim( $input ['vote_permisions_wordpress'] ) ){
								$newinput ['vote_permisions_wordpress'] = trim( $input ['vote_permisions_wordpress'] );
								$updated .= __( 'Option "Vote as Wordpress User" Updated!', 'shivs_poll' ) . $message_delimiter;
							}

							if ( 'yes' == $input['vote_permisions_wordpress'] ){
								if ( $default_options ['vote_permisions_wordpress_label'] != trim( $input ['vote_permisions_wordpress_label'] ) ){
									$newinput ['vote_permisions_wordpress_label'] = trim( $input ['vote_permisions_wordpress_label'] );
									$updated .= __( 'Option "Vote as Wordpress User Buton Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
						if ( isset( $input['vote_permisions_anonymous'] ) && in_array( $input['vote_permisions_anonymous'], array( 'yes', 'no' ) ) ){
							if ( $default_options ['vote_permisions_anonymous'] != trim( $input ['vote_permisions_anonymous'] ) ){
								$newinput ['vote_permisions_anonymous'] = trim( $input ['vote_permisions_anonymous'] );
								$updated .= __( 'Option "Vote as Anonymous User" Updated!', 'shivs_poll' ) . $message_delimiter;
							}

							if ( 'yes' == $input['vote_permisions_anonymous'] ){
								if ( $default_options ['vote_permisions_anonymous_label'] != trim( $input ['vote_permisions_anonymous_label'] ) ){
									$newinput ['vote_permisions_anonymous_label'] = trim( $input ['vote_permisions_anonymous_label'] );
									$updated .= __( 'Option "Vote as Anonymous User Buton Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
					}
				}
				else {
					$newinput ['vote_permisions'] = $default_options ['vote_permisions'];
					$errors .= __( 'Option "Vote Permissions" Not Updated! Please choose between \'Quest Only\', \'Registered Only\', \'Guest & Registered Users\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// blocking_voters
			if ( isset ( $input ['blocking_voters'] ) ){
				if ( in_array( $input ['blocking_voters'], array( 'dont-block', 'cookie', 'ip', 'username', 'cookie-ip' ) ) ){
					if ( $default_options ['blocking_voters'] != trim( $input ['blocking_voters'] ) ){
						$newinput ['blocking_voters'] = trim( $input ['blocking_voters'] );
						$updated .= __( 'Option "Blocking Voters" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'dont-block' != $newinput ['blocking_voters'] ){
						// blocking_voters_interval_value
						if ( isset ( $input ['blocking_voters_interval_value'] ) ){
							if ( ctype_digit( $input ['blocking_voters_interval_value'] ) ){
								if ( $default_options ['blocking_voters_interval_value'] != trim( $input ['blocking_voters_interval_value'] ) ){
									$newinput ['blocking_voters_interval_value'] = trim( $input ['blocking_voters_interval_value'] );
									$updated .= __( 'Option "Blocking Voters Interval Value" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['blocking_voters_interval_value'] = $default_options ['blocking_voters_interval_value'];
								$errors .= __( 'Option "Blocking Voters Interval Value" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// blocking_voters_interval_unit
						if ( isset ( $input ['blocking_voters_interval_unit'] ) ){
							if ( in_array( $input ['blocking_voters_interval_unit'], array( 'seconds', 'minutes', 'hours', 'days' ) ) ){
								if ( $default_options ['blocking_voters_interval_unit'] != trim( $input ['blocking_voters_interval_unit'] ) ){
									$newinput ['blocking_voters_interval_unit'] = trim( $input ['blocking_voters_interval_unit'] );
									$updated .= __( 'Option "Blocking Voters Interval Unit" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['blocking_voters_interval_unit'] = $default_options ['blocking_voters_interval_unit'];
								$errors .= __( 'Option "Blocking Voters Interval Unit" Not Updated! Please choose between \'Seconds\', \'Minutes\', \'Hours\' or \'Days\'', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['blocking_voters'] = $default_options ['blocking_voters'];
					$errors .= __( 'Option "Blocking Voters" Not Updated! Please choose between: \'Don`t Block\', \'Cookie\', \'Ip\', \'Username\', \'Cookie and Ip\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// limit_number_of_votes_per_user
			if ( isset ( $input ['limit_number_of_votes_per_user'] ) ){
				if ( in_array( $input ['limit_number_of_votes_per_user'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['limit_number_of_votes_per_user'] != trim( $input ['limit_number_of_votes_per_user'] ) ){
						$newinput ['limit_number_of_votes_per_user'] = trim( $input ['limit_number_of_votes_per_user'] );
						$updated .= __( 'Option "Limit Number of Votes per User" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['limit_number_of_votes_per_user'] ){
						if ( isset ( $input ['number_of_votes_per_user'] ) ){
							if ( intval( $input ['number_of_votes_per_user'] ) <= 0 ){
								$newinput ['number_of_votes_per_user'] = $default_options ['number_of_votes_per_user'];
								$errors .= __( '"Number of Votes per User" must be a number > 0 !', 'shivs_poll' ) . $message_delimiter;
							}
							else {
								if ( $default_options ['number_of_votes_per_user'] != $input ['number_of_votes_per_user'] ){
									$newinput ['number_of_votes_per_user'] = trim( $input ['number_of_votes_per_user'] );
									$updated .= __( 'Option "Number of Votes per User" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
						}
					}
				}
				else {
					$newinput ['use_default_loading_image'] = $default_options ['use_default_loading_image'];
					$errors .= __( 'Option "Use Default Loading Image" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// percentages_decimals
			if ( isset ( $input ['percentages_decimals'] ) ){
				if ( ctype_digit( $input ['percentages_decimals'] ) ){
					if ( $default_options ['percentages_decimals'] != trim( $input ['percentages_decimals'] ) ){
						$newinput ['percentages_decimals'] = trim( $input ['percentages_decimals'] );
						$updated .= __( 'Option "Percentages Decimals" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['percentages_decimals'] = $default_options ['percentages_decimals'];
					$errors .= __( 'Option "Percentages Decimals" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// redirect_after_vote
			if ( isset ( $input ['redirect_after_vote'] ) ){
				if ( in_array( $input ['redirect_after_vote'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['redirect_after_vote'] != trim( $input ['redirect_after_vote'] ) ){
						$newinput ['redirect_after_vote'] = trim( $input ['redirect_after_vote'] );
						$updated .= __( 'Option "Redirect After Vote" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['redirect_after_vote'] ){
						// archive_order
						if ( isset ( $input ['redirect_after_vote_url'] ) ){
							if ( '' != $input ['redirect_after_vote_url'] ){
								if ( $default_options ['redirect_after_vote_url'] != trim( $input ['redirect_after_vote_url'] ) ){
									$newinput ['redirect_after_vote_url'] = trim( $input ['redirect_after_vote_url'] );
									$updated .= __( 'Option "Redirect After Vote Url" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['redirect_after_vote_url'] = $default_options ['redirect_after_vote_url'];
								$errors .= __( 'Option "Redirect After Vote Url" Not Updated! Please fill in an url!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['redirect_after_vote'] = $default_options ['redirect_after_vote'];
					$errors .= __( 'Option ""Redirect After Vote" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// date_format
			if ( isset ( $input ['date_format'] ) ){
				if ( $default_options ['date_format'] != trim( $input ['date_format'] ) ){
					$newinput ['date_format'] = trim( $input ['date_format'] );
					$updated .= __( 'Option "Poll Date Format" Updated!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// view_poll_archive_link
			if ( isset ( $input ['view_poll_archive_link'] ) ){
				if ( in_array( $input ['view_poll_archive_link'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['view_poll_archive_link'] != trim( $input ['view_poll_archive_link'] ) ){
						$newinput ['view_poll_archive_link'] = trim( $input ['view_poll_archive_link'] );
						$updated .= __( 'Option "View Poll Archive Link" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['view_poll_archive_link'] ){
						// view_results_link_label
						if ( isset ( $input ['view_poll_archive_link_label'] ) ){
							if ( '' != $input ['view_poll_archive_link_label'] ){
								if ( $default_options ['view_poll_archive_link_label'] != trim( $input ['view_poll_archive_link_label'] ) ){
									$newinput ['view_poll_archive_link_label'] = trim( $input ['view_poll_archive_link_label'] );
									$updated .= __( 'Option "View Poll Archive Link Label" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['view_poll_archive_link_label'] = $default_options ['view_poll_archive_link_label'];
								$errors .= __( 'Option "View Poll Archive Link Label" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						if ( isset ( $input ['poll_archive_url'] ) ){
							if ( '' != $input ['poll_archive_url'] ){
								if ( $default_options ['poll_archive_url'] != trim( $input ['poll_archive_url'] ) ){
									$newinput ['poll_archive_url'] = trim( $input ['poll_archive_url'] );
									$updated .= __( 'Option "Poll Archive URL" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['poll_archive_url'] = $default_options ['poll_archive_url'];
								$errors .= __( 'Option "Poll Archive URL" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['view_poll_archive_link'] = $default_options ['view_poll_archive_link'];
					$errors .= __( 'Option "View Poll Archive Link" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// show_in_archive
			if ( isset ( $input ['show_in_archive'] ) ){
				if ( in_array( $input ['show_in_archive'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['show_in_archive'] != trim( $input ['show_in_archive'] ) ){
						$newinput ['show_in_archive'] = trim( $input ['show_in_archive'] );
						$updated .= __( 'Option "Show Poll in Arhive" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['show_in_archive'] ){
						// archive_order
						if ( isset ( $input ['archive_order'] ) ){
							if ( ctype_digit( $input ['archive_order'] ) ){
								if ( $default_options ['archive_order'] != trim( $input ['archive_order'] ) ){
									$newinput ['archive_order'] = trim( $input ['archive_order'] );
									$updated .= __( 'Option "Archive Order" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['archive_order'] = $default_options ['archive_order'];
								$errors .= __( 'Option "Archive Order" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['show_in_archive'] = $default_options ['show_in_archive'];
					$errors .= __( 'Option "Show Poll in Archive" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// send_email_notifications
			if ( isset ( $input ['send_email_notifications'] ) ){
				if ( in_array( $input ['send_email_notifications'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['send_email_notifications'] != trim( $input ['send_email_notifications'] ) ){
						$newinput ['send_email_notifications'] = trim( $input ['send_email_notifications'] );
						$updated .= __( 'Option "Send Email Notifications" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['send_email_notifications'] ){
						// email_notifications_from_name
						if ( isset ( $input ['email_notifications_from_name'] ) ){
							if ( '' != $input ['email_notifications_from_name'] ){
								if ( $default_options ['email_notifications_from_name'] != trim( $input ['email_notifications_from_name'] ) ){
									$newinput ['email_notifications_from_name'] = trim( $input ['email_notifications_from_name'] );
									$updated .= __( 'Option "Notifications From Name" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['email_notifications_from_name'] = $default_options ['email_notifications_from_name'];
								$errors .= __( 'Option "Notifications From Name" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// email_notifications_from_email
						if ( isset ( $input ['email_notifications_from_email'] ) ){
							if ( '' != $input ['email_notifications_from_email'] ){
								if ( $default_options ['email_notifications_from_email'] != trim( $input ['email_notifications_from_email'] ) ){
									$newinput ['email_notifications_from_email'] = trim( $input ['email_notifications_from_email'] );
									$updated .= __( 'Option "Notifications From Email" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['email_notifications_from_email'] = $default_options ['email_notifications_from_email'];
								$errors .= __( 'Option "Notifications From Email" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// email_notifications_recipients
						if ( isset ( $input ['email_notifications_recipients'] ) ){
							if ( '' != $input ['email_notifications_recipients'] ){
								if ( $default_options ['email_notifications_recipients'] != trim( $input ['email_notifications_recipients'] ) ){
									$newinput ['email_notifications_recipients'] = trim( $input ['email_notifications_recipients'] );
									$updated .= __( 'Option "Email Notifications Recipients" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['email_notifications_recipients'] = $default_options ['email_notifications_recipients'];
								$errors .= __( 'Option "Email Notifications Recipients" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// email_notifications_subject
						if ( isset ( $input ['email_notifications_subject'] ) ){
							if ( '' != $input ['email_notifications_subject'] ){
								if ( $default_options ['email_notifications_subject'] != trim( $input ['email_notifications_subject'] ) ){
									$newinput ['email_notifications_subject'] = trim( $input ['email_notifications_subject'] );
									$updated .= __( 'Option "Email Notifications Subject" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['email_notifications_subject'] = $default_options ['email_notifications_subject'];
								$errors .= __( 'Option "Email Notifications Subject" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}

						// email_notifications_subject
						if ( isset ( $input ['email_notifications_body'] ) ){
							if ( '' != $input ['email_notifications_body'] ){
								if ( $default_options ['email_notifications_body'] != trim( $input ['email_notifications_body'] ) ){
									$newinput ['email_notifications_body'] = trim( $input ['email_notifications_body'] );
									$updated .= __( 'Option "Email Notifications Body" Updated!', 'shivs_poll' ) . $message_delimiter;
								}
							}
							else {
								$newinput ['email_notifications_body'] = $default_options ['email_notifications_body'];
								$errors .= __( 'Option "Email Notifications Body" Not Updated! The field is empty!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['send_email_notifications'] = $default_options ['send_email_notifications'];
					$errors .= __( 'Option "Send Email Notifications" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			// archive_polls_per_page
			if ( isset ( $input ['archive_polls_per_page'] ) ){
				if ( ctype_digit( $input ['archive_polls_per_page'] ) ){
					if ( $default_options ['archive_polls_per_page'] != trim( $input ['archive_polls_per_page'] ) ){
						$newinput ['archive_polls_per_page'] = trim( $input ['archive_polls_per_page'] );
						$updated .= __( 'Option "Archive Polls Per Page', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['archive_polls_per_page'] = $default_options ['archive_polls_per_page'];
					$errors .= __( 'Option "Archive Polls Per Page" Not Updated! Please fill in a number!', 'shivs_poll' ) . $message_delimiter;
				}
			}

			//share after vote
			if ( isset ( $input ['share_after_vote'] ) ){
				if ( in_array( $input ['share_after_vote'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['share_after_vote'] != trim( $input ['share_after_vote'] ) ){
						$newinput ['share_after_vote'] = trim( $input ['share_after_vote'] );
						$updated .= __( 'Option "Share After Vote" Updated!', 'shivs_poll' ) . $message_delimiter;
					}

					if ( 'yes' == $input ['share_after_vote'] ){
						// share_name
						if ( isset ( $input ['share_name'] ) ){
							if ( $default_options ['share_name'] != trim( $input ['share_name'] ) ){
								$newinput ['share_name'] = trim( $input ['share_name'] );
								$updated .= __( 'Option "Share Name" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						// share_caption
						if ( isset ( $input ['share_caption'] ) ){
							if ( $default_options ['share_caption'] != trim( $input ['share_caption'] ) ){
								$newinput ['share_caption'] = trim( $input ['share_caption'] );
								$updated .= __( 'Option "Share Caption" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						// share_description
						if ( isset ( $input ['share_description'] ) ){
							if ( $default_options ['share_description'] != trim( $input ['share_description'] ) ){
								$newinput ['share_description'] = trim( $input ['share_description'] );
								$updated .= __( 'Option "Share Description" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
						// share_picture
						if ( isset ( $input ['share_picture'] ) ){
							if ( $default_options ['share_picture'] != trim( $input ['share_picture'] ) ){
								$newinput ['share_picture'] = trim( $input ['share_picture'] );
								$updated .= __( 'Option "Share Picture" Updated!', 'shivs_poll' ) . $message_delimiter;
							}
						}
					}
				}
				else {
					$newinput ['share_after_vote'] = $default_options ['share_after_vote'];
					$errors .= __( 'Option "Share After Vote" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}

			//start_scheduler
			if ( isset ( $input ['start_scheduler'] ) ){
				if ( in_array( $input ['start_scheduler'], array( 'yes', 'no' ) ) ){
					if ( $default_options ['start_scheduler'] != trim( $input ['start_scheduler'] ) ){
						$newinput ['start_scheduler'] = trim( $input ['start_scheduler'] );
						$updated .= __( 'Option "Start Scheduler" Updated!', 'shivs_poll' ) . $message_delimiter;
					}
				}
				else {
					$newinput ['start_scheduler'] = $default_options ['start_scheduler'];
					$errors .= __( 'Option "Start Scheduler" Not Updated! Please choose between \'yes\' or \'no\'', 'shivs_poll' ) . $message_delimiter;
				}
			}
		}
		else {
			$errors .= __( 'Bad Request!', 'shivs_poll' ) . $message_delimiter;
		}

		if ( '' != $errors )
			add_settings_error( 'general', 'shivs-poll-errors', $errors, 'error' );
		if ( '' != $updated )
			add_settings_error( 'general', 'shivs-poll-updates', $updated, 'updated' );

		return $newinput;
	}

	public function view_shivs_poll_options() {

		require_once( ABSPATH . '/wp-admin/options-head.php' );

		global $page;

		$default_options = get_option( 'shivs_poll_options', array() );

		$pro_options     = get_option( 'shivs_poll_pro_options' );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php _e( 'Poll Options', 'shivs_poll' ); ?></h2>
		<div id="message"></div>
		<br/>

		<form action="options.php" method="post">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<?php settings_fields( 'shivs_poll_options' ); ?>
						<div class="meta-box-sortables ui-sortable" id="normal-sortables">
							<div class="postbox" id="shivs-poll-advanced-options-div1">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Answers options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'Allow other answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-allow-other-answers-no">
														<input id="shivs-poll-allow-other-answers-no" type="radio" name="shivs_poll_options[allow_other_answers]" value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-allow-other-answers-yes">
														<input <?php echo 'yes' == $default_options['allow_other_answers'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[allow_other_answers]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-other-answers-label-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Other Answer Label', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-other-answers-label" type="text" name="shivs_poll_options[other_answers_label]" value="<?php echo isset( $other_answer[0]['answer'] ) ? esc_html( stripslashes( $other_answer[0]['answer'] ) ) : $default_options['other_answers_label'] ?>"/>
													<input type="hidden" name="shivs_poll_options[other_answers_id]" value="<?php echo isset( $other_answer[0]['id'] ) ? $other_answer[0]['id'] : '' ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-other-answers-to-results-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Add the values submitted in "Other" as answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-add-other-answers-to-default-answers-no">
														<input id="shivs-poll-add-other-answers-to-default-answers-no" <?php echo 'no' == $default_options['add_other_answers_to_default_answers'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[add_other_answers_to_default_answers]" value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-add-other-answers-to-default-answers-yes">
														<input id="shivs-poll-add-other-answers-to-default-answers-yes" <?php echo 'yes' == $default_options['add_other_answers_to_default_answers'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[add_other_answers_to_default_answers]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-display-other-answers-values-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Display Other Answers Values', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-display-other-answers-values-no">
														<input id="shivs-poll-display-other-answers-values-no" <?php echo 'no' == $default_options['display_other_answers_values'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_other_answers_values]" value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-display-other-answers-values-yes">
														<input id="shivs-poll-display-other-answers-values-yes" <?php echo 'yes' == $default_options['display_other_answers_values'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_other_answers_values]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Allow Multiple Answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-allow-multiple-answers-no">
														<input id="shivs-poll-allow-multiple-answers-no" <?php echo $default_options['allow_multiple_answers'] == 'no' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[allow_multiple_answers]" value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-allow-multiple-answers-yes">
														<input id="shivs-poll-allow-multiple-answers-yes" <?php echo $default_options['allow_multiple_answers'] == 'yes' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[allow_multiple_answers]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>

												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-allow-multiple-answers-div" style="<?php echo $default_options['allow_multiple_answers'] == 'no' ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Max Number of allowed answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-allow-multiple-answers-number" type="text" name="shivs_poll_options[allow_multiple_answers_number]" value="<?php echo $default_options['allow_multiple_answers_number']; ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-allow-multiple-answers-div1" style="<?php echo $default_options['allow_multiple_answers'] == 'no' ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Min Number of allowed answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-allow-multiple-answers-min-number" type="text" name="shivs_poll_options[allow_multiple_answers_min_number]" value="<?php echo $default_options['allow_multiple_answers_min_number']; ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div2">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Display Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'Use CAPTCHA', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-use-captcha-no"><input
															id="shivs-poll-use-captcha-no"
															<?php echo 'no' == $default_options['use_captcha'] ? 'checked="checked"' : ''; ?>
															type="radio" name="shivs_poll_options[use_captcha]"
														value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-use-captcha-yes">
														<input id="shivs-poll-use-captcha-yes" <?php echo 'yes' == $default_options['use_captcha'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[use_captcha]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Display Answers', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-display-answers-vertical">
														<input id="shivs-poll-display-answers-vertical" <?php echo $default_options['display_answers'] == 'vertical' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_answers]" value="vertical"/> <?php _e( 'Vertical', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-display-answers-orizontal">
														<input id="shivs-poll-display-answers-orizontal" <?php echo $default_options['display_answers'] == 'orizontal' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_answers]" value="orizontal"/> <?php _e( 'Horizontal', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-display-answers-tabulated">
														<input id="shivs-poll-display-answers-tabulated" <?php echo $default_options['display_answers'] == 'tabulated' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_answers]" value="tabulated"/> <?php _e( 'Tabulated', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-display-answers-tabulated-div" style="<?php echo $default_options['display_answers'] != 'tabulated' ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Columns', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-display-answers-tabulated-cols" type="text" name="shivs_poll_options[display_answers_tabulated_cols]" value="<?php echo $default_options['display_answers_tabulated_cols']; ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Display Results', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-display-results-vertical">
														<input id="shivs-poll-display-results-vertical" <?php echo $default_options['display_results'] == 'vertical' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_results]" value="vertical"> <?php _e( 'Vertical', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-display-results-orizontal">
														<input id="shivs-poll-display-results-orizontal" <?php echo $default_options['display_results'] == 'orizontal' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_results]" value="orizontal"> <?php _e( 'Horizontal', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-display-results-tabulated">
														<input id="shivs-poll-display-results-tabulated" <?php echo $default_options['display_results'] == 'tabulated' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[display_results]" value="tabulated"> <?php _e( 'Tabulated', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-display-results-tabulated-div" style="<?php echo $default_options['display_results'] != 'tabulated' ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Columns', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-display-results-tabulated-cols" type="text" name="shivs_poll_options[display_results_tabulated_cols]" value="<?php echo $default_options['display_results_tabulated_cols']; ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Template Width', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-template-width" type="text" name="shivs_poll_options[template_width]" value="<?php echo $default_options['template_width']; ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Widget Template Width', 'shivs_poll' ); ?>:
												</th>
												<td>
													<input id="shivs-poll-widget-template-width" type="text" name="shivs_poll_options[widget_template_width]" value="<?php echo $default_options['widget_template_width']; ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div3">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Poll Bar Style', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'Use Template Result Bar', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-use-template-bar-no">
														<input id="shivs-poll-use-template-bar-no" <?php echo 'no' == $default_options['use_template_bar'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[use_template_bar]" value="no"/> <?php _e( 'No', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-use-template-bar-yes">
														<input id="shivs-poll-use-template-bar-yes" <?php echo 'yes' == $default_options['use_template_bar'] ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[use_template_bar]" value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label for="shivs-poll-bar-background"><?php _e( 'Background Color', 'shivs_poll' ); ?></label>
												</th>
												<td>
													#<input class="shivs-small-input" id="shivs-poll-bar-background" value="<?php echo $default_options['bar_background']; ?>" onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'background-color', '#' + this.value)" type="text" name="shivs_poll_options[bar_background]"/>
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label for="shivs-poll-bar-height"><?php _e( 'Height', 'shivs_poll' ); ?></label>
												</th>
												<td>
													<input class="shivs-small-input" id="shivs-poll-bar-height" value="<?php echo $default_options['bar_height']; ?>" onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'height', this.value + 'px')" type="text" name="shivs_poll_options[bar_height]"/> px
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label for="shivs-poll-bar-border-color"><?php _e( 'Border Color', 'shivs_poll' ) ?></label>
												</th>
												<td>
													#<input class="shivs-small-input" id="shivs-poll-bar-border-color" value="<?php echo $default_options['bar_border_color']; ?>" onblur="shivs_poll_update_bar_style( '#shivs-poll-bar-preview', 'border-color', '#' + this.value )" type="text" name="shivs_poll_options[bar_border_color]"/>
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label for="shivs-poll-bar-border-width"><?php _e( 'Border Width', 'shivs_poll' ); ?></label>
												</th>
												<td>
													<input class="shivs-small-input" id="shivs-poll-bar-border-width" value="<?php echo $default_options['bar_border_width']; ?>" onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'border-width', this.value + 'px')" type="text" name="shivs_poll_options[bar_border_width]"/> px
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label for="shivs-poll-bar-border-style"><?php _e( 'Border Style', 'shivs_poll' ); ?></label>
												</th>
												<td>
													<select id="shivs-poll-bar-border-style" onchange="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'border-style', this.value)" name="shivs_poll_options[bar_border_style]">
														<option <?php print 'solid' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?> value="solid">Solid</option>
														<option <?php print 'dashed' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?> value="dashed">Dashed</option>
														<option <?php print 'dotted' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?> value="dotted">Dotted</option>
													</select>
												</td>
											</tr>
											<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
												<th>
													<label><?php _e( 'Shivs Poll Bar Preview', 'shivs_poll' ); ?></label>
												</th>
												<td>
													<div id="shivs-poll-bar-preview"; style="width: 100px; height: <?php echo $default_options['bar_height']; ?>px; background-color:#<?php echo $default_options ['bar_background']; ?>; border-style: <?php echo $default_options['bar_border_style']; ?>; border-width: <?php echo $default_options['bar_border_width']; ?>px; border-color: #<?php echo $default_options ['bar_border_color']; ?>;"></div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div4">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Sorting Answers &amp; Results', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th><?php _e( 'Sort Answers', 'shivs_poll' ); ?>:</th>
												<td valign="top">
													<label for="shivs_poll_sorting_answers_exact">
														<input id="shivs_poll_sorting_answers_exact" <?php echo $default_options['sorting_answers'] == 'exact' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers]" value="exact"> <?php _e( 'Exact Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_answers_alphabetical">
														<input id="shivs_poll_sorting_answers_alphabetical" <?php echo $default_options['sorting_answers'] == 'alphabetical' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers]" value="alphabetical"> <?php _e( 'Alphabetical Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_answers_random">
														<input id="shivs_poll_sorting_answers_random" <?php echo $default_options['sorting_answers'] == 'random' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers]" value="random"> <?php _e( 'Random Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_answers_votes">
														<input id="shivs_poll_sorting_answers_votes" <?php echo $default_options['sorting_answers'] == 'votes' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers]" value="votes"> <?php _e( 'Number of Votes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Sort Answers Rule', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs_poll_sorting_answers_asc">
														<input id="shivs_poll_sorting_answers_asc" <?php echo $default_options['sorting_answers_direction'] == 'asc' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers_direction]" value="asc"> <?php _e( 'Ascending', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_answers_desc">
														<input id="shivs_poll_sorting_answers_desc" <?php echo $default_options['sorting_answers_direction'] == 'desc' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_answers_direction]" value="desc"> <?php _e( 'Descending', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th><?php _e( 'Sorting Results', 'shivs_poll' ); ?>:</th>
												<td valign="top">
													<label for="shivs_poll_sorting_results_exact">
														<input id="shivs_poll_sorting_results_exact" <?php echo $default_options['sorting_results'] == 'exact' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results]" value="exact"> <?php _e( 'Exact Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_results_alphabetical">
														<input id="shivs_poll_sorting_results_alphabetical" <?php echo $default_options['sorting_results'] == 'alphabetical' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results]" value="alphabetical"> <?php _e( 'Alphabetical Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_results_random">
														<input id="shivs_poll_sorting_results_random" <?php echo $default_options['sorting_results'] == 'random' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results]" value="random"> <?php _e( 'Random Order', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_results_votes">
														<input id="shivs_poll_sorting_results_votes" <?php echo $default_options['sorting_results'] == 'votes' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results]" value="votes"> <?php _e( 'Number of Votes', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Sorting Results Rule', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs_poll_sorting_results_asc">
														<input id="shivs_poll_sorting_results_asc" <?php echo $default_options['sorting_results_direction'] == 'asc' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results_direction]" value="asc"> <?php _e( 'Ascending', 'shivs_poll' ); ?>
													</label>
													<label for="shivs_poll_sorting_results_desc">
														<input id="shivs_poll_sorting_results_desc" <?php echo $default_options['sorting_results_direction'] == 'desc' ? 'checked="checked"' : ''; ?> type="radio" name="shivs_poll_options[sorting_results_direction]" value="desc"> <?php _e( 'Descending', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div5">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Poll Start/End Date', 'shivs_poll' ); ?>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th><label for="shivs-poll-start-date-input"><?php _e( 'Start Date', 'shivs_poll' ); ?>
														:</label><br><font size="0">(<?php _e( 'Current Server Time', 'shivs_poll' );
															echo ': ' . current_time( 'mysql' ); ?>)</font>
												</th>
												<td><input id="shivs-poll-start-date-input" type="text"
														name="shivs_poll_options[start_date]"
														value="<?php echo '' == $default_options['start_date'] ? current_time( 'mysql' ) : $default_options['start_date']; ?>"/>
												</td>
											</tr>
											<tr>
												<th><label for="shivs-poll-end-date-input"><?php _e( 'End Date', 'shivs_poll' ); ?>
														:</label><br><font size="0">(<?php _e( 'Current Server Time', 'shivs_poll' );
															echo ': ' . current_time( 'mysql' ); ?>)</font>
												</th>
												<td>
													<input style="<?php echo 'yes' == $default_options['never_expire'] ? 'display: none;' : ''; ?>" <?php echo 'yes' == $default_options['never_expire'] ? 'disabled="disabled"' : ''; ?> id="shivs-poll-end-date-input" type="text" name="shivs_poll_options[end_date]" value="<?php echo '' == $default_options['end_date'] ? '' : $default_options['end_date']; ?>"/>
													<label for="shivs-poll-never-expire">
														<input type="checkbox" <?php echo $default_options['never_expire'] == 'yes' ? 'checked="checked"' : ''; ?> id="shivs-poll-never-expire" name="shivs_poll_options[never_expire]" value="yes"/> <?php _e( 'No End Date', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div6">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'View Results Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'View Results', 'shivs_poll' ); ?>:
												</th>
												<td>
													<label for="shivs-poll-view-results-before-vote">
														<input class="shivs-poll-view-results-hide-custom" <?php echo 'before' == $default_options['view_results'] ? 'checked="checked"' : ''; ?> id="shivs-poll-view-results-before-vote" type="radio" value="before" name="shivs_poll_options[view_results]"/> <?php _e( 'Before Vote', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-after-vote">
														<input class="shivs-poll-view-results-hide-custom" <?php echo 'after' == $default_options['view_results'] ? 'checked="checked"' : ''; ?> id="shivs-poll-view-results-after-vote" type="radio" value="after" name="shivs_poll_options[view_results]"/> <?php _e( 'After Vote', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-after-poll-end-date">
														<input class="shivs-poll-view-results-hide-custom" <?php echo 'after-poll-end-date' == $default_options['view_results'] ? 'checked="checked"' : ''; ?> id="shivs-poll-view-results-after-poll-end-date" type="radio" value="after-poll-end-date" name="shivs_poll_options[view_results]"/> <?php _e( 'After Poll End Date', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-never">
														<input class="shivs-poll-view-results-hide-custom" <?php echo 'never' == $default_options['view_results'] ? 'checked="checked"' : ''; ?> id="shivs-poll-view-results-never" type="radio" value="never" name="shivs_poll_options[view_results]"/> <?php _e( 'Never', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-custom">
														<input class="shivs-poll-view-results-show-custom" <?php echo 'custom-date' == $default_options['view_results'] ? 'checked="checked"' : ''; ?> id="shivs-poll-view-results-custom" type="radio" value="custom-date" name="shivs_poll_options[view_results]"/> <?php _e( 'Custom Date', 'shivs_poll' ); ?>
													</label>
													<div id="shivs-poll-display-view-results-div" style="<?php echo 'custom-date' != $default_options['view_results'] ? 'display: none;' : ''; ?>">
														<label for="shivs-poll-view-results-start-date"><?php _e( 'Results display date (the users will be able to see the results starting with this date)', 'shivs_poll' ); ?>:</label>
														<input id="shivs-poll-view-results-start-date" type="text" name="shivs_poll_options[view_results_start_date]" value="<?php echo $default_options['view_results_start_date']; ?>">
													</div>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Results Permissions', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-permissions-quest-only">
														<input id="shivs-poll-view-results-permissions-quest-only" <?php echo 'quest-only' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?> type="radio" value="quest-only" name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Guest Only', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-permissions-registered-only">
														<input id="shivs-poll-view-results-permissions-registered-only" <?php echo 'registered-only' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?> type="radio" value="registered-only" name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Registered Users Only', 'shivs_poll' ); ?>
													</label>
													<label for="shivs-poll-view-results-permissions-guest-registered">
														<input id="shivs-poll-view-results-permissions-guest-registered" <?php echo 'guest-registered' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?> type="radio" value="guest-registered" name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Guest &amp; Registered Users', 'shivs_poll' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Results Display', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-votes-number"><input
															id="shivs-poll-view-results-votes-number"
															<?php echo 'votes-number' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="votes-number"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'By Votes Number', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-percentages"><input
															id="shivs-poll-view-results-percentages"
															<?php echo 'percentages' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="percentages"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'Percentages', 'shivs_poll' ); ?></label>
													<label
														for="shivs-poll-view-results-votes-number-and-percentages"><input
															id="shivs-poll-view-results-votes-number-and-percentages"
															<?php echo 'votes-number-and-percentages' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="votes-number-and-percentages"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'by Votes Number and Percentages', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Answer Result Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-answer-result-label" type="text"
														name="shivs_poll_options[answer_result_label]"
														value="<?php echo esc_html( stripslashes( $default_options['answer_result_label'] ) ); ?>"/>
													<small><i><?php _e( 'Use %POLL-ANSWER-RESULT-PERCENTAGES% for showing answer percentages and  %POLL-ANSWER-RESULT-VOTES% for showing answer number of votes', 'shivs_poll' ); ?></i></small>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Answer Result Votes Number Label', 'shivs_poll' ); ?>
													:
												</th>
												<td>
													<?php _e( 'Singular', 'shivs_poll' ); ?>
													<input
														id="shivs-poll-singular-answer-result-votes-number-label"
														type="text"
														name="shivs_poll_options[singular_answer_result_votes_number_label]"
														value="<?php echo esc_html( stripslashes( $default_options['singular_answer_result_votes_number_label'] ) ); ?>"/>
													<?php _e( 'Plural', 'shivs_poll' ); ?>
													<input
														id="shivs-poll-plural-answer-result-votes-number-label"
														type="text"
														name="shivs_poll_options[plural_answer_result_votes_number_label]"
														value="<?php echo esc_html( stripslashes( $default_options['plural_answer_result_votes_number_label'] ) ); ?>"/>

												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Vote Button Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-vote-button-label" type="text"
														name="shivs_poll_options[vote_button_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_button_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Results Link', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-link-yes"><input
															<?php echo 'yes' == $default_options['view_results_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-link-yes" type="radio"
														value="yes" name="shivs_poll_options[view_results_link]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-link-no"><input
															<?php echo 'no' == $default_options['view_results_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-link-no" type="radio" value="no"
														name="shivs_poll_options[view_results_link]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-results-link-div" style="<?php echo 'yes' != $default_options['view_results_link'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Results Link Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-results-link-label" type="text"
														name="shivs_poll_options[view_results_link_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_results_link_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Back To Vote Link ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-back-to-vote-link-yes"><input
															<?php echo 'yes' == $default_options['view_back_to_vote_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-back-to-vote-link-yes" type="radio"
														value="yes" name="shivs_poll_options[view_back_to_vote_link]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-back-to-vote-link-no"><input
															<?php echo 'no' == $default_options['view_back_to_vote_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-back-to-vote-link-no" type="radio"
														value="no" name="shivs_poll_options[view_back_to_vote_link]"/><?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-back-to-vote-link-div" style="<?php echo 'yes' != $default_options['view_back_to_vote_link'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Back To Vote Link Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-back-to-vote-link-label"
														type="text"
														name="shivs_poll_options[view_back_to_vote_link_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_back_to_vote_link_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Total Votes ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-total-votes-yes"><input
															<?php echo 'yes' == $default_options['view_total_votes'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-votes-yes" type="radio" value="yes"
														name="shivs_poll_options[view_total_votes]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-total-votes-no"><input
															<?php echo 'no' == $default_options['view_total_votes'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-votes-no" type="radio" value="no"
														name="shivs_poll_options[view_total_votes]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-total-votes-div" style="<?php echo 'yes' != $default_options['view_total_votes'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Total Votes Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-total-votes-label" type="text"
														name="shivs_poll_options[view_total_votes_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_total_votes_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Total Answers ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-total-answers-yes"><input
															<?php echo 'yes' == $default_options['view_total_answers'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-answers-yes" type="radio"
														value="yes" name="shivs_poll_options[view_total_answers]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-total-answers-no"><input
															<?php echo 'no' == $default_options['view_total_answers'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-answers-no" type="radio" value="no"
														name="shivs_poll_options[view_total_answers]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-total-answers-div" style="<?php echo 'yes' != $default_options['view_total_answers'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Total Answers Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-total-answers-label" type="text"
														name="shivs_poll_options[view_total_answers_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_total_answers_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Message After Vote', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-message-after-vote" type="text"
														name="shivs_poll_options[message_after_vote]"
														value="<?php echo esc_html( stripslashes( $default_options['message_after_vote'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th><label for="shivs-poll-page-url"><?php _e( 'Poll Page Url ', 'shivs_poll' ); ?>
														:</label>
												</th>
												<td><input id="shivs-poll-page-url" type="text"
														name="shivs_poll_options[poll_page_url]"
														value="<?php echo esc_html( stripslashes( $default_options['poll_page_url'] ) ); ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div7">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Other Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'Vote Permissions ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-vote-permisions-quest-only"><input
															id="shivs-poll-vote-permisions-quest-only"
															<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="quest-only"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Guest Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-registered-only"><input
															id="shivs-poll-vote-permisions-registered-only"
															<?php echo 'registered-only' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="registered-only"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Registered Users Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-guest-registered"><input
															id="shivs-poll-vote-permisions-guest-registered"
															<?php echo 'guest-registered' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="guest-registered"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Guest &amp; Registered Users', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<?php if ( false ){ ?>
												<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
													<th>
														<?php _e( 'Vote as Facebook User', 'shivs_poll' ); ?>:
														<?php if ($pro_options['pro_user'] == 'no') { ?>
															<br/><font size="-1">(<?php _e( 'Available only for pro version of Shivs Poll', 'shivs_poll' ); ?>
																)</font>
															<?php } ?></label>
													</th>
													<td><label for="shivs-poll-vote-permisions-facebook-yes"><input
																<?php echo 'yes' == $default_options['vote_permisions_facebook'] ? 'checked="checked"' : ''; ?>
																id="shivs-poll-vote-permisions-facebook-yes" type="radio"
															value="yes" name="shivs_poll_options[vote_permisions_facebook]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
														<label for="shivs-poll-vote-permisions-facebook-no"><input
																<?php echo 'no' == $default_options['vote_permisions_facebook'] ? 'checked="checked"' : ''; ?>
																id="shivs-poll-vote-permisions-facebook-no" type="radio" value="no"
															name="shivs_poll_options[vote_permisions_facebook]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
													</td>
												</tr>

												<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-facebook-div" style="<?php echo 'yes' != $default_options['vote_permisions_facebook'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
													<th>
														<?php _e( '"Vote as Facebook User" Button Label', 'shivs_poll' ); ?>
														:
													</th>
													<td><input id="shivs-poll-vote-permisions-facebook-label" type="text"
															name="shivs_poll_options[vote_permisions_facebook_label]"
															value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_facebook_label'] ) ); ?>"/>
													</td>
												</tr>
												<?php } ?>

											<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Vote as Wordpress User', 'shivs_poll' ); ?>
													<br><font size="0">(<?php _e( 'Will force users to login into your blog', 'shivs_poll' ); ?>
														)</font>:
												</th>
												<td><label for="shivs-poll-vote-permisions-wordpress-yes"><input
															<?php echo 'yes' == $default_options['vote_permisions_wordpress'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-wordpress-yes" type="radio"
														value="yes" name="shivs_poll_options[vote_permisions_wordpress]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-wordpress-no"><input
															<?php echo 'no' == $default_options['vote_permisions_wordpress'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-wordpress-no" type="radio" value="no"
														name="shivs_poll_options[vote_permisions_wordpress]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-wordpress-div" style="<?php echo 'yes' != $default_options['vote_permisions_wordpress'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( '"Vote as Wordpress User" Button Label', 'shivs_poll' ); ?>
													:
												</th>
												<td><input id="shivs-poll-vote-permisions-wordpress-label" type="text"
														name="shivs_poll_options[vote_permisions_wordpress_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_wordpress_label'] ) ); ?>"/>
												</td>
											</tr>

											<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Vote as Anonymous User', 'shivs_poll' ); ?>
													<br><font size="0">(<?php _e( 'Logged users will be treated as anonymous', 'shivs_poll' ); ?>
														)</font>:
												</th>
												<td><label for="shivs-poll-vote-permisions-anonymous-yes"><input
															<?php echo 'yes' == $default_options['vote_permisions_anonymous'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-anonymous-yes" type="radio"
														value="yes" name="shivs_poll_options[vote_permisions_anonymous]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-anonymous-no"><input
															<?php echo 'no' == $default_options['vote_permisions_anonymous'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-anonymous-no" type="radio" value="no"
														name="shivs_poll_options[vote_permisions_anonymous]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-anonymous-div" style="<?php echo 'yes' != $default_options['vote_permisions_anonymous'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( '"Vote as Anonymous User" Button Label', 'shivs_poll' ); ?>
													:
												</th>
												<td><input id="shivs-poll-vote-permisions-anonymous-label" type="text"
														name="shivs_poll_options[vote_permisions_anonymous_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_anonymous_label'] ) ); ?>"/>
												</td>
											</tr>

											<tr>
												<th>
													<?php _e( 'Blocking Voters', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-blocking-voters-dont-block"><input
															class="shivs-poll-blocking-voters-hide-interval"
															<?php echo 'dont-block' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-dont-block" type="radio"
														value="dont-block" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'Dont`t Block', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-cookie"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'cookie' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-cookie" type="radio"
														value="cookie" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Cookie', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-ip"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'ip' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-ip" type="radio" value="ip"
														name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Ip', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-username"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'username' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-username" type="radio"
														value="username" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Username', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-cookie-ip"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'cookie-ip' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-cookie-ip" type="radio"
														value="cookie-ip" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Cookie &amp; Ip', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-blocking-voters-interval-div" style="<?php echo 'dont-block' == $default_options['blocking_voters'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Blocking voters interval', 'shivs_poll' ); ?>:
												</th>
												<td><input type="text"
														name="shivs_poll_options[blocking_voters_interval_value]"
														id="shivs-poll-blocking-voters-interval-value"
														value="<?php echo $default_options['blocking_voters_interval_value']; ?>"/>
													<select id="shivs-poll-blocking-voters-interval-unit"
														name="shivs_poll_options[blocking_voters_interval_unit]">
														<option
															<?php echo 'seconds' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="seconds"><?php _e( 'Seconds', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'minutes' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="minutes"><?php _e( 'Minutes', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'hours' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="hours"><?php _e( 'Hours', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'days' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="days"><?php _e( 'Days', 'shivs_poll' ); ?></option>
													</select></td>
											</tr>
											<tr class="shivs-poll-limit-number-of-votes-per-user-div">
												<th>
													<?php _e( 'Limit Number of Votes per User', 'shivs_poll' ); ?>
													:<br><small>(<?php _e( 'Only for logged users', 'shivs_poll' ); ?>
														)</small>
												</th>
												<td><label for="shivs-poll-limit-number-of-votes-per-user-yes"><input
															<?php echo 'yes' == $default_options['limit_number_of_votes_per_user'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-limit-number-of-votes-per-user-yes" type="radio"
														value="yes" name="shivs_poll_options[limit_number_of_votes_per_user]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-limit-number-of-votes-per-user-no"><input
															<?php echo 'no' == $default_options['limit_number_of_votes_per_user'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-limit-number-of-votes-per-user-no" type="radio" value="no"
														name="shivs_poll_options[limit_number_of_votes_per_user]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-limit-number-of-votes-per-user-divs shivs_poll_suboption" id="shivs-poll-number-of-votes-per-user-div" style="<?php echo 'yes' != $default_options['limit_number_of_votes_per_user'] ? 'display: none;' : '' ?>">
												<th>
													<?php _e( 'Number of Votes per User', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-number-of-votes-per-user" type="text"
														name="shivs_poll_options[number_of_votes_per_user]"
														value="<?php echo esc_html( stripslashes( $default_options['number_of_votes_per_user'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Percentages Decimals', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-percentages-decimals" type="text"
														name="shivs_poll_options[percentages_decimals]"
														value="<?php echo esc_html( stripslashes( $default_options['percentages_decimals'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Use Default Loading Image', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-use-default-loading-image-yes"><input
															<?php echo 'yes' == $default_options['use_default_loading_image'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-use-default-loading-image-yes" type="radio"
															value="yes"
														name="shivs_poll_options[use_default_loading_image]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-use-default-loading-image-no"><input
															<?php echo 'no' == $default_options['use_default_loading_image'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-use-default-loading-image-no" type="radio"
															value="no"
														name="shivs_poll_options[use_default_loading_image]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-use-default-loading-image-div" style="<?php echo 'yes' == $default_options['use_default_loading_image'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Loading Image Url', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-loading-image-url" type="text"
														name="shivs_poll_options[loading_image_url]"
														value="<?php echo esc_html( stripslashes( $default_options['loading_image_url'] ) ); ?>"/>
												</td>
											</tr>

											<tr>
												<th>
													<?php _e( 'Redirect After Vote', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-redirect-after-vote-yes"><input
															<?php echo 'yes' == $default_options['redirect_after_vote'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-redirect-after-vote-yes" type="radio"
															value="yes"
														name="shivs_poll_options[redirect_after_vote]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-redirect-after-vote-no"><input
															<?php echo 'no' == $default_options['redirect_after_vote'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-redirect-after-vote-no" type="radio"
															value="no"
														name="shivs_poll_options[redirect_after_vote]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-redirect-after-vote-url-div" style="<?php echo 'no' == $default_options['redirect_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Redirect After Vote Url', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-redirect-after-vote-url" type="text"
														name="shivs_poll_options[redirect_after_vote_url]"
														value="<?php echo esc_html( stripslashes( $default_options['redirect_after_vote_url'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Date Format', 'shivs_poll' ); ?>
													: <br/><font size="0"><?php _e( 'Check', 'shivs_popll' ) ?>
														<a target="_blank" href="http://codex.wordpress.org/Formatting_Date_and_Time"> <?php _e( 'documentation', 'shivs_popll' ) ?></a></font>
												</th>
												<td><input id="shivs-poll-date-format" type="text"
														name="shivs_poll_options[date_format]"
														value="<?php echo esc_html( stripslashes( $default_options['date_format'] ) ); ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div8">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Archive Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
										<tr>
											<th>
												<?php _e( 'View Poll Archive Link ', 'shivs_poll' ); ?>:
											</th>
											<td><input
													<?php checked( 'yes', $default_options['view_poll_archive_link'] ); ?>
													id="shivs-poll-view-poll-archive-link-yes" type="radio"
													value="yes" name="shivs_poll_options[view_poll_archive_link]"/><label
													for="shivs-poll-view-poll-archive-link-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<input
													<?php checked( 'no', $default_options['view_poll_archive_link'] ); ?>
													id="shivs-poll-view-poll-archive-link-no" type="radio"
													value="no" name="shivs_poll_options[view_poll_archive_link]"/><label
													for="shivs-poll-view-poll-archive-link-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-view-poll-archive-link-div" style="<?php echo 'yes' != $default_options['view_poll_archive_link'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'View Poll Archive Link Label', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-view-poll-archive-link-label"
													type="text"
													name="shivs_poll_options[view_poll_archive_link_label]"
													value="<?php echo esc_html( stripslashes( $default_options['view_poll_archive_link_label'] ) ); ?>"/>
											</td>
										</tr>
										<tr id="shivs-poll-view-poll-archive-link-div" style="<?php echo 'yes' != $default_options['view_poll_archive_link'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Poll Archive Url', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-poll-archive-url" type="text"
													name="shivs_poll_options[poll_archive_url]"
													value="<?php echo esc_html( stripslashes( $default_options['poll_archive_url'] ) ); ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Show Poll In Archive ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-show-in-archive-yes"><input
														<?php checked( 'yes', $default_options['show_in_archive'] ); ?>
														id="shivs-poll-show-in-archive-yes" type="radio" value="yes"
													name="shivs_poll_options[show_in_archive]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-show-in-archive-no"><input
														<?php checked( 'no', $default_options['show_in_archive'] ); ?>
														id="shivs-poll-show-in-archive-no" type="radio" value="no"
													name="shivs_poll_options[show_in_archive]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-show-in-archive-div" style="<?php echo 'yes' != $default_options['show_in_archive'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Archive Order', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-show-in-archive-order" type="text"
													name="shivs_poll_options[archive_order]"
													value="<?php echo $default_options['archive_order']; ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Archive Polls Per Page', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-archive-polls-per-page" type="text"
													name="shivs_poll_options[archive_polls_per_page]"
													value="<?php echo $default_options['archive_polls_per_page']; ?>"/>
											</td>
										</tr>

									</table>
								</div>
							</div>

							<div class="postbox" id="shivs-poll-advanced-options-div8">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Notifications Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
										<tr>
											<th>
												<?php _e( 'Send Email Notifications', 'shivs_poll' ); ?>:
											</th>
											<td><input <?php checked( 'yes', $default_options['send_email_notifications'] ); ?>
													id="shivs-poll-send-email-notifications-yes" type="radio"
													value="yes" name="shivs_poll_options[send_email_notifications]"/><label
													for="shivs-poll-send-email-notifications-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<input <?php checked( 'no', $default_options['send_email_notifications'] ); ?>
													id="shivs-poll-send-email-notifications-no" type="radio"
													value="no" name="shivs_poll_options[send_email_notifications]"/><label
													for="shivs-poll-send-email-notifications-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-from-name-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications From Name', 'shivs_poll' ); ?>:
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-from-name"
													type="text"
													name="shivs_poll_options[email_notifications_from_name]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_from_name'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-from-email-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications From Email', 'shivs_poll' ); ?>:
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-from-email"
													type="text"
													name="shivs_poll_options[email_notifications_from_email]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_from_email'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-recipients-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Recipients', 'shivs_poll' ); ?>
												:<br><font size="0"><?php _e( 'Use comma separated email addresses: email@xmail.com,email2@xmail.com', 'shivs_poll' ) ?></font>
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-recipients"
													type="text"
													name="shivs_poll_options[email_notifications_recipients]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_recipients'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-subject-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Subject', 'shivs_poll' ); ?>:
											</th>
											<td>
												<input id="shivs-poll-email-notifications-subject"
													type="text"
													name="shivs_poll_options[email_notifications_subject]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_subject'] ) ); ?>"
													/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-body-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Body', 'shivs_poll' ); ?>:
											</th>
											<td>
												<textarea id="shivs-poll-email-notifications-body" rows="10"
													name="shivs_poll_options[email_notifications_body]"><?php echo esc_html( stripslashes( $default_options['email_notifications_body'] ) ); ?></textarea>
											</td>
										</tr>
									</table>
								</div>
							</div>

							<?php if ( false ){ ?>
								<div class="postbox" id="shivs-poll-advanced-options-div9">
									<div title="Click to toggle" class="handlediv">
										<br/>
									</div>
									<h3 class="hndle">
										<span><?php _e( 'Facebook Share Options', 'shivs_poll' ); ?>
											<?php if ( $pro_options['pro_user'] == 'no' ){ ?>
												<font size="-1">(<?php _e( 'Available only for pro version of Shivs Poll', 'shivs_poll' ); ?>
													)</font>
												<?php } ?>
										</span>
									</h3>
									<div class="inside">
										<table cellspacing="0" class="links-table">
											<tbody>
											<tr>
												<th>
													<?php _e( 'Share After Vote ', 'shivs_poll' ); ?>:
												</th>
												<td><input
														<?php checked( 'yes', $default_options['share_after_vote'] ); ?>
														id="shivs-poll-share-after-vote-yes" type="radio"
														value="yes" name="shivs_poll_options[share_after_vote]"/><label
														for="shivs-poll-share-after-vote-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<input
														<?php checked( 'no', $default_options['share_after_vote'] ); ?>
														id="shivs-poll-share-after-vote-no" type="radio"
														value="no" name="shivs_poll_options[share_after_vote]"/><label
														for="shivs-poll-share-after-vote-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-name-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Name', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-name"
														type="text"
														name="shivs_poll_options[share_name]"
														value="<?php echo esc_html( stripslashes( $default_options['share_name'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-caption-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Caption', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-caption"
														type="text"
														name="shivs_poll_options[share_caption]"
														value="<?php echo esc_html( stripslashes( $default_options['share_caption'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-description-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Description', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-description"
														type="text"
														name="shivs_poll_options[share_description]"
														value="<?php echo esc_html( stripslashes( $default_options['share_description'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-picture-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Picture', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-picture"
														type="text"
														name="shivs_poll_options[share_picture]"
														value="<?php echo esc_html( stripslashes( $default_options['share_picture'] ) ); ?>"/>
												</td>
											</tr>

										</table>
									</div>
								</div>
								<?php } ?>
						</div>
						<input name="Submit" class="button-primary" type="submit"
							value="<?php _e( 'Save Changes', 'shivs_poll' ); ?>"/>
					</div>

					<div class="postbox-container" id="postbox-container-1">
						<div class="meta-box-sortables ui-sortable" id="side-sortables">
							<div class="postbox " id="linksubmitdiv">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Save Changes', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<div id="submitlink" class="submitbox">
										<div id="major-publishing-actions">


											<div id="publishing-action">
												<input name="Submit" class="button-primary" type="submit"
													value="<?php _e( 'Save Changes', 'shivs_poll' ); ?>"/>
											</div>
											<div class="clear"></div>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>

	<?php
	}

	public function view_add_edit_new_poll() {
		global $shivs_poll_add_new_config, $action, $current_user;
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$shivs_poll_model  = new Shivs_Poll_Model ();
		$page_name       = __( 'Add New Poll', 'shivs_poll' );
		$action_type     = 'add-new';
		$poll_id         = '';
		$default_options = get_option( 'shivs_poll_options', array() );
		if ( 'edit' == $action ){
			$poll_id     = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
			$poll_author = Shivs_Poll_Model::get_poll_field_from_database_by_id( 'poll_author', $poll_id );
			if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_author != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) )
				wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
			$shivs_poll_model       = new Shivs_Poll_Model ( $poll_id );
			$answers              = Shivs_Poll_Model::get_poll_answers( $poll_id );
			$other_answer         = Shivs_Poll_Model::get_poll_answers( $poll_id, array( 'other' ) );
			$custom_fields        = Shivs_Poll_Model::get_poll_customfields( $poll_id );
			$page_name            = __( 'Edit Poll', 'shivs_poll' );
			$action_type          = 'edit';
			$poll_default_options = get_shivs_poll_meta( $poll_id, 'options', true );
			foreach ( $default_options as $option_name => $option_value ) {
				if ( isset ( $poll_default_options [$option_name] ) ){
					$default_options [$option_name] = $poll_default_options [$option_name];
				}
			}
		}
		$current_poll        = $shivs_poll_model->get_current_poll();
		$answers_number      = $shivs_poll_add_new_config ['default_number_of_answers'];
		$customfields_number = $shivs_poll_add_new_config ['default_number_of_customfields'];
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php print $page_name; ?><?php if ('edit' == $action): ?><a
					class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-add-new', 'action' => false, 'id' => false ) ) ); ?>"><?php _e( 'Add New', 'shivs_poll' ); ?></a><?php endif; ?></h2>
		<div id="message"></div>
		<form method="post" name="shivs_poll_edit_add_new_form"
			id="shivs-poll-edit-add-new-form">
			<?php wp_nonce_field( 'shivs-poll-edit-add-new' ); ?>
			<span <?php if ( 'edit' != $action ){ ?> style="display: none;"
					<?php } ?>> Shortcode: <input id="shivs_poll_shortcode" type="text"
					value='[shivs_poll id="<?php echo $current_poll['id']; ?>"]'
					readonly="readonly">
			</span>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="stuffbox" id="shivs-poll-namediv">
							<h3>
								<label for="shivs-poll-name"><?php _e( 'Poll Name', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<input type="text" id="shivs-poll-name"
									value="<?php echo esc_html( stripslashes( $current_poll['name'] ) ); ?>"
									tabindex="1" name="shivs_poll_name" size="30"/>
								<p><?php _e( 'Example: Test Poll', 'shivs_poll' ); ?></p>
							</div>
						</div>
						<div class="stuffbox" id="shivs-poll-questiondiv">
							<h3>
								<label for="shivs-poll-question"><?php _e( 'Question', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<input type="text" id="shivs-poll-question"
									value="<?php echo esc_html( stripslashes( $current_poll['question'] ) ); ?>"
									tabindex="1" name="shivs_poll_question" size="30"/>
								<p><?php _e( 'Example: How is my plugin?', 'shivs_poll' ); ?></p>
							</div>
						</div>
						<div class="stuffbox" id="shivs-poll-answersdiv">
							<h3>
								<span><?php _e( 'Answers', 'shivs_poll' ); ?></span>
							</h3>
							<div class="inside">
								<table cellspacing="0" class="links-table"
									id='shivs-poll-answer-table'>
									<tbody>
										<?php
											for ($answer_id = 1;
												$answer_id < $answers_number;
												$answer_id++) {
												if ( isset ( $answers [$answer_id - 1] ) ){
													$answer_options = get_shivs_poll_answer_meta( $answers [$answer_id - 1] ['id'], 'options' );
												}
											?>
											<tr class="shivs_poll_tr_answer"
												id="shivs_poll_tr_answer<?php echo $answer_id ?>">
												<th scope="row"><label class="shivs_poll_answer_label"
														for="shivs-poll-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_answer']; ?> <?php echo $answer_id ?></label></th>
												<td><input type="hidden"
														value="<?php echo isset( $answers[$answer_id - 1]['id'] ) ? $answers[$answer_id - 1]['id'] : ''; ?>"
														name="shivs_poll_answer_ids[answer<?php echo $answer_id ?>]"/>
													<input type="text"
														value="<?php echo isset( $answers[$answer_id - 1]['answer'] ) ? esc_html( stripslashes( $answers[$answer_id - 1]['answer'] ) ) : ''; ?>"
														id="shivs-poll-answer<?php echo $answer_id ?>"
														name="shivs_poll_answer[answer<?php echo $answer_id ?>]"/></td>
												<td align="right">
													<?php if ( 'edit' == $action ){ ?>
														<input type="button"
															value="<?php echo $shivs_poll_add_new_config['text_change_votes_number_answer']; ?> (<?php echo $answers[$answer_id - 1]['votes'] ?>)"
															onclick="shivs_poll_show_change_votes_number_answer(<?php echo $answers [$answer_id - 1] ['id'] ?>); return false;"
															class="button shivs-poll-change-no-votes-buttons" id="shivs-poll-change-no-votes-button-<?php echo $answers [$answer_id - 1] ['id'] ?>"/>
														<?php } ?>
													<input type="button"
														value="<?php echo $shivs_poll_add_new_config['text_customize_answer']; ?>"
														onclick="shivs_poll_toogle_customize_answer('#shivs-poll-answer-table', <?php echo $answer_id ?>); return false;"
														class="button"/>
													<input
														onclick="shivs_poll_remove_answer('#shivs-poll-answer-table', <?php echo $answer_id ?>); return false;"
														type="button"
														value="<?php echo $shivs_poll_add_new_config['text_remove_answer']; ?>"
														class="button"/></td>
											</tr>
											<tr class="shivs_poll_tr_customize_answer"
												id="shivs_poll_tr_customize_answer<?php echo $answer_id ?>"
												style="display: none;">
												<td colspan="3">
													<table cellspacing="0" width="100%">
														<tbody>
															<tr>
																<th>
																	<?php echo $shivs_poll_add_new_config['text_is_default_answer']; ?>
																	:
																</th>
																<td valign="top"><input
																		id="shivs-poll-is-default-answer-no-<?php echo $answer_id ?>"
																		<?php echo checked( 'no', isset ( $answer_options[0]['is_default_answer'] ) ? $answer_options[0]['is_default_answer'] : $default_options['is_default_answer'] ); ?>
																		type="radio"
																		name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][is_default_answer]"
																		value="no"/> <label
																		for="shivs-poll-id-default-answer-no-<?php echo $answer_id ?>"><?php _e( 'No', 'shivs_poll' ); ?></label>&nbsp;|&nbsp;
																	<input
																		id="shivs-poll-is-default-answer-yes-<?php echo $answer_id ?>"
																		<?php echo checked( 'yes', isset ( $answer_options[0]['is_default_answer'] ) ? $answer_options[0]['is_default_answer'] : $default_options['is_default_answer'] ); ?>
																		type="radio"
																		name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][is_default_answer]"
																		value="yes"/> <label
																		for="shivs-poll-id-default-answer-yes-<?php echo $answer_id ?>"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
																</td>
															</tr>
														</tbody>
													</table>
													<table cellspacing="0" width="100%">
														<tbody>
															<tr>
																<th>
																	<?php echo $shivs_poll_add_new_config['text_poll_bar_style']['use_template_bar_label']; ?>
																	:
																</th>
																<td><input
																		onclick="jQuery('#shivs-poll-answer-use-template-bar-table-<?php echo $answer_id ?>').show();"
																		id="shivs-poll-answer-use-template-bar-no-<?php echo $answer_id ?>"
																		<?php echo checked( 'no', isset ( $answer_options[0]['use_template_bar'] ) ? $answer_options[0]['use_template_bar'] : $default_options['use_template_bar'] ); ?>
																		type="radio"
																		name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][use_template_bar]"
																		value="no"/> <label
																		for="shivs-poll-answer-use-template-bar-no-<?php echo $answer_id ?>"><?php _e( 'No', 'shivs_poll' ); ?></label>&nbsp;|&nbsp;
																	<input
																		onclick="jQuery('#shivs-poll-answer-use-template-bar-table-<?php echo $answer_id ?>').hide();"
																		id="shivs-poll-answer-use-template-bar-yes-<?php echo $answer_id ?>"
																		<?php echo checked( 'yes', isset ( $answer_options[0]['use_template_bar'] ) ? $answer_options[0]['use_template_bar'] : $default_options['use_template_bar'] ); ?>
																		type="radio"
																		name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][use_template_bar]"
																		value="yes"/> <label
																		for="shivs-poll-answer-use-template-bar-yes-<?php echo $answer_id ?>"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
																</td>
															</tr>
														</tbody>
													</table>
													<table cellspacing="0" width="100%" id="shivs-poll-answer-use-template-bar-table-<?php echo $answer_id ?>" style="<?php echo( 'yes' == ( isset ( $answer_options[0]['use_template_bar'] ) ? $answer_options[0]['use_template_bar'] : $default_options['use_template_bar'] ) ? 'display: none;' : '' ); ?>">
														<tbody>
															<tr>
																<th><label><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_label']; ?></label>
																</th>
																<td>
																	<table cellspacing="0" style="margin-left: 0px;"
																		style="width:100%">
																		<tbody>
																			<tr>
																				<th><label
																						for="shivs-poll-answer-option-bar-background-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_background_label']; ?></label>
																				</th>
																				<td>#<input
																						id="shivs-poll-answer-option-bar-background-answer<?php echo $answer_id ?>"
																						value="<?php echo isset ( $answer_options[0]['bar_background'] ) ? $answer_options[0]['bar_background'] : $default_options['bar_background']; ?>"
																						onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview<?php echo $answer_id ?>', 'background-color', '#' + this.value)"
																						type="text"
																						name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][bar_background]"/>
																				</td>
																			</tr>
																			<tr>
																				<th><label
																						for="shivs-poll-answer-option-bar-height-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_height_label']; ?></label>
																				</th>
																				<td><input
																						id="shivs-poll-answer-option-bar-height-answer<?php echo $answer_id ?>"
																						value="<?php echo isset ( $answer_options[0]['bar_height'] ) ? $answer_options[0]['bar_height'] : $default_options['bar_height']; ?>"
																						onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview<?php echo $answer_id ?>', 'height', this.value + 'px')"
																						type="text"
																						name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][bar_height]"/>
																					px</td>
																			</tr>
																			<tr>
																				<th><label
																						for="shivs-poll-answer-option-bar-border-color-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_color_label']; ?></label>
																				</th>
																				<td>#<input
																						id="shivs-poll-answer-option-bar-border-color-answer<?php echo $answer_id ?>"
																						value="<?php echo isset ( $answer_options[0]['bar_border_color'] ) ? $answer_options[0]['bar_border_color'] : $default_options['bar_border_color']; ?>"
																						onblur="shivs_poll_update_bar_style( '#shivs-poll-bar-preview<?php echo $answer_id ?>', 'border-color', '#' + this.value )"
																						type="text"
																						name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][bar_border_color]"/>
																				</td>
																			</tr>
																			<tr>
																				<th><label
																						for="shivs-poll-answer-option-bar-border-width-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_width_label']; ?></label>
																				</th>
																				<td><input
																						id="shivs-poll-answer-option-bar-border-width-answer<?php echo $answer_id ?>"
																						value="<?php echo isset ( $answer_options[0]['bar_border_width'] ) ? $answer_options[0]['bar_border_width'] : $default_options['bar_border_width']; ?>"
																						onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview<?php echo $answer_id ?>', 'border-width', this.value + 'px')"
																						type="text"
																						name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][bar_border_width]"/>
																					px</td>
																			</tr>
																			<tr>
																				<th><label
																						for="shivs-poll-answer-option-bar_border-style-answer<?php echo $answer_id ?>"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_style_label']; ?></label>
																				</th>
																				<td><select
																						id="shivs-poll-answer-option-bar_border-style-answer<?php echo $answer_id ?>"
																						onchange="shivs_poll_update_bar_style('#shivs-poll-bar-preview<?php echo $answer_id ?>', 'border-style', this.value)"
																						name="shivs_poll_answer_options[answer<?php echo $answer_id ?>][bar_border_style]">
																						<option
																							<?php print ( 'solid' == ( isset ( $answer_options[0]['bar_border_style'] ) ? $answer_options[0]['bar_border_style'] : $default_options['bar_border_style'] ) ) ? 'selected="selected"' : ''; ?>
																							value="solid">Solid</option>
																						<option
																							<?php print ( 'dashed' == ( isset ( $answer_options[0]['bar_border_style'] ) ? $answer_options[0]['bar_border_style'] : $default_options['bar_border_style'] ) ) ? 'selected="selected"' : ''; ?>
																							value="dashed">Dashed</option>
																						<option
																							<?php print ( 'dotted' == ( isset ( $answer_options[0]['bar_border_style'] ) ? $answer_options[0]['bar_border_style'] : $default_options['bar_border_style'] ) ) ? 'selected="selected"' : ''; ?>
																							value="dotted">Dotted</option>
																					</select></td>
																			</tr>
																		</tbody>
																	</table>
																</td>
															</tr>
															<tr>
																<th><label><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_preview_label']; ?></label>
																</th>
																<td>
																	<div id="shivs-poll-bar-preview<?php echo $answer_id ?>"; style="width: 100px; height: <?php echo isset ( $answer_options[0]['bar_height'] ) ? $answer_options[0]['bar_height'] : $default_options['bar_height']; ?>
																	px; background-color:#<?php

																		echo isset ( $answer_options [0] ['bar_background'] ) ? $answer_options [0] ['bar_background'] : $default_options ['bar_background'];
																	?>
																	; border-style: <?php echo isset ( $answer_options[0]['bar_border_style'] ) ? $answer_options[0]['bar_border_style'] : $default_options['bar_border_style']; ?>
																	; border-width: <?php echo isset ( $answer_options[0]['bar_border_width'] ) ? $answer_options[0]['bar_border_width'] : $default_options['bar_border_width']; ?>
																	px; border-color: #<?php

																		echo isset ( $answer_options [0] ['bar_border_color'] ) ? $answer_options [0] ['bar_border_color'] : $default_options ['bar_border_color'];
																	?>;"></div>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
											<?php } ?>
									</tbody>
								</table>
								<p id="shivs-poll-add-answer-holder" style="display: block;">
									<button id="shivs-poll-add-answer-button" class="button"><?php _e( 'Add New Answer', 'shivs_poll' ) ?></button>
									<button id="shivs-poll-answers-advanced-options-button"
										class="button"><?php _e( 'Answers Advanced Options', 'shivs_poll' ); ?></button>
								</p>

								<table cellspacing="0" id="shivs-poll-answers-advanced-options-div"
									style="display: none;" class="links-table">
									<tbody>
										<tr>
											<th>
												<?php _e( 'Allow other answers ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-allow-other-answers-no"><input
														id="shivs-poll-allow-other-answers-no"
														<?php echo 'no' == $default_options['allow_other_answers'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[allow_other_answers]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-allow-other-answers-yes"><input
														id="shivs-poll-allow-other-answers-yes"
														<?php echo 'yes' == $default_options['allow_other_answers'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[allow_other_answers]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<?php if ( 'edit' == $action ){ ?>
													<input type="button"
														value="<?php echo $shivs_poll_add_new_config['text_change_votes_number_answer']; ?> (<?php echo $other_answer[0]['votes'] ?>)"
														onclick="shivs_poll_show_change_votes_number_answer(<?php echo $other_answer[0] ['id'] ?>); return false;"
														class="button shivs-poll-change-no-votes-buttons" id="shivs-poll-change-no-votes-button-<?php echo $other_answer[0] ['id'] ?>"/>
													<?php } ?>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-other-answers-label-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Other Answer Label', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-other-answers-label" type="text"
													name="shivs_poll_options[other_answers_label]"
													value="<?php echo isset( $other_answer[0]['answer'] ) ? esc_html( stripslashes( $other_answer[0]['answer'] ) ) : $default_options['other_answers_label'] ?>"/>
												<input type="hidden"
													name="shivs_poll_options[other_answers_id]"
													value="<?php echo isset( $other_answer[0]['id'] ) ? $other_answer[0]['id'] : '' ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-other-answers-to-results-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Add the values submitted in "Other" as answers ', 'shivs_poll' ); ?>
												:<br><small><?php _e( 'all the values submitted in this field by your users will be automatically added as an available "Answer"', 'shivs_poll' ) ?></small>
											</th>
											<td>
												<label for="shivs-poll-add-other-answers-to-default-answers-no"><input
														id="shivs-poll-add-other-answers-to-default-answers-no"
														<?php echo 'no' == $default_options['add_other_answers_to_default_answers'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[add_other_answers_to_default_answers]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-add-other-answers-to-default-answers-yes"><input
														id="shivs-poll-add-other-answers-to-default-answers-yes"
														<?php echo 'yes' == $default_options['add_other_answers_to_default_answers'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[add_other_answers_to_default_answers]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-display-other-answers-values-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Display Other Answers Values', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-display-other-answers-values-no"><input
														id="shivs-poll-display-other-answers-values-no"
														<?php echo 'no' == $default_options['display_other_answers_values'] ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[display_other_answers_values]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-display-other-answers-values-yes"><input
														id="shivs-poll-display-other-answers-values-yes"
														<?php echo 'yes' == $default_options['display_other_answers_values'] ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[display_other_answers_values]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label></td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-is-default-other-answers-values-div" style="<?php echo 'no' == $default_options['allow_other_answers'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Make "Other answer" default answer ', 'shivs_poll' ); ?>
												:<br>
												<small><?php _e( '"Other Answer" will be autoselected', 'shivs_poll' ); ?></small>
											</th>
											<td><label for="shivs-poll-is-default-other-answers-no"><input
														id="shivs-poll-is-default-other-answers-no"
														<?php echo $default_options['is_default_answer'] == 'no' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[is_default_answer]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-is-default-other-answers-yes"><input
														id="shivs-poll-is-default-other-answers-yes"
														<?php echo $default_options['is_default_answer'] == 'yes' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[is_default_answer]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label></td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Allow Multiple Answers ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-allow-multiple-answers-no"><input
														id="shivs-poll-allow-multiple-answers-no"
														<?php echo $default_options['allow_multiple_answers'] == 'no' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[allow_multiple_answers]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-allow-multiple-answers-yes"><input
														id="shivs-poll-allow-multiple-answers-yes"
														<?php echo $default_options['allow_multiple_answers'] == 'yes' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[allow_multiple_answers]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label></td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-allow-multiple-answers-div" style="<?php echo $default_options['allow_multiple_answers'] == 'no' ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Number of allowed answers', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-allow-multiple-answers-number"
													type="text"
													name="shivs_poll_options[allow_multiple_answers_number]"
													value="<?php echo $default_options['allow_multiple_answers_number']; ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-allow-multiple-answers-div1" style="<?php echo $default_options['allow_multiple_answers'] == 'no' ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Min Number of allowed answers', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-allow-multiple-answers-min-number"
													type="text"
													name="shivs_poll_options[allow_multiple_answers_min_number]"
													value="<?php echo $default_options['allow_multiple_answers_min_number']; ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Display Answers ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-display-answers-vertical"><input
														id="shivs-poll-display-answers-vertical"
														<?php echo $default_options['display_answers'] == 'vertical' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_answers]"
													value="vertical"/> <?php _e( 'Vertical', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-display-answers-orizontal"><input
														id="shivs-poll-display-answers-orizontal"
														<?php echo $default_options['display_answers'] == 'orizontal' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_answers]"
													value="orizontal"/> <?php _e( 'Horizontal', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-display-answers-tabulated"><input
														id="shivs-poll-display-answers-tabulated"
														<?php echo $default_options['display_answers'] == 'tabulated' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_answers]"
													value="tabulated"/> <?php _e( 'Tabulated', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-display-answers-tabulated-div" style="<?php echo $default_options['display_answers'] != 'tabulated' ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Columns', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-display-answers-tabulated-cols"
													type="text"
													name="shivs_poll_options[display_answers_tabulated_cols]"
													value="<?php echo $default_options['display_answers_tabulated_cols']; ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Display Results ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-display-results-vertical"><input
														id="shivs-poll-display-results-vertical"
														<?php echo $default_options['display_results'] == 'vertical' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_results]"
													value="vertical"> <?php _e( 'Vertical', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-display-results-orizontal"><input
														id="shivs-poll-display-results-orizontal"
														<?php echo $default_options['display_results'] == 'orizontal' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_results]"
													value="orizontal"> <?php _e( 'Horizontal', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-display-results-tabulated"><input
														id="shivs-poll-display-results-tabulated"
														<?php echo $default_options['display_results'] == 'tabulated' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[display_results]"
													value="tabulated"> <?php _e( 'Tabulated', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-display-results-tabulated-div" style="<?php echo $default_options['display_results'] != 'tabulated' ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Columns', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-display-results-tabulated-cols"
													type="text"
													name="shivs_poll_options[display_results_tabulated_cols]"
													value="<?php echo $default_options['display_results_tabulated_cols']; ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Use Template Result Bar', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-use-template-bar-no"><input
														id="shivs-poll-use-template-bar-no"
														<?php echo 'no' == $default_options['use_template_bar'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[use_template_bar]"
													value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
													for="shivs-poll-use-template-bar-yes"><input
														id="shivs-poll-use-template-bar-yes"
														<?php echo 'yes' == $default_options['use_template_bar'] ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[use_template_bar]"
													value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label></td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
											<th><label for="shivs-poll-bar-background"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_background_label']; ?></label>
											</th>
											<td>#<input class="shivs-small-input"
													id="shivs-poll-bar-background"
													value="<?php echo $default_options['bar_background']; ?>"
													onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'background-color', '#' + this.value)"
													type="text" name="shivs_poll_options[bar_background]"/>
											</td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
											<th><label for="shivs-poll-bar-height"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_height_label']; ?></label>
											</th>
											<td><input class="shivs-small-input" id="shivs-poll-bar-height"
													value="<?php echo $default_options['bar_height']; ?>"
													onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'height', this.value + 'px')"
													type="text" name="shivs_poll_options[bar_height]"/> px</td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
											<th><label for="shivs-poll-bar-border-color"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_color_label']; ?></label>
											</th>
											<td>#<input class="shivs-small-input"
													id="shivs-poll-bar-border-color"
													value="<?php echo $default_options['bar_border_color']; ?>"
													onblur="shivs_poll_update_bar_style( '#shivs-poll-bar-preview', 'border-color', '#' + this.value )"
													type="text" name="shivs_poll_options[bar_border_color]"/>
											</td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
											<th><label for="shivs-poll-bar-border-width"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_width_label']; ?></label>
											</th>
											<td><input class="shivs-small-input"
													id="shivs-poll-bar-border-width"
													value="<?php echo $default_options['bar_border_width']; ?>"
													onblur="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'border-width', this.value + 'px')"
													type="text" name="shivs_poll_options[bar_border_width]"/> px</td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">
											<th><label for="shivs-poll-bar-border-style"><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_style_border_style_label']; ?></label>
											</th>
											<td><select id="shivs-poll-bar-border-style"
													onchange="shivs_poll_update_bar_style('#shivs-poll-bar-preview', 'border-style', this.value)"
													name="shivs_poll_options[bar_border_style]">
													<option
														<?php print 'solid' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?>
														value="solid">Solid</option>
													<option
														<?php print 'dashed' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?>
														value="dashed">Dashed</option>
													<option
														<?php print 'dotted' == $default_options['bar_border_style'] ? 'selected="selected"' : ''; ?>
														value="dotted">Dotted</option>
												</select></td>
										</tr>
										<tr class="shivs-poll-custom-result-bar-table shivs_poll_suboption" style="<?php echo $default_options['use_template_bar'] == 'yes' ? 'display: none;' : ''; ?>">

											<th><label><?php echo $shivs_poll_add_new_config['text_poll_bar_style']['poll_bar_preview_label']; ?></label>
											</th>
											<td>
												<div id="shivs-poll-bar-preview"; style="width: 100px; height: <?php echo $default_options['bar_height']; ?>
												px; background-color:#<?php

													echo $default_options ['bar_background'];
												?>
												; border-style: <?php echo $default_options['bar_border_style']; ?>
												; border-width: <?php echo $default_options['bar_border_width']; ?>
												px; border-color: #<?php

													echo $default_options ['bar_border_color'];
												?>;"></div>
											</td>
										</tr>
										<tr>
											<th><?php _e( 'Sort Answers', 'shivs_poll' ); ?>:</th>
											<td valign="top"><label for="shivs_poll_sorting_answers_exact"><input
														id="shivs_poll_sorting_answers_exact"
														<?php echo $default_options['sorting_answers'] == 'exact' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_answers]"
													value="exact"> <?php _e( 'Exact Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_answers_alphabetical"><input
														id="shivs_poll_sorting_answers_alphabetical"
														<?php echo $default_options['sorting_answers'] == 'alphabetical' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_answers]"
													value="alphabetical"> <?php _e( 'Alphabetical Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_answers_random"><input
														id="shivs_poll_sorting_answers_random"
														<?php echo $default_options['sorting_answers'] == 'random' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_answers]"
													value="random"> <?php _e( 'Random Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_answers_votes"><input
														id="shivs_poll_sorting_answers_votes"
														<?php echo $default_options['sorting_answers'] == 'votes' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_answers]"
													value="votes"> <?php _e( 'Number of Votes', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Sort Answers Rule', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs_poll_sorting_answers_asc"><input
														id="shivs_poll_sorting_answers_asc"
														<?php echo $default_options['sorting_answers_direction'] == 'asc' ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[sorting_answers_direction]"
													value="asc"> <?php _e( 'Ascending', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_answers_desc"><input
														id="shivs_poll_sorting_answers_desc"
														<?php echo $default_options['sorting_answers_direction'] == 'desc' ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[sorting_answers_direction]"
													value="desc"> <?php _e( 'Descending', 'shivs_poll' ); ?> </label>
											</td>
										</tr>
										<tr>
											<th><?php _e( 'Sorting Results in', 'shivs_poll' ); ?>:</th>
											<td valign="top"><label for="shivs_poll_sorting_results_exact"><input
														id="shivs_poll_sorting_results_exact"
														<?php echo $default_options['sorting_results'] == 'exact' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_results]"
													value="exact"> <?php _e( 'Exact Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_results_alphabetical"><input
														id="shivs_poll_sorting_results_alphabetical"
														<?php echo $default_options['sorting_results'] == 'alphabetical' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_results]"
													value="alphabetical"> <?php _e( 'Alphabetical Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_results_random"><input
														id="shivs_poll_sorting_results_random"
														<?php echo $default_options['sorting_results'] == 'random' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_results]"
													value="random"> <?php _e( 'Random Order', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_results_votes"><input
														id="shivs_poll_sorting_results_votes"
														<?php echo $default_options['sorting_results'] == 'votes' ? 'checked="checked"' : ''; ?>
														type="radio" name="shivs_poll_options[sorting_results]"
													value="votes"> <?php _e( 'Number of Votes', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Sorting Results Rule', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs_poll_sorting_results_asc"><input
														id="shivs_poll_sorting_results_asc"
														<?php echo $default_options['sorting_results_direction'] == 'asc' ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[sorting_results_direction]"
													value="asc"> <?php _e( 'Ascending', 'shivs_poll' ); ?></label>
												<label for="shivs_poll_sorting_results_desc"><input
														id="shivs_poll_sorting_results_desc"
														<?php echo $default_options['sorting_results_direction'] == 'desc' ? 'checked="checked"' : ''; ?>
														type="radio"
														name="shivs_poll_options[sorting_results_direction]"
													value="desc"> <?php _e( 'Descending', 'shivs_poll' ); ?></label>
											</td>
										</tr>
									</tbody>
								</table>

							</div>
						</div>
						<div class="stuffbox" id="shivs-poll-customfieldsdiv">
							<h3>
								<span><?php _e( 'Custom Text Fields', 'shivs_poll' ); ?></span>
							</h3>
							<div class="inside">
								<table cellspacing="0" class="links-table"
									id='shivs-poll-customfields-table'>
									<tbody>
										<?php
											for ( $custom_field_id = 1; $custom_field_id < $customfields_number; $custom_field_id++ ) {
												if ( isset ( $custom_fields [$custom_field_id - 1] ['id'] ) ){
												?>
												<tr class="shivs_poll_tr_customfields"
													id="shivs_poll_tr_customfield<?php echo $custom_field_id; ?>">
													<th scope="row"><label class="shivs_poll_customfield_label"
															for="shivs_poll_customfield<?php echo $custom_field_id; ?>"><?php echo $shivs_poll_add_new_config['text_customfield'] ?> <?php echo $custom_field_id ?></label>
													</th>
													<td><input type="hidden"
															value="<?php echo isset( $custom_fields[$custom_field_id - 1]['id'] ) ? $custom_fields[$custom_field_id - 1]['id'] : ''; ?>"
															name="shivs_poll_customfield_ids[customfield<?php echo $custom_field_id ?>]"/>
														<input type="text"
															value="<?php echo isset( $custom_fields[$custom_field_id - 1]['custom_field'] ) ? $custom_fields[$custom_field_id - 1]['custom_field'] : ''; ?>"
															id="shivs-poll-customfield<?php echo $custom_field_id ?>"
															name="shivs_poll_customfield[customfield<?php echo $custom_field_id ?>]"/>
														<input value="yes"
															<?php if ( isset ( $custom_fields[$custom_field_id - 1]['required'] ) )
																	echo ( 'yes' == $custom_fields[$custom_field_id - 1]['required'] ) ? 'checked="checked"' : ''; ?>
															id="shivs-poll-customfield-required-<?php echo $custom_field_id ?>"
															type="checkbox"
															name="shivs_poll_customfield_required[customfield<?php echo $custom_field_id ?>]"/>
														<label
															for="shivs-poll-customfield-required-<?php echo $custom_field_id ?>"><?php echo $shivs_poll_add_new_config['text_requiered_customfield'] ?></label>
													</td>
													<td align="right"><input
															onclick="shivs_poll_remove_customfield( '#shivs-poll-customfields-table', <?php echo $custom_field_id ?> ); return false;"
															type="button"
															value="<?php echo $shivs_poll_add_new_config['text_remove_customfield']; ?>"
															class="button"/></td>
												</tr>
												<?php
												}
											}
										?>
									</tbody>
								</table>
								<p id="shivs-poll-add-customfield-holder" style="display: block;">
									<button id="shivs-poll-add-customfield-button" class="button"><?php _e( 'Add New Custom Field', 'shivs_poll' ) ?></button>
								</p>
							</div>
						</div>
						<div class="meta-box-sortables ui-sortable" id="normal-sortables">
							<div class="postbox" id="shivs-poll-advanced-options-div">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Poll Start/End Date', 'shivs_poll' ); ?>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th><label for="shivs-poll-start-date-input"><?php _e( 'Start Date', 'shivs_poll' ); ?>
														:</label><br><small>(<?php _e( 'Current Server Time', 'shivs_poll' );
															echo ': ' . current_time( 'mysql' ); ?>)</small>
												</th>
												<td><input id="shivs-poll-start-date-input" type="text"
														name="shivs_poll_options[start_date]"
														value="<?php echo ( 'edit' != $action ) ? current_time( 'mysql' ) : ( '' == $default_options['start_date'] ) ? current_time( 'mysql' ) : $default_options['start_date']; ?>"/>
												</td>
											</tr>
											<tr>
												<th><label for="shivs-poll-end-date-input"><?php _e( 'End Date ', 'shivs_poll' ); ?>
														:</label><br><small>(<?php _e( 'Current Server Time', 'shivs_poll' );
															echo ': ' . current_time( 'mysql' ); ?>)</small>
												</th>
												<td><input style="<?php echo 'yes' == $default_options['never_expire'] ? 'display: none;' : ''; ?>" <?php echo 'yes' == $default_options['never_expire'] ? 'disabled="disabled"' : ''; ?> id="shivs-poll-end-date-input" type="text" name="shivs_poll_options[end_date]" value="<?php echo '' == $default_options['end_date'] ? '' : $default_options['end_date']; ?>"/>
													<label for="shivs-poll-never-expire"><input type="checkbox"
															<?php echo $default_options['never_expire'] == 'yes' ? 'checked="checked"' : ''; ?>
															id="shivs-poll-never-expire"
														name="shivs_poll_options[never_expire]" value="yes"/> <?php _e( 'No end date', 'shivs_poll' ); ?></label>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'View Results Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'View Results', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-before-vote"><input
															class="shivs-poll-view-results-hide-custom"
															<?php echo 'before' == $default_options['view_results'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-before-vote" type="radio"
														value="before" name="shivs_poll_options[view_results]"/> <?php _e( 'Before Vote', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-after-vote"><input
															class="shivs-poll-view-results-hide-custom"
															<?php echo 'after' == $default_options['view_results'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-after-vote" type="radio"
														value="after" name="shivs_poll_options[view_results]"/> <?php _e( 'After Vote', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-after-poll-end-date"><input
															class="shivs-poll-view-results-hide-custom"
															<?php echo 'after-poll-end-date' == $default_options['view_results'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-after-poll-end-date" type="radio"
															value="after-poll-end-date"
														name="shivs_poll_options[view_results]"/> <?php _e( 'After Poll End Date', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-never"><input
															class="shivs-poll-view-results-hide-custom"
															<?php echo 'never' == $default_options['view_results'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-never" type="radio" value="never"
														name="shivs_poll_options[view_results]"/> <?php _e( 'Never', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-custom"><input
															class="shivs-poll-view-results-show-custom"
															<?php echo 'custom-date' == $default_options['view_results'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-custom" type="radio"
														value="custom-date" name="shivs_poll_options[view_results]"/> <?php _e( 'Custom Date', 'shivs_poll' ); ?></label>
													<div id="shivs-poll-display-view-results-div" style="<?php echo 'custom-date' != $default_options['view_results'] ? 'display: none;' : ''; ?>">
														<label for="shivs-poll-view-results-start-date"><?php _e( 'Results display date (the users will be able to see the results starting with this date)', 'shivs_poll' ); ?>
															:</label>
														<input id="shivs-poll-view-results-start-date" type="text"
															name="shivs_poll_options[view_results_start_date]"
															value="<?php echo $default_options['view_results_start_date']; ?>">
													</div></td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Results Permissions', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-permissions-quest-only"><input
															id="shivs-poll-view-results-permissions-quest-only"
															<?php echo 'quest-only' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="quest-only"
														name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Guest Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-permissions-registered-only"><input
															id="shivs-poll-view-results-permissions-registered-only"
															<?php echo 'registered-only' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="registered-only"
														name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Registered Users Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-permissions-guest-registered"><input
															id="shivs-poll-view-results-permissions-guest-registered"
															<?php echo 'guest-registered' == $default_options['view_results_permissions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="guest-registered"
														name="shivs_poll_options[view_results_permissions]"/> <?php _e( 'Guest &amp; Registered Users', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Results Display', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-votes-number"><input
															id="shivs-poll-view-results-votes-number"
															<?php echo 'votes-number' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="votes-number"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'By Votes Number', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-percentages"><input
															id="shivs-poll-view-results-percentages"
															<?php echo 'percentages' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="percentages"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'Percentages', 'shivs_poll' ); ?></label>
													<label
														for="shivs-poll-view-results-votes-number-and-percentages"><input
															id="shivs-poll-view-results-votes-number-and-percentages"
															<?php echo 'votes-number-and-percentages' == $default_options['view_results_type'] ? 'checked="checked"' : ''; ?>
															type="radio" value="votes-number-and-percentages"
														name="shivs_poll_options[view_results_type]"/> <?php _e( 'by Votes Number and Percentages', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Answer Result Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-answer-result-label" type="text"
														name="shivs_poll_options[answer_result_label]"
														value="<?php echo esc_html( stripslashes( $default_options['answer_result_label'] ) ); ?>"/>
													<small><i><?php _e( 'Use %POLL-ANSWER-RESULT-PERCENTAGES% for showing answer percentages and  %POLL-ANSWER-RESULT-VOTES% for showing answer number of votes', 'shivs_poll' ); ?></i></small>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Answer Result Votes Number Label', 'shivs_poll' ); ?>
													:
												</th>
												<td>
													<?php _e( 'Singular', 'shivs_poll' ); ?>
													<input
														id="shivs-poll-singular-answer-result-votes-number-label"
														type="text"
														name="shivs_poll_options[singular_answer_result_votes_number_label]"
														value="<?php echo esc_html( stripslashes( $default_options['singular_answer_result_votes_number_label'] ) ); ?>"/>
													<?php _e( 'Plural', 'shivs_poll' ); ?>
													<input
														id="shivs-poll-plural-answer-result-votes-number-label"
														type="text"
														name="shivs_poll_options[plural_answer_result_votes_number_label]"
														value="<?php echo esc_html( stripslashes( $default_options['plural_answer_result_votes_number_label'] ) ); ?>"/>

												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Vote Button Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-vote-button-label" type="text"
														name="shivs_poll_options[vote_button_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_button_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Results Link', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-results-link-yes"><input
															<?php echo 'yes' == $default_options['view_results_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-link-yes" type="radio"
														value="yes" name="shivs_poll_options[view_results_link]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-results-link-no"><input
															<?php echo 'no' == $default_options['view_results_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-results-link-no" type="radio" value="no"
														name="shivs_poll_options[view_results_link]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-results-link-div" style="<?php echo 'yes' != $default_options['view_results_link'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Results Link Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-results-link-label" type="text"
														name="shivs_poll_options[view_results_link_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_results_link_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Back To Vote Link ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-back-to-vote-link-yes"><input
															<?php echo 'yes' == $default_options['view_back_to_vote_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-back-to-vote-link-yes" type="radio"
														value="yes" name="shivs_poll_options[view_back_to_vote_link]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-back-to-vote-link-no"><input
															<?php echo 'no' == $default_options['view_back_to_vote_link'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-back-to-vote-link-no" type="radio"
														value="no" name="shivs_poll_options[view_back_to_vote_link]"/><?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-back-to-vote-link-div" style="<?php echo 'yes' != $default_options['view_back_to_vote_link'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Back To Vote Link Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-back-to-vote-link-label"
														type="text"
														name="shivs_poll_options[view_back_to_vote_link_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_back_to_vote_link_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Total Votes ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-total-votes-yes"><input
															<?php echo 'yes' == $default_options['view_total_votes'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-votes-yes" type="radio" value="yes"
														name="shivs_poll_options[view_total_votes]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-total-votes-no"><input
															<?php echo 'no' == $default_options['view_total_votes'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-votes-no" type="radio" value="no"
														name="shivs_poll_options[view_total_votes]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-total-votes-div" style="<?php echo 'yes' != $default_options['view_total_votes'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Total Votes Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-total-votes-label" type="text"
														name="shivs_poll_options[view_total_votes_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_total_votes_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'View Total Answers ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-view-total-answers-yes"><input
															<?php echo 'yes' == $default_options['view_total_answers'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-answers-yes" type="radio"
														value="yes" name="shivs_poll_options[view_total_answers]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-view-total-answers-no"><input
															<?php echo 'no' == $default_options['view_total_answers'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-view-total-answers-no" type="radio" value="no"
														name="shivs_poll_options[view_total_answers]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-view-total-answers-div" style="<?php echo 'yes' != $default_options['view_total_answers'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'View Total Answers Label', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-view-total-answers-label" type="text"
														name="shivs_poll_options[view_total_answers_label]"
														value="<?php echo esc_html( stripslashes( $default_options['view_total_answers_label'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Message After Vote', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-message-after-vote" type="text"
														name="shivs_poll_options[message_after_vote]"
														value="<?php echo esc_html( stripslashes( $default_options['message_after_vote'] ) ); ?>"/>
												</td>
											</tr>
											<?php if ( 'no' == $default_options['has_auto_generate_poll_page'] ){ ?>
												<tr>
													<th>
														<?php _e( 'Auto Generate Poll Page ', 'shivs_poll' ); ?>:
													</th>
													<td><input
															<?php checked( 'yes', $default_options['auto_generate_poll_page'] ); ?>
															id="shivs-poll-auto-generate-poll-page-yes" type="radio"
															value="yes" name="shivs_poll_options[auto_generate_poll_page]"/><label
															for="shivs-poll-auto-generate-poll-page-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
														<input
															<?php checked( 'no', $default_options['auto_generate_poll_page'] ); ?>
															id="shivs-poll-auto-generate-poll-page-no" type="radio"
															value="no" name="shivs_poll_options[auto_generate_poll_page]"/><label
															for="shivs-poll-auto-generate-poll-page-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
													</td>
												</tr>
												<?php } ?>
											<tr>
												<th><label for="shivs-poll-page-url"><?php _e( 'Poll Page Url ', 'shivs_poll' ); ?>
														:</label>
												</th>
												<td><input id="shivs-poll-page-url" type="text"
														name="shivs_poll_options[poll_page_url]"
														value="<?php echo esc_html( stripslashes( $default_options['poll_page_url'] ) ); ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div">
								<div title="Click to toggle" class="handlediv"><br/></div>
								<h3 class="hndle">
									<span><?php _e( 'Other Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
											<tr>
												<th>
													<?php _e( 'Use CAPTCHA ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-use-captcha-no"><input
															id="shivs-poll-use-captcha-no"
															<?php echo 'no' == $default_options['use_captcha'] ? 'checked="checked"' : ''; ?>
															type="radio" name="shivs_poll_options[use_captcha]"
														value="no"/> <?php _e( 'No', 'shivs_poll' ); ?></label> <label
														for="shivs-poll-use-captcha-yes"><input
															id="shivs-poll-use-captcha-yes"
															<?php echo 'yes' == $default_options['use_captcha'] ? 'checked="checked"' : ''; ?>
															type="radio" name="shivs_poll_options[use_captcha]"
														value="yes"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label></td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Vote Permissions ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-vote-permisions-quest-only"><input
															id="shivs-poll-vote-permisions-quest-only"
															<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="quest-only"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Guest Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-registered-only"><input
															id="shivs-poll-vote-permisions-registered-only"
															<?php echo 'registered-only' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="registered-only"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Registered Users Only', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-guest-registered"><input
															id="shivs-poll-vote-permisions-guest-registered"
															<?php echo 'guest-registered' == $default_options['vote_permisions'] ? 'checked="checked"' : ''; ?>
															type="radio" value="guest-registered"
														name="shivs_poll_options[vote_permisions]"/> <?php _e( 'Guest &amp; Registered Users', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<?php if ( false ){ ?>
												<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
													<th>
														<?php _e( 'Vote as Facebook User', 'shivs_poll' ); ?>:
														<?php if ($pro_options['pro_user'] == 'no') { ?>
															<br/><small>(<?php _e( 'Available only for pro version of Shivs Poll', 'shivs_poll' ); ?>
																)</small>
															<?php } ?></label>
													</th>
													<td><label for="shivs-poll-vote-permisions-facebook-yes"><input
																<?php echo 'yes' == $default_options['vote_permisions_facebook'] ? 'checked="checked"' : ''; ?>
																id="shivs-poll-vote-permisions-facebook-yes" type="radio"
															value="yes" name="shivs_poll_options[vote_permisions_facebook]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
														<label for="shivs-poll-vote-permisions-facebook-no"><input
																<?php echo 'no' == $default_options['vote_permisions_facebook'] ? 'checked="checked"' : ''; ?>
																id="shivs-poll-vote-permisions-facebook-no" type="radio" value="no"
															name="shivs_poll_options[vote_permisions_facebook]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
													</td>
												</tr>
												<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-facebook-div" style="<?php echo 'yes' != $default_options['vote_permisions_facebook'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
													<th>
														<?php _e( '"Vote as Facebook User" Button Label', 'shivs_poll' ); ?>
														:
													</th>
													<td><input id="shivs-poll-vote-permisions-facebook-label" type="text"
															name="shivs_poll_options[vote_permisions_facebook_label]"
															value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_facebook_label'] ) ); ?>"/>
													</td>
												</tr>
												<?php } ?>

											<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Vote as Wordpress User', 'shivs_poll' ); ?>
													<br><small> (<?php _e( 'Will force users to login into your blog', 'shivs_poll' ); ?>
														)</small>:
												</th>
												<td><label for="shivs-poll-vote-permisions-wordpress-yes"><input
															<?php echo 'yes' == $default_options['vote_permisions_wordpress'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-wordpress-yes" type="radio"
														value="yes" name="shivs_poll_options[vote_permisions_wordpress]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-wordpress-no"><input
															<?php echo 'no' == $default_options['vote_permisions_wordpress'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-wordpress-no" type="radio" value="no"
														name="shivs_poll_options[vote_permisions_wordpress]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-wordpress-div" style="<?php echo 'yes' != $default_options['vote_permisions_wordpress'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( '"Vote as Wordpress User" Button Label', 'shivs_poll' ); ?>
													:
												</th>
												<td><input id="shivs-poll-vote-permisions-wordpress-label" type="text"
														name="shivs_poll_options[vote_permisions_wordpress_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_wordpress_label'] ) ); ?>"/>
												</td>
											</tr>

											<tr class="shivs-poll-vote-as-div" style="<?php echo 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Vote as Anonymous User', 'shivs_poll' ); ?>
													<br><small>(<?php _e( 'Logged users will be treated as anonymous', 'shivs_poll' ); ?>
														)</small>:
												</th>
												<td><label for="shivs-poll-vote-permisions-anonymous-yes"><input
															<?php echo 'yes' == $default_options['vote_permisions_anonymous'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-anonymous-yes" type="radio"
														value="yes" name="shivs_poll_options[vote_permisions_anonymous]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-vote-permisions-anonymous-no"><input
															<?php echo 'no' == $default_options['vote_permisions_anonymous'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-vote-permisions-anonymous-no" type="radio" value="no"
														name="shivs_poll_options[vote_permisions_anonymous]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-vote-as-div shivs_poll_suboption" id="shivs-poll-vote-permisions-anonymous-div" style="<?php echo 'yes' != $default_options['vote_permisions_anonymous'] ? 'display: none;' : 'quest-only' == $default_options['vote_permisions'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( '"Vote as Anonymous User" Button Label', 'shivs_poll' ); ?>
													:
												</th>
												<td><input id="shivs-poll-vote-permisions-anonymous-label" type="text"
														name="shivs_poll_options[vote_permisions_anonymous_label]"
														value="<?php echo esc_html( stripslashes( $default_options['vote_permisions_anonymous_label'] ) ); ?>"/>
												</td>
											</tr>

											<tr>
												<th>
													<?php _e( 'Blocking Voters ', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-blocking-voters-dont-block"><input
															class="shivs-poll-blocking-voters-hide-interval"
															<?php echo 'dont-block' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-dont-block" type="radio"
														value="dont-block" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'Dont`t Block', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-cookie"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'cookie' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-cookie" type="radio"
														value="cookie" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Cookie', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-ip"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'ip' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-ip" type="radio" value="ip"
														name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Ip', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-username"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'username' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-username" type="radio"
														value="username" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Username', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-blocking-voters-cookie-ip"><input
															class="shivs-poll-blocking-voters-show-interval"
															<?php echo 'cookie-ip' == $default_options['blocking_voters'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-blocking-voters-cookie-ip" type="radio"
														value="cookie-ip" name="shivs_poll_options[blocking_voters]"/> <?php _e( 'By Cookie &amp; Ip', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-blocking-voters-interval-div" style="<?php echo 'dont-block' == $default_options['blocking_voters'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Blocking voters interval', 'shivs_poll' ); ?>:
												</th>
												<td><input type="text"
														name="shivs_poll_options[blocking_voters_interval_value]"
														id="shivs-poll-blocking-voters-interval-value"
														value="<?php echo $default_options['blocking_voters_interval_value']; ?>"/>
													<select id="shivs-poll-blocking-voters-interval-unit"
														name="shivs_poll_options[blocking_voters_interval_unit]">
														<option
															<?php echo 'seconds' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="seconds"><?php _e( 'Seconds', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'minutes' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="minutes"><?php _e( 'Minutes', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'hours' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="hours"><?php _e( 'Hours', 'shivs_poll' ); ?></option>
														<option
															<?php echo 'days' == $default_options['blocking_voters_interval_unit'] ? 'selected="selected"' : ''; ?>
															value="days"><?php _e( 'Days', 'shivs_poll' ); ?></option>
													</select></td>
											</tr>
											<tr class="shivs-poll-limit-number-of-votes-per-user-div">
												<th>
													<?php _e( 'Limit Number of Votes per User', 'shivs_poll' ); ?>:
													<br><small>(<?php _e( 'Only for logged users', 'shivs_poll' ); ?>
														)</small>
												</th>
												<td><label for="shivs-poll-limit-number-of-votes-per-user-yes"><input
															<?php echo 'yes' == $default_options['limit_number_of_votes_per_user'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-limit-number-of-votes-per-user-yes" type="radio"
														value="yes" name="shivs_poll_options[limit_number_of_votes_per_user]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-limit-number-of-votes-per-user-no"><input
															<?php echo 'no' == $default_options['limit_number_of_votes_per_user'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-limit-number-of-votes-per-user-no" type="radio" value="no"
														name="shivs_poll_options[limit_number_of_votes_per_user]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-limit-number-of-votes-per-user-divs shivs_poll_suboption" id="shivs-poll-number-of-votes-per-user-div" style="<?php echo 'yes' != $default_options['limit_number_of_votes_per_user'] ? 'display: none;' : '' ?>">
												<th>
													<?php _e( 'Number of Votes per User', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-number-of-votes-per-user" type="text"
														name="shivs_poll_options[number_of_votes_per_user]"
														value="<?php echo esc_html( stripslashes( $default_options['number_of_votes_per_user'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th><?php _e( 'Poll Template ', 'shivs_poll' ); ?>:</th>
												<td>
													<?php
														$templates = Shivs_Poll_Model::get_shivs_poll_templates_search( 'id', 'asc' );
													?>
													<select class="shivs-poll-template" id="shivs-poll-template"
														name="shivs_poll_options[template]"
														onchange="shivs_poll_return_template_preview(jQuery(this).val(), '#shivs_poll_preview_page_template', 1);">
														<option value=""><?php _e( '--SELECT Template--', 'shivs_poll' ); ?></option>
														<?php
															if ( count( $templates ) > 0 ){
																foreach ( $templates as $template ) {
																?>
																<option
																	<?php if ( $default_options['template'] == $template['id'] )
																			echo 'selected="selected"' ?>
																	value="<?php echo $template['id']; ?>"><?php echo esc_html( stripslashes( $template['name'] ) ) ?></option>
																<?php
																}
															}
														?>
													</select>
													<div id="shivs_poll_preview_page_template" style="position: relative; float: right;">
														<?php
															if ( ( $default_options['template'] ) )
																print( Shivs_Poll_Model::return_template_preview_html( $default_options['template'], 1 ) );
														?>
													</div>
												</td>
											</tr>
											<tr class="shivs_poll_suboption">
												<th>
													<?php _e( 'Poll Template Width', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-template-width"
														type="text"
														name="shivs_poll_options[template_width]"
														style="width: 50%;"
														value="<?php echo $default_options['template_width']; ?>"/>
												</td>
											</tr>
											<tr>
												<th><?php _e( 'Widget Template ', 'shivs_poll' ); ?>:</th>
												<td>
													<?php
														$templates = Shivs_Poll_Model::get_shivs_poll_templates_search( 'id', 'asc' );
													?>
													<select class="shivs-poll-widget-template" id="shivs-poll-widget-template"
														name="shivs_poll_options[widget_template]"
														onchange="shivs_poll_return_template_preview(jQuery(this).val(), '#shivs_poll_preview_widget_template', 2);">
														<option value=""><?php _e( '--SELECT Template--', 'shivs_poll' ); ?></option>
														<?php
															if ( count( $templates ) > 0 ){
																foreach ( $templates as $template ) {
																?>
																<option
																	<?php if ( $default_options['widget_template'] == $template['id'] )
																			echo 'selected="selected"' ?>
																	value="<?php echo $template['id']; ?>"><?php echo esc_html( stripslashes( $template['name'] ) ) ?></option>
																<?php
																}
															}
														?>
													</select>
													<div id="shivs_poll_preview_widget_template" style="position: relative; float: right;">
														<?php
															if ( ( $default_options['widget_template'] ) )
																print( Shivs_Poll_Model::return_template_preview_html( $default_options['widget_template'], 2 ) );
														?>
													</div>
												</td>
											</tr>
											<tr class="shivs_poll_suboption">
												<th>
													<?php _e( 'Widget Template Width', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-widget-template-width"
														type="text"
														name="shivs_poll_options[widget_template_width]"
														style="width: 50%;"
														value="<?php echo $default_options['widget_template_width']; ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Percentages Decimals', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-percentages-decimals" type="text"
														name="shivs_poll_options[percentages_decimals]"
														value="<?php echo esc_html( stripslashes( $default_options['percentages_decimals'] ) ); ?>"/>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Use Default Loading Image', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-use-default-loading-image-yes"><input
															<?php echo 'yes' == $default_options['use_default_loading_image'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-use-default-loading-image-yes" type="radio"
															value="yes"
														name="shivs_poll_options[use_default_loading_image]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-use-default-loading-image-no"><input
															<?php echo 'no' == $default_options['use_default_loading_image'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-use-default-loading-image-no" type="radio"
															value="no"
														name="shivs_poll_options[use_default_loading_image]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-use-default-loading-image-div" style="<?php echo 'yes' == $default_options['use_default_loading_image'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Loading Image Url', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-loading-image-url" type="text"
														name="shivs_poll_options[loading_image_url]"
														value="<?php echo esc_html( stripslashes( $default_options['loading_image_url'] ) ); ?>"/>
												</td>
											</tr>

											<tr>
												<th>
													<?php _e( 'Redirect After Vote', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-redirect-after-vote-yes"><input
															<?php echo 'yes' == $default_options['redirect_after_vote'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-redirect-after-vote-yes" type="radio"
															value="yes"
														name="shivs_poll_options[redirect_after_vote]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-redirect-after-vote-no"><input
															<?php echo 'no' == $default_options['redirect_after_vote'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-redirect-after-vote-no" type="radio"
															value="no"
														name="shivs_poll_options[redirect_after_vote]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-redirect-after-vote-url-div" style="<?php echo 'no' == $default_options['redirect_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Redirect After Vote Url', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-redirect-after-vote-url" type="text"
														name="shivs_poll_options[redirect_after_vote_url]"
														value="<?php echo esc_html( stripslashes( $default_options['redirect_after_vote_url'] ) ); ?>"/>
												</td>
											</tr>

											<tr>
												<th>
													<?php _e( 'Reset Poll Stats Automatically', 'shivs_poll' ); ?>:
												</th>
												<td><label for="shivs-poll-schedule-reset-poll-stats-yes"><input
															<?php echo 'yes' == $default_options['schedule_reset_poll_stats'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-schedule-reset-poll-stats-yes" type="radio"
															value="yes"
														name="shivs_poll_options[schedule_reset_poll_stats]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<label for="shivs-poll-schedule-reset-poll-stats-no"><input
															<?php echo 'no' == $default_options['schedule_reset_poll_stats'] ? 'checked="checked"' : ''; ?>
															id="shivs-poll-schedule-reset-poll-stats-no" type="radio"
															value="no"
														name="shivs_poll_options[schedule_reset_poll_stats]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs-poll-schedule-reset-poll-stats-options-div shivs_poll_suboption" style="<?php echo 'no' == $default_options['schedule_reset_poll_stats'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Reset Stats Date', 'shivs_poll' ); ?>
													:<br><small>(<?php _e( 'Current Server Time', 'shivs_poll' );
															echo ': ' . current_time( 'mysql' ); ?>)</small>
												</th>
												<td><input id="shivs-poll-schedule-reset-poll-stats-date" type="text"
														name="shivs_poll_options[schedule_reset_poll_date]"
														value="<?php echo date( 'Y-m-d H:i:s', $default_options['schedule_reset_poll_date'] ); ?>"/>
												</td>
											</tr>
											<tr class="shivs-poll-schedule-reset-poll-stats-options-div shivs_poll_suboption" style="<?php echo 'no' == $default_options['schedule_reset_poll_stats'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Reset Stats Every', 'shivs_poll' ); ?>:
												</th>
												<td><input style="width:20%" id="shivs-poll-schedule-reset-poll-stats-recurring-value" type="text"
														name="shivs_poll_options[schedule_reset_poll_recurring_value]"
														value="<?php echo esc_html( stripslashes( $default_options['schedule_reset_poll_recurring_value'] ) ); ?>"/>
													<select name="shivs_poll_options[schedule_reset_poll_recurring_unit]">
														<option value="hour" <?php echo selected( 'hour', $default_options['schedule_reset_poll_recurring_unit'] ) ?>>HOURS</option>
														<option value="day" <?php echo selected( 'day', $default_options['schedule_reset_poll_recurring_unit'] ) ?>>DAYS</option>
													</select>
												</td>
											</tr>
											<tr>
												<th>
													<?php _e( 'Poll Date Format', 'shivs_poll' ); ?>
													: <br/><small><?php _e( 'Check', 'shivs_popll' ) ?>
														<a target="_blank" href="http://codex.wordpress.org/Formatting_Date_and_Time"> <?php _e( 'documentation', 'shivs_popll' ) ?></a></small>
												</th>
												<td><input id="shivs-poll-date-format" type="text"
														name="shivs_poll_options[date_format]"
														value="<?php echo esc_html( stripslashes( $default_options['date_format'] ) ); ?>"/>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Archive Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
										<tr>
											<th>
												<?php _e( 'View Poll Archive Link ', 'shivs_poll' ); ?>:
											</th>
											<td><input
													<?php checked( 'yes', $default_options['view_poll_archive_link'] ); ?>
													id="shivs-poll-view-poll-archive-link-yes" type="radio"
													value="yes" name="shivs_poll_options[view_poll_archive_link]"/><label
													for="shivs-poll-view-poll-archive-link-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<input
													<?php checked( 'no', $default_options['view_poll_archive_link'] ); ?>
													id="shivs-poll-view-poll-archive-link-no" type="radio"
													value="no" name="shivs_poll_options[view_poll_archive_link]"/><label
													for="shivs-poll-view-poll-archive-link-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-view-poll-archive-link-div" style="<?php echo 'yes' != $default_options['view_poll_archive_link'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'View Poll Archive Link Label', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-view-poll-archive-link-label"
													type="text"
													name="shivs_poll_options[view_poll_archive_link_label]"
													value="<?php echo esc_html( stripslashes( $default_options['view_poll_archive_link_label'] ) ); ?>"/>
											</td>
										</tr>
										<tr id="shivs-poll-view-poll-archive-link-div" style="<?php echo 'yes' != $default_options['view_poll_archive_link'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Poll Archive Url', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-poll-archive-url" type="text"
													name="shivs_poll_options[poll_archive_url]"
													value="<?php echo esc_html( stripslashes( $default_options['poll_archive_url'] ) ); ?>"/>
											</td>
										</tr>
										<tr>
											<th>
												<?php _e( 'Show Poll In Archive ', 'shivs_poll' ); ?>:
											</th>
											<td><label for="shivs-poll-show-in-archive-yes"><input
														<?php checked( 'yes', $default_options['show_in_archive'] ); ?>
														id="shivs-poll-show-in-archive-yes" type="radio" value="yes"
													name="shivs_poll_options[show_in_archive]"/> <?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<label for="shivs-poll-show-in-archive-no"><input
														<?php checked( 'no', $default_options['show_in_archive'] ); ?>
														id="shivs-poll-show-in-archive-no" type="radio" value="no"
													name="shivs_poll_options[show_in_archive]"/> <?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption" id="shivs-poll-show-in-archive-div" style="<?php echo 'yes' != $default_options['show_in_archive'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Archive Order', 'shivs_poll' ); ?>:
											</th>
											<td><input id="shivs-poll-show-in-archive-order" type="text"
													name="shivs_poll_options[archive_order]"
													value="<?php echo esc_html( stripslashes( $default_options['archive_order'] ) ); ?>"/>
											</td>
										</tr>

									</table>
								</div>
							</div>
							<div class="postbox" id="shivs-poll-advanced-options-div8">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Notifications Options', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<table cellspacing="0" class="links-table">
										<tbody>
										<tr>
											<th>
												<?php _e( 'Send Email Notifications', 'shivs_poll' ); ?>:
											</th>
											<td><input <?php checked( 'yes', $default_options['send_email_notifications'] ); ?>
													id="shivs-poll-send-email-notifications-yes" type="radio"
													value="yes" name="shivs_poll_options[send_email_notifications]"/><label
													for="shivs-poll-send-email-notifications-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
												<input <?php checked( 'no', $default_options['send_email_notifications'] ); ?>
													id="shivs-poll-send-email-notifications-no" type="radio"
													value="no" name="shivs_poll_options[send_email_notifications]"/><label
													for="shivs-poll-send-email-notifications-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-from-name-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications From Name', 'shivs_poll' ); ?>:
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-from-name"
													type="text"
													name="shivs_poll_options[email_notifications_from_name]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_from_name'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-from-email-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications From Email', 'shivs_poll' ); ?>:
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-from-email"
													type="text"
													name="shivs_poll_options[email_notifications_from_email]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_from_email'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-recipients-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Recipients', 'shivs_poll' ); ?>
												:<br><small><?php _e( 'Use comma separated email addresses: email@xmail.com,email2@xmail.com', 'shivs_poll' ) ?></small>
											</th>
											<td valign="top">
												<input id="shivs-poll-email-notifications-recipients"
													type="text"
													name="shivs_poll_options[email_notifications_recipients]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_recipients'] ) ); ?>"/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-subject-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Subject', 'shivs_poll' ); ?>:
											</th>
											<td>
												<input id="shivs-poll-email-notifications-subject"
													type="text"
													name="shivs_poll_options[email_notifications_subject]"
													value="<?php echo esc_html( stripslashes( $default_options['email_notifications_subject'] ) ); ?>"
													/>
											</td>
										</tr>
										<tr class="shivs_poll_suboption shivs-poll-email-notifications-div" id="shivs-poll-email-notifications-body-div" style="<?php echo 'yes' != $default_options['send_email_notifications'] ? 'display: none;' : ''; ?>">
											<th>
												<?php _e( 'Notifications Body', 'shivs_poll' ); ?>:
											</th>
											<td>
												<textarea id="shivs-poll-email-notifications-body" rows="10"
													name="shivs_poll_options[email_notifications_body]"><?php echo esc_html( stripslashes( $default_options['email_notifications_body'] ) ); ?></textarea>
											</td>
										</tr>
									</table>
								</div>
							</div>
							<?php if ( false ){ ?>
								<div class="postbox" id="shivs-poll-advanced-options-div9">
									<div title="Click to toggle" class="handlediv">
										<br/>
									</div>
									<h3 class="hndle">
										<span><?php _e( 'Facebook Share Options', 'shivs_poll' ); ?>
											<?php if ( $pro_options['pro_user'] == 'no' ){ ?>
												<small>(<?php _e( 'Available only for pro version of Shivs Poll', 'shivs_poll' ); ?> )</small>
												<?php } ?>
										</span>
									</h3>
									<div class="inside">
										<table cellspacing="0" class="links-table">
											<tbody>
											<tr>
												<th>
													<?php _e( 'Share After Vote ', 'shivs_poll' ); ?>:
												</th>
												<td><input
														<?php checked( 'yes', $default_options['share_after_vote'] ); ?>
														id="shivs-poll-share-after-vote-yes" type="radio"
														value="yes" name="shivs_poll_options[share_after_vote]"/><label
														for="shivs-poll-share-after-vote-yes"><?php _e( 'Yes', 'shivs_poll' ); ?></label>
													<input
														<?php checked( 'no', $default_options['share_after_vote'] ); ?>
														id="shivs-poll-share-after-vote-no" type="radio"
														value="no" name="shivs_poll_options[share_after_vote]"/><label
														for="shivs-poll-share-after-vote-no"><?php _e( 'No', 'shivs_poll' ); ?></label>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-name-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Name', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-name"
														type="text"
														name="shivs_poll_options[share_name]"
														value="<?php echo esc_html( stripslashes( $default_options['share_name'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-caption-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Caption', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-caption"
														type="text"
														name="shivs_poll_options[share_caption]"
														value="<?php echo esc_html( stripslashes( $default_options['share_caption'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-description-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Description', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-description"
														type="text"
														name="shivs_poll_options[share_description]"
														value="<?php echo esc_html( stripslashes( $default_options['share_description'] ) ); ?>"/>
												</td>
											</tr>
											<tr class="shivs_poll_suboption" id="shivs-poll-share-after-vote-picture-tr" style="<?php echo 'yes' != $default_options['share_after_vote'] ? 'display: none;' : ''; ?>">
												<th>
													<?php _e( 'Share Picture', 'shivs_poll' ); ?>:
												</th>
												<td><input id="shivs-poll-share-picture"
														type="text"
														name="shivs_poll_options[share_picture]"
														value="<?php echo esc_html( stripslashes( $default_options['share_picture'] ) ); ?>"/>
												</td>
											</tr>

										</table>
									</div>
								</div>
								<?php } ?>
						</div>
						<input type="hidden" value="<?php echo $poll_id ?>"
							name="shivs_poll_id" id="shivs-poll-edit-add-new-form-poll-id"/> <input
							type="hidden" value="<?php echo $action_type ?>"
							name="action_type" id="shivs-poll-edit-add-new-form-action-type"/>
						<input type="button" accesskey="p" class="button-primary"
							value="<?php _e( 'Save Poll', 'shivs_poll' ); ?>"
							id="shivs-poll-edit-add-new-form-submit"/>
					</div>
					<div class="postbox-container" id="postbox-container-1">
						<div class="meta-box-sortables ui-sortable" id="side-sortables">
							<div class="postbox " id="linksubmitdiv">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Save', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<div id="submitlink" class="submitbox">
										<div id="major-publishing-actions">
											<div id="publishing-action">
												<input type="button" accesskey="p" class="button-primary"
													value="<?php _e( 'Save Poll', 'shivs_poll' ); ?>"
													id="shivs-poll-edit-add-new-form-submit1"/>
											</div>
											<div class="clear"></div>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
							<?php if ( 'edit' == $action ) : ?>
								<div id="submitdiv" class="postbox ">
									<div title="Click to toggle" class="handlediv">
										<br/>
									</div>
									<h3 class="hndle">
										<span><?php _e( 'Tools ', 'shivs_poll' ); ?></span>
									</h3>
									<div class="inside">
										<div id="submitpost" class="submitbox">
											<div id="minor-publishing">
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<label for="post_status"><?php _e( 'Poll Author', 'shivs_poll' ) ?>
															:</label>
														<span id="shivs-poll-change-poll-author-container-<?php echo $current_poll['id'] ?>"><b><?php $poll_author = get_user_by( 'id', $current_poll ['poll_author'] );
																echo $poll_author->user_nicename; ?></b></span>
														<a class="edit-post-status hide-if-no-js" href="javascript:void(0)" onclick="shivs_poll_show_change_poll_author( '<?php echo $current_poll ['id'] ?>', 'answers'); return false;" style="display: inline;">Edit</a>
													</div>
												</div>
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<label for="post_status"><?php _e( 'Total Votes', 'shivs_poll' ) ?>
															:</label>
														<span id="shivs-poll-change-no-votes-poll-container-<?php echo $current_poll['id'] ?>"><b><?php echo $current_poll['total_votes'] ?></b></span>
														<a class="edit-post-status hide-if-no-js" href="javascript:void(0)" onclick="shivs_poll_show_change_total_number_poll( '<?php echo $current_poll ['id'] ?>', 'votes'); return false;" style="display: inline;">Edit</a>
													</div>
												</div>
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<label for="post_status"><?php _e( 'Total Answers', 'shivs_poll' ) ?>
															:</label>
														<span id="shivs-poll-change-no-answers-poll-container-<?php echo $current_poll['id'] ?>"><b><?php echo $current_poll['total_answers'] ?></b></span>
														<a class="edit-post-status hide-if-no-js" href="javascript:void(0)" onclick="shivs_poll_show_change_total_number_poll( '<?php echo $current_poll ['id'] ?>', 'answers'); return false;" style="display: inline;">Edit</a>
													</div>
												</div>
											</div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
								<?php endif; ?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</form>
	</div>
	<?php
	}

	public function view_add_edit_poll_template() {
		global $action, $current_user;
		$page_name   = __( 'Add New Poll Template', 'shivs_poll' );
		$action_type = 'add-new';
		$template_id = '';
		if ( 'edit' == $action ){
			$template_id     = ( isset ( $_GET ['id'] ) ? intval( $_GET ['id'] ) : 0 );
			$template_author = Shivs_Poll_Model::get_poll_template_field_from_database_by_id( 'template_author', $template_id );
			if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) || $template_author != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) )
				wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
			$page_name   = __( 'Edit Poll Template', 'shivs_poll' );
			$action_type = 'edit';
		}
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$current_template = Shivs_Poll_Model::get_poll_template_from_database_by_id( $template_id );
	?>
	<div class="wrap">
		<div class="icon32 icon32-shivs-poll">
			<br>

		</div>
		<h2><?php print $page_name; ?><?php if ('edit' == $action): ?><a
					class="add-new-h2"
				href="<?php echo esc_url( add_query_arg( array( 'page' => 'shivs-polls-templates', 'action' => 'add-new', 'id' => false ) ) ); ?>"><?php _e( 'Add New', 'shivs_poll' ); ?></a><?php endif; ?></h2>
		<div id="message"></div>
		<form method="post" name="shivs_poll_edit_add_new_template_form"
			id="shivs-poll-edit-add-new-template-form">
			<?php wp_nonce_field( 'shivs-poll-edit-add-new-template' ); ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="stuffbox" id="shivs-poll-template-namediv">
							<h3>
								<label for="shivs-poll-template-name"><?php _e( 'Template Name', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<input type="text" id="shivs-poll-template-name"
									value="<?php echo esc_html( stripslashes( $current_template['name'] ) ); ?>"
									tabindex="1" name="shivs_poll_template_name" size="30"/>
								<p><?php _e( 'Example: Test Poll Template', 'shivs_poll' ); ?></p>
							</div>
						</div>
						<div class="stuffbox" id="shivs-poll-before-vote-template-div">
							<h3>
								<label for="shivs-poll-before_vote-template-input"><?php _e( 'Template Before Vote', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['before_vote_template'] ), 'shivs-poll-before-vote-template-input', array( 'textarea_name' => 'shivs_poll_before_vote_template', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>
						<div class="stuffbox" id="shivs-poll-after-vote-template-div">
							<h3>
								<label for="shivs-poll-after-vote-template-input"><?php _e( 'Template After Vote', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['after_vote_template'] ), 'shivs-poll-after-vote-template-input', array( 'textarea_name' => 'shivs_poll_after_vote_template', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>
						<div class="postbox" id="shivs-poll-template-before-start-date-div">
							<div title="Click to toggle" class="handlediv"
								id="shivs-poll-template-before-start-date-handler">
								<br>

							</div>
							<h3>
								<label for="shivs-poll-template-before-start-date-input"><?php _e( 'Template Before Start Date', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['before_start_date_template'] ), 'shivs-poll-template-before-start-date-input', array( 'textarea_name' => 'shivs_poll_template_before_start_date', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>
						<div class="postbox" id="shivs-poll-template-after-end-date-div">
							<div title="Click to toggle" class="handlediv"
								id="shivs-poll-template-after-end-date-handler">
								<br>

							</div>
							<h3>
								<label for="shivs-poll-template-after-end-date-input"><?php _e( 'Template After End Date', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['after_end_date_template'] ), 'shivs-poll-template-after-end-date-input', array( 'textarea_name' => 'shivs_poll_template_after_end_date', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>
						<div class="postbox" id="shivs-poll-template-css-div">
							<div title="Click to toggle" class="handlediv"
								id="shivs-poll-template-css-handler">
								<br>

							</div>
							<h3>
								<label for="shivs-poll-template-css-input"><?php _e( 'Css', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['css'] ), 'shivs-poll-template-css-input', array( 'textarea_name' => 'shivs_poll_template_css', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>
						<div class="postbox" id="shivs-poll-template-js-div">
							<div title="Click to toggle" class="handlediv"
								id="shivs-poll-template-js-handler">
								<br>

							</div>
							<h3>
								<label for="shivs-poll-template-js-input"><?php _e( 'JavaScript', 'shivs_poll' ); ?></label>
							</h3>
							<div class="inside">
								<?php wp_editor( stripslashes( $current_template['js'] ), 'shivs-poll-template-js-input', array( 'textarea_name' => 'shivs_poll_template_js', 'teeny' => true, 'media_buttons' => false ) ); ?>
							</div>
						</div>

						<input type="hidden" value="<?php echo $current_template['id']; ?>"
							name="template_id"
							id="shivs-poll-edit-add-new-template-form-template-id"/> <input
							type="hidden" value="<?php echo $action_type ?>"
							name="action_type"
							id="shivs-poll-edit-add-new-template-form-action-type"/> <input
							type="button" class="button-primary"
							value="<?php _e( 'Save Poll Template', 'shivs_poll' ) ?>"
							id="shivs-poll-edit-add-new-template-form-save"/>
					</div>
					<div class="postbox-container" id="postbox-container-1">
						<div class="meta-box-sortables ui-sortable" id="side-sortables">
							<div class="postbox " id="linksubmitdiv">
								<div title="Click to toggle" class="handlediv">
									<br/>
								</div>
								<h3 class="hndle">
									<span><?php _e( 'Save', 'shivs_poll' ); ?></span>
								</h3>
								<div class="inside">
									<div id="submitlink" class="submitbox">
										<div id="major-publishing-actions">
											<div id="publishing-action">
												<input type="button" accesskey="p" class="button-primary"
													value="<?php _e( 'Save Poll Template', 'shivs_poll' ); ?>"
													id="shivs-poll-edit-add-new-template-form-save1"/>
											</div>
											<div class="clear"></div>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
							<?php
								if ( 'edit' == $action ){
								?>
								<div id="submitdiv" class="postbox ">
									<div title="Click to toggle" class="handlediv">
										<br/>
									</div>
									<h3 class="hndle">
										<span><?php _e( 'Tools ', 'shivs_poll' ); ?></span>
									</h3>
									<div class="inside">
										<div id="submitpost" class="submitbox">
											<div id="minor-publishing">
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<label for="post_status"><?php _e( 'Template Author', 'shivs_poll' ) ?>
															:</label>
														<span id="shivs-poll-change-template-author-container-<?php echo $current_template['id'] ?>"><b><?php $template_author = get_user_by( 'id', $current_template['template_author'] );
																echo $template_author->user_nicename; ?></b></span>
														<a class="edit-post-status hide-if-no-js" href="javascript:void(0)" onclick="shivs_poll_show_change_template_author( '<?php echo $current_template ['id'] ?>', 'answers'); return false;" style="display: inline;">Edit</a>
													</div>
												</div>
											</div>
											<div class="clear"></div>
										</div>
										<div id="submitpost" class="submitbox">
											<div id="minor-publishing">
												<div id="misc-publishing-actions">
													<div class="misc-pub-section">
														<p><?php _e( 'Select Template For Reset', 'shivs_poll' ) ?>
															:</p>
														<select name="shivs_poll_reset_template_id"
															id="shivs-poll-reset-template-id">
															<option <?php selected( 'White', $current_template['name'] ); ?> value="1"><?php _e( 'White', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Grey', $current_template['name'] ); ?> value="2"><?php _e( 'Grey', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Dark', $current_template['name'] ); ?> value="3"><?php _e( 'Dark', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Blue v1', $current_template['name'] ); ?> value="4"><?php _e( 'Blue v1', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Blue v2', $current_template['name'] ); ?> value="5"><?php _e( 'Blue v2', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Blue v3', $current_template['name'] ); ?> value="6"><?php _e( 'Blue v3', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Red v1', $current_template['name'] ); ?> value="7"><?php _e( 'Red v1', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Red v2', $current_template['name'] ); ?> value="8"><?php _e( 'Red v2', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Red v3', $current_template['name'] ); ?> value="9"><?php _e( 'Red v3', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Green v1', $current_template['name'] ); ?> value="10"><?php _e( 'Green v1', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Green v2', $current_template['name'] ); ?> value="11"><?php _e( 'Green v2', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Green v3', $current_template['name'] ); ?> value="12"><?php _e( 'Green v3', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Orange v1', $current_template['name'] ); ?> value="13"><?php _e( 'Orange v1', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Orange v2', $current_template['name'] ); ?> value="14"><?php _e( 'Orange v2', 'shivs_poll' ) ?></option>
															<option <?php selected( 'Orange v3', $current_template['name'] ); ?> value="15"><?php _e( 'Orange v3', 'shivs_poll' ) ?></option>
														</select>
													</div>
												</div>
											</div>
											<div class="clear"></div>
										</div>
										<div id="submitlink" class="submitbox">
											<div id="major-publishing-actions">
												<div id="publishing-action">
													<input type="button" accesskey="r" class="button-primary"
														value="<?php _e( 'Reset Poll Template', 'shivs_poll' ); ?>"
														id="shivs-poll-edit-add-new-template-form-reset" onclick="if (confirm('<?php _e( 'Are You Sure You Want To Reset This Template?' ); ?>')) { shivs_poll_reset_template() }"/>
												</div>
												<div class="clear"></div>
											</div>
											<div class="clear"></div>
										</div>
									</div>
								</div>
								<?php
								}
							?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</form>
	</div>
	<?php
	}
	/**
	* End Views section
	*/

	/**
	* Start Ajax section
	*/
	function ajax_edit_add_new_poll() {
		if ( is_admin() ){
			global $wpdb, $current_user;
			check_ajax_referer( 'shivs-poll-edit-add-new' );

			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$shivs_poll_model = new Shivs_Poll_Model ();
			if ( 'add-new' == $_REQUEST ['action_type'] ){
				if ( ( !$this->current_user_can( 'edit_own_polls' ) ) && ( !$this->current_user_can( 'edit_polls' ) ) )
					wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
				else {
					$shivs_poll_id = $shivs_poll_model->add_poll_to_database( $_REQUEST, $this->_config );
					if ( $shivs_poll_id ){
						_e( 'Poll successfully added!', 'shivs_poll' );
					}
					else {
						echo $shivs_poll_model->error;
					}
				}
			}
			if ( 'edit' == $_REQUEST ['action_type'] ){
				if ( ctype_digit( $_REQUEST ['shivs_poll_id'] ) ){
					$poll_details = Shivs_Poll_Model::get_poll_from_database_by_id( $_REQUEST ['shivs_poll_id'] );
					if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) )
						wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
					else {
						$shivs_poll_id = $shivs_poll_model->edit_poll_in_database( $_REQUEST, $this->_config );
						if ( $shivs_poll_id )
							_e( 'Poll successfully Edited!', 'shivs_poll' );
						else
							echo $shivs_poll_model->error;
					}
				}
				else
					_e( 'We\'re unable to update your poll!', 'shivs_poll' );
			}
			unset ( $shivs_poll_model );
		}
		die ();
	}

	function ajax_edit_add_new_poll_template() {
		if ( is_admin() ){
			global $wpdb, $current_user;
			check_ajax_referer( 'shivs-poll-edit-add-new-template' );
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$shivs_poll_model = new Shivs_Poll_Model ();
			if ( 'add-new' == $_REQUEST ['action_type'] ){
				if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) )
					wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
				else {
					$shivs_poll_template_id = $shivs_poll_model->add_poll_template_to_database( $_REQUEST, $this->_config );
					if ( $shivs_poll_template_id ){
						_e( 'Poll template successfully added!', 'shivs_poll' );
					}
					else {
						echo $shivs_poll_model->error;
					}
				}
			}
			if ( 'edit' == $_REQUEST ['action_type'] ){
				if ( ctype_digit( $_REQUEST ['template_id'] ) ){
					$template_details = Shivs_Poll_Model::get_poll_template_from_database_by_id( $_REQUEST ['template_id'] );
					if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) || $template_details['template_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) )
						wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
					else {
						$shivs_poll_template_id = $shivs_poll_model->edit_poll_template_in_database( $_REQUEST, $this->_config );
						if ( $shivs_poll_template_id ){
							_e( 'Poll Template successfully Edited!', 'shivs_poll' );
						}
						else {
							echo $shivs_poll_model->error;
						}
					}
				}
				else
					_e( 'We\'re unable to update your poll template!', 'shivs_poll' );
			}
			unset ( $shivs_poll_model );
		}
		die ();
	}

	function ajax_reset_poll_template() {
		if ( is_admin() ){
			global $wpdb, $current_user;
			check_ajax_referer( 'shivs-poll-edit-add-new-template' );
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$shivs_poll_model = new Shivs_Poll_Model ();
			if ( 'edit' == $_REQUEST ['action_type'] ){
				if ( ctype_digit( $_REQUEST ['template_id'] ) ){
					$template_details = Shivs_Poll_Model::get_poll_template_from_database_by_id( $_REQUEST ['template_id'] );
					if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) || $template_details['template_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) )
						wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
					else {
						$shivs_poll_template_id = $shivs_poll_model->reset_poll_template( $_REQUEST, $this->_config );
						if ( $shivs_poll_template_id ){
							_e( 'Poll Template Successfully Reseted!', 'shivs_poll' );
						}
						else {
							echo $shivs_poll_model->error;
						}
					}
				}
				else
					_e( 'We\'re unable to reset your poll template!', 'shivs_poll' );
			}
			unset ( $shivs_poll_model );
		}
		die ();
	}

	public function shivs_poll_do_vote() {
		$error   = '';
		$success = '';
		$message = '';
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? $_REQUEST ['poll_id'] : NULL;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : NULL;
			$location  = isset ( $_REQUEST ['location'] ) ? $_REQUEST ['location'] : NULL;
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$poll_html = $shivs_poll_model->register_vote( $_REQUEST );
				if ( $poll_html ){
					$message = $poll_html;
					$success = $shivs_poll_model->success;
				}
				else {
					$error = $shivs_poll_model->error;
				}
				unset ( $shivs_poll_model );
			}
			else {
				$error = __( 'Invalid Request! Try later!', 'shivs_poll' );
			}
		}
		print '[ajax-response]' . json_encode( array( 'error' => $error, 'success' => $success, 'message' => $message ) ) . '[/ajax-response]';
		die ();
	}

	public function shivs_poll_view_results() {
		$error   = '';
		$success = '';
		$message = '';
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? $_REQUEST ['poll_id'] : 0;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : '';
			$location  = isset ( $_REQUEST ['location'] ) ? $_REQUEST ['location'] : 'page';
			$tr_id     = isset ( $_REQUEST ['tr_id'] ) ? $_REQUEST ['tr_id'] : '';
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$shivs_poll_model->vote = true;
				$poll_html            = do_shortcode( $shivs_poll_model->return_poll_html( array( 'tr_id' => $tr_id, 'location' => $location ) ) );
				if ( $poll_html ){
					$message = $poll_html;
					$success = $shivs_poll_model->success;
				}
				else {
					$error = $shivs_poll_model->error;
				}
				unset ( $shivs_poll_model );
			}
			else {
				$error = __( 'Invalid Request! Try later!', 'shivs_poll' );
			}
		}
		print '[ajax-response]' . json_encode( array( 'error' => $error, 'success' => $success, 'message' => $message ) ) . '[/ajax-response]';
		die ();
	}

	public function shivs_poll_back_to_vote() {
		$error   = '';
		$success = '';
		$message = '';
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? $_REQUEST ['poll_id'] : 0;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : '';
			$location  = isset ( $_REQUEST ['location'] ) ? $_REQUEST ['location'] : 'page';
			$tr_id     = isset ( $_REQUEST ['tr_id'] ) ? $_REQUEST ['tr_id'] : '';
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$poll_html = do_shortcode( $shivs_poll_model->return_poll_html( array( 'tr_id' => $tr_id, 'location' => $location ) ) );
				if ( $poll_html ){
					$message = $poll_html;
					$success = $shivs_poll_model->success;
				}
				else {
					$error = $shivs_poll_model->error;
				}
				unset ( $shivs_poll_model );
			}
			else {
				$error = __( 'Invalid Request! Try later!', 'shivs_poll' );
			}
		}
		print '[ajax-response]' . json_encode( array( 'error' => $error, 'success' => $success, 'message' => $message ) ) . '[/ajax-response]';
		die ();
	}

	public function shivs_poll_load_css() {
		header( 'Content-Type: text/css' );
		// check_ajax_referer('shivs-poll-public-css');
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : NULL;
			$location  = isset ( $_REQUEST ['location'] ) ? $_REQUEST ['location'] : NULL;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : NULL;
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$poll_css = $shivs_poll_model->return_poll_css( array( 'location' => $location ) );
				print $poll_css;
				unset ( $shivs_poll_model );
			}
		}
		die ();
	}

	public function shivs_poll_load_js() {
		header( 'Content-Type: text/javascript' );
		// check_ajax_referer('shivs-poll-public-js');
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['id'] ) ? $_REQUEST ['id'] : NULL;
			$location  = isset ( $_REQUEST ['location'] ) ? $_REQUEST ['location'] : NULL;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : NULL;
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$poll_js = $shivs_poll_model->return_poll_js( array( 'location' => $location ) );
				print $poll_js;
				unset ( $shivs_poll_model );
			}
		}
		die ();
	}

	public function ajax_get_polls_for_editor() {
		check_ajax_referer( 'shivs-poll-editor' );
		if ( is_admin() ){
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$shivs_polls = Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
		?>
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<title><?php _e( 'Insert Poll', 'shivs_poll' ); ?></title>
				<script type="text/javascript"
					src="<?php echo get_option( 'siteurl' ) ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
				<script type="text/javascript">
					function insertShivsPollTinyMce( poll_id, tr_id ) {
						tr_id = typeof tr_id !== 'undefined' ? tr_id : '';
						if ( isNaN( poll_id ) ) {
							alert( '<?php _e( 'Error: Invalid Shivs Poll!\n\nPlease choose the poll again:\n\n', 'shivs_poll' ) ?>' );
						}
						else {
							if ( poll_id != null && poll_id != '' ) {
								if ( tr_id != '' ) {
									tinyMCEPopup.editor.execCommand( 'mceInsertContent', false, '[shivs_poll id="' + poll_id + '" tr_id="' + tr_id + '"]' );
								}
								else {
									tinyMCEPopup.editor.execCommand( 'mceInsertContent', false, '[shivs_poll id="' + poll_id + '"]' );
								}
							}
							else {
								tinyMCEPopup.editor.execCommand( 'mceInsertContent', false, '[shivs_poll]' );
							}
							tinyMCEPopup.close();
						}
					}
				</script>
			</head>
			<body>
				<p>
					<label for="shivs-poll-id-dialog"> <span><?php _e( 'Poll to Display', 'shivs_poll' ); ?>:</span>
						<select class="widefat" name="shivs_poll_id" id="shivs-poll-id-dialog">
							<option value="-3"><?php _e( 'Display Random Poll', 'shivs_poll' ); ?></option>
							<option value="-2"><?php _e( 'Display Latest Poll', 'shivs_poll' ); ?></option>
							<option value="-1"><?php _e( 'Display Current Active Poll', 'shivs_poll' ); ?></option>
							<?php
								if ( count( $shivs_polls ) > 0 ){
									foreach ( $shivs_polls as $shivs_poll ) {
									?>
									<option value="<?php echo $shivs_poll['id']; ?>"><?php echo esc_html( stripslashes( $shivs_poll['name'] ) ); ?></option>
									<?php
									}
								}
							?>
						</select>
					</label>
					<br/>
					<label for="shivs-poll-tr-id-dialog"> <span><?php _e( 'Tracking ID', 'shivs_poll' ); ?>:</span><br>
						<input class="widefat" name="shivs_poll_tr_id" id="shivs-poll-tr-id-dialog"/>
					</label>


					<center> <input type="button" class="button-primary"
							value="<?php _e( 'Insert Poll', 'shivs_poll' ); ?>"
							onclick=" insertShivsPollTinyMce( document.getElementById('shivs-poll-id-dialog').value, document.getElementById('shivs-poll-tr-id-dialog').value );"/></center>
					<br/>
					<center> <input type="button" class="button-primary"
							value="<?php _e( 'Close', 'shivs_poll' ); ?>"
							onclick="tinyMCEPopup.close();"/></center>
				</p>
			</body>
		</html>
		<?php
		}
		die ();
	}

	public function ajax_preview_template() {
		if ( true /*check_ajax_referer( 'shivs-poll-edit-add-new' )*/ ){
			if ( is_admin() ){
				$template_id = trim( $_POST['template_id'] );
				$loc         = trim( $_POST['loc'] );
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model();
				$template       = $shivs_poll_model->return_template_preview_html( $template_id, $loc );
				print $template;
				unset( $shivs_poll_model );
			}
		}
		die();
	}

	public function ajax_get_polls_for_html_editor() {
		check_ajax_referer( 'shivs-poll-html-editor' );
		if ( is_admin() ){
			require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
			$shivs_polls = Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
		?>
		<p style="text-align: center;">
			<label for="shivs-poll-id-html-dialog"> <span><?php _e( 'Poll to Display', 'shivs_poll' ); ?>:</span>
				<select class="widefat" name="shivs_poll_id"
					id="shivs-poll-id-html-dialog">
					<option value="-3"><?php _e( 'Display Random Poll', 'shivs_poll' ); ?></option>
					<option value="-2"><?php _e( 'Display Latest Poll', 'shivs_poll' ); ?></option>
					<option value="-1"><?php _e( 'Display Current Active Poll', 'shivs_poll' ); ?></option>
					<?php
						if ( count( $shivs_polls ) > 0 ){
							foreach ( $shivs_polls as $shivs_poll ) {
							?>
							<option value="<?php echo $shivs_poll['id']; ?>"><?php echo esc_html( stripslashes( $shivs_poll['name'] ) ); ?></option>
							<?php
							}
						}
					?>
				</select>
			</label>
			<br/><br/>
			<label for="shivs-poll-tr-id-html-dialog"> <span><?php _e( 'Tracking ID', 'shivs_poll' ); ?>:</span>
				<input type="text" name="shivs_poll_tr_id" id="shivs-poll-tr-id-html-dialog" class="widefat" value=""/>
			</label>

			<br/> <br/> <input type="button" class=""
				value="<?php _e( 'Insert Poll', 'shivs_poll' ); ?>"
				onclick=" insertShivsPoll( edCanvas, document.getElementById('shivs-poll-id-html-dialog').value, document.getElementById('shivs-poll-tr-id-html-dialog').value );"/>
			<br/> <br/> <input type="button" class=""
				value="<?php _e( 'Close', 'shivs_poll' ); ?>" onclick="tb_remove();"/>
		</p>

		<?php
		}
		die ();
	}

	public function ajax_show_captcha() {
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? $_REQUEST ['poll_id'] : NULL;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : NULL;
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$shivs_poll_model->set_unique_id( $unique_id );
				$poll_options = $shivs_poll_model->poll_options;
				if ( 'yes' == $poll_options ['use_captcha'] ){
					require_once( $this->_config->plugin_inc_dir . '/securimage.php' );
					$img               = new Shivs_Poll_Securimage ();
					$img->ttf_file     = $this->_config->plugin_path . 'captcha/AHGBold.ttf';
					$img->namespace    = 'shivs_poll_' . $poll_id . $unique_id;
					$img->image_height = 60;
					$img->image_width  = intval( $img->image_height * M_E );
					$img->text_color   = new Shivs_Poll_Securimage_Color ( rand( 0, 255 ), rand( 0, 255 ), rand( 0, 255 ) );
					$img->show();
				}
			}
			else
				wp_die( 'Invalid Poll' );
		}
		else
			wp_die( 'captcha error' );
		die ();
	}

	public function ajax_play_captcha() {
		if ( is_admin() ){
			$poll_id   = isset ( $_REQUEST ['poll_id'] ) ? $_REQUEST ['poll_id'] : NULL;
			$unique_id = isset ( $_REQUEST ['unique_id'] ) ? $_REQUEST ['unique_id'] : NULL;
			if ( $poll_id ){
				require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
				$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
				$poll_options   = $shivs_poll_model->poll_options;
				if ( 'yes' == $poll_options ['use_captcha'] ){
					require_once( $this->_config->plugin_inc_dir . '/securimage.php' );
					$img                   = new Shivs_Poll_Securimage ();
					$img->audio_path       = $this->_config->plugin_path . 'captcha/audio/';
					$img->audio_noise_path = $this->_config->plugin_path . 'captcha/audio/noise/';
					$img->namespace        = 'shivs_poll_' . $poll_id . $unique_id;

					$img->outputAudioFile();
				}
			}
			else
				wp_die( 'Invalid Poll' );
		}
		else
			wp_die( 'captcha error' );
		die ();
	}

	public function ajax_show_opt_box_modal() {
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$opt_box_modal_options                    = get_option( 'shivs_poll_opt_box_modal_options' );
		$opt_box_modal_options ['show']           = 'no'; //restore to no
		$opt_box_modal_options ['last_show_date'] = Shivs_Poll_Model::get_mysql_curent_date();
		update_option( 'shivs_poll_opt_box_modal_options', $opt_box_modal_options );
	?>
	<?php
		$this->shivs_poll_opt_form1();
	?>
	<?php
		die ();
	}

	public function ajax_modal_option_signup() {
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$opt_box_modal_options                      = get_option( 'shivs_poll_opt_box_modal_options' );
		$opt_box_modal_options ['modal_had_submit'] = 'yes';
		update_option( 'shivs_poll_opt_box_modal_options', $opt_box_modal_options );
		die ();
	}

	public function ajax_sidebar_option_signup() {
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$opt_box_modal_options                        = get_option( 'shivs_poll_opt_box_modal_options' );
		$opt_box_modal_options ['sidebar_had_submit'] = 'yes';
		update_option( 'shivs_poll_opt_box_modal_options', $opt_box_modal_options );
		die ();
	}

	public function ajax_is_wordpress_user() {
		global $current_user;
		if ( $current_user->ID > 0 )
			print '[response]true[/response]';
		else
			print '[response]false[/response]';
		die();
	}

	public static function base64_decode( $str ) {
		$str = str_replace( '-', '/', $str );
		$str = str_replace( '_', '+', $str );
		return base64_decode( $str );
	}

	public function ajax_set_wordpress_vote() {

		$poll_id   = self::base64_decode( $_GET['poll_id'] );
		$unique_id = self::base64_decode( $_GET['unique_id'] );
		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );

		$answers          = Shivs_Poll_Model::get_poll_answers( $poll_id, array( 'default', 'other' ) );
		$shivs_poll_answers = array();
		if ( count( $answers ) > 0 ){
			foreach ( $answers as $answer ) {
				$shivs_poll_answers[] = array( 'id' => $answer['id'], 'value' => html_entity_decode( (string)$answer['answer'], ENT_QUOTES, 'UTF-8' ), 'type' => $answer['type'] );
			}
		}

		$public_config = array( 'poll_options' => array( 'share_after_vote' => $shivs_poll_model->poll_options['share_after_vote'], 'share_name' => html_entity_decode( (string)$shivs_poll_model->poll_options['share_name'], ENT_QUOTES, 'UTF-8' ), 'share_caption' => html_entity_decode( (string)$shivs_poll_model->poll_options['share_caption'], ENT_QUOTES, 'UTF-8' ), 'share_description' => html_entity_decode( (string)$shivs_poll_model->poll_options['share_description'], ENT_QUOTES, 'UTF-8' ), 'share_picture' => html_entity_decode( (string)$shivs_poll_model->poll_options['share_picture'], ENT_QUOTES, 'UTF-8' ), 'share_question' => html_entity_decode( (string)$shivs_poll_model->poll['question'], ENT_QUOTES, 'UTF-8' ), 'share_poll_name' => html_entity_decode( (string)$shivs_poll_model->poll['name'], ENT_QUOTES, 'UTF-8' ), 'share_link' => $shivs_poll_model->poll_options['poll_page_url'] == '' ? site_url() : $shivs_poll_model->poll_options['poll_page_url'], 'answers' => $shivs_poll_answers, 'redirect_after_vote' => html_entity_decode( (string)$shivs_poll_model->poll['redirect_after_vote'], ENT_QUOTES, 'UTF-8' ), 'redirect_after_vote_url' => html_entity_decode( (string)$shivs_poll_model->poll['redirect_after_vote_url'], ENT_QUOTES, 'UTF-8' ),

			) );

	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<script type="text/javascript">

				function close_window() {
					var shivs_poll_various_config = new Object();
					shivs_poll_various_config.poll_id = '<?php echo self::base64_decode( $_GET['poll_id'] ) ?>';
					shivs_poll_various_config.unique_id = '<?php echo self::base64_decode( $_GET['unique_id'] ) ?>';
					shivs_poll_various_config.poll_location = '<?php echo self::base64_decode( $_GET['poll_location'] ) ?>';
					shivs_poll_various_config.is_modal = <?php echo self::base64_decode( $_GET['is_modal'] ) == 'true' ? 'true' : 'false'; ?>;
					shivs_poll_various_config.vote_loading_image_target = '<?php echo self::base64_decode( $_GET['vote_loading_image_target'] ) ?>';
					shivs_poll_various_config.vote_loading_image_id = '<?php echo self::base64_decode( $_GET['vote_loading_image_id'] ) ?>';
					shivs_poll_various_config.vote_type = '<?php echo self::base64_decode( $_GET['vote_type'] ) ?>';
					shivs_poll_various_config.facebook_user_details = '<?php echo isset( $_GET['facebook_user_details'] ) ? $_GET['facebook_user_details'] : '' ?>';
					shivs_poll_various_config.facebook_error = '<?php echo isset( $_GET['facebook_error'] ) ? $_GET['facebook_error'] : '' ?>';
					shivs_poll_various_config.public_config =  <?php echo json_encode( $public_config ); ?>;
					window.opener.jQuery( '#shivs-poll-nonce-' + shivs_poll_various_config.poll_id + shivs_poll_various_config.unique_id ).val( '<?php echo wp_create_nonce( 'shivs_poll-'.$poll_id.$unique_id.'-user-actions' ) ?>' );
					result = window.opener.shivs_poll_do_vote( shivs_poll_various_config );
					if ( result ) {
						window.close();
					}
				}
			</script>
		</head>
		<body onload="close_window()">
			<div style="margin:auto; width: 100px; height: 100px; text-align: center;"><img src="<?php echo $this->_config->plugin_url ?>/images/loading100x100.gif" alt="<?php _e( 'Loading', 'shivs_poll' ) ?>"/><br>
			</div>
		</body>
	</html>
	<?php
		die();
	}

	public function ajax_do_change_votes_number_answer() {
		global $current_user;
		$answer_id         = intval( $_POST['shivs_poll_answer_id'] );
		$votes_number      = intval( $_POST['shivs_poll_answer_votes'] );
		$change_to_all     = $_POST['shivs_poll_change_to_all_poll_answers'];
		$according_to_logs = $_POST['shivs_poll_update_answers_with_logs'];
		$response          = NULL;

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$answer_details = Shivs_Poll_Model::get_poll_answer_by_id( $answer_id );

		$shivs_poll_model = new Shivs_Poll_Model ( $answer_details['poll_id'] );
		$poll_details   = $shivs_poll_model->get_current_poll();

		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details ['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) ){
			$response = __( 'You are not allowed to edit this item.', 'shivs_poll' );
		}
		else {
			if ( !wp_verify_nonce( $_POST['shivs-poll-nonce-change-votes-number-answer-' . $answer_id], 'shivs_poll-change-votes-number-answer-action-' . $answer_id ) ){
				$response = __( 'Bad Request!', 'shivs_poll' );
			}
			else {
				if ( 'yes' == $according_to_logs ){
					if ( 'yes' == $change_to_all ){
						$poll_answers = Shivs_Poll_Model::get_poll_answers( $answer_details['poll_id'], array( 'default', 'other' ) );
						if ( count( $poll_answers ) > 0 )
							foreach ( $poll_answers as $answer ) {
								Shivs_Poll_Model::update_answer_field( $answer['id'], array( 'name' => 'votes', 'value' => Shivs_Poll_Model::get_answer_votes_from_logs( $answer['id'] ), 'type' => '%d' ) );
						}
					}
					else
						Shivs_Poll_Model::update_answer_field( $answer_id, array( 'name' => 'votes', 'value' => Shivs_Poll_Model::get_answer_votes_from_logs( $answer_id ), 'type' => '%d' ) );
					$response = __( 'Success', 'shivs_poll' );
				}
				else {
					if ( intval( $votes_number ) < 0 )
						$response = __( 'Invalid Number Of Votes', 'shivs_poll' ) . '!';
					else {
						if ( 'yes' == $change_to_all )
							Shivs_Poll_Model::update_all_poll_answers_field( $answer_details['poll_id'], array( 'name' => 'votes', 'value' => $votes_number, 'type' => '%d' ) );
						else
							Shivs_Poll_Model::update_answer_field( $answer_id, array( 'name' => 'votes', 'value' => $votes_number, 'type' => '%d' ) );
						$response = __( 'Success', 'shivs_poll' );
					}
				}
			}
		}
		print '[response]' . $response . '[/response]';
		die();
	}

	public function ajax_do_change_total_number_poll() {
		global $current_user;
		$poll_id              = intval( $_POST['shivs_poll_id'] );
		$total_votes          = intval( $_POST['shivs_poll_total_votes'] );
		$total_answers        = intval( $_POST['shivs_poll_total_answers'] );
		$type                 = $_POST['shivs_poll_type'];
		$change_to_all        = $_POST['shivs_poll_change_to_all'];
		$according_to_logs    = $_POST['shivs_poll_update_poll_with_logs'];
		$according_to_answers = $_POST['shivs_poll_update_poll_with_answers'];
		$response             = NULL;

		$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
		$poll_details   = $shivs_poll_model->get_current_poll();

		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details ['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) ){
			$response = __( 'You are not allowed to edit this item.', 'shivs_poll' );
		}
		else {
			if ( !wp_verify_nonce( $_POST['shivs-poll-nonce-change-total-number-poll-' . $poll_id], 'shivs_poll-change-total-number-poll-action-' . $poll_id ) ){
				$response = __( 'Bad Request!', 'shivs_poll' );
			}
			else {
				if ( 'yes' == $according_to_logs ){
					if ( 'votes' == $type ){
						if ( 'yes' == $change_to_all ){
							$all_polls = Shivs_Poll_Model::get_shivs_polls_filter_search();
							if ( count( $all_polls ) > 0 ){
								foreach ( $all_polls as $poll )
									Shivs_Poll_Model::update_poll_field( $poll['id'], array( 'name' => 'total_votes', 'value' => Shivs_Poll_Model::get_poll_total_votes_from_logs( $poll['id'] ), 'type' => '%d' ) );
							}
						}
						else {
							Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'total_votes', 'value' => Shivs_Poll_Model::get_poll_total_votes_from_logs( $poll_id ), 'type' => '%d' ) );
						}
					}
					if ( 'answers' == $type ){
						if ( 'yes' == $change_to_all ){
							$all_polls = Shivs_Poll_Model::get_shivs_polls_filter_search();
							if ( count( $all_polls ) > 0 ){
								foreach ( $all_polls as $poll )
									Shivs_Poll_Model::update_poll_field( $poll['id'], array( 'name' => 'total_answers', 'value' => Shivs_Poll_Model::get_poll_total_answers_from_logs( $poll['id'] ), 'type' => '%d' ) );
							}
						}
						else {
							Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'total_answers', 'value' => Shivs_Poll_Model::get_poll_total_answers_from_logs( $poll_id ), 'type' => '%d' ) );
						}
					}
					$response = __( 'Success', 'shivs_poll' );
				}
				elseif ( 'yes' == $according_to_answers ) {
					if ( 'votes' == $type ){
						if ( 'yes' == $change_to_all ){
							$all_polls = Shivs_Poll_Model::get_shivs_polls_filter_search();
							if ( count( $all_polls ) > 0 ){
								foreach ( $all_polls as $poll )
									Shivs_Poll_Model::update_poll_field( $poll['id'], array( 'name' => 'total_votes', 'value' => Shivs_Poll_Model::get_poll_total_votes_from_answers( $poll['id'] ), 'type' => '%d' ) );
							}
						}
						else {
							Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'total_votes', 'value' => Shivs_Poll_Model::get_poll_total_votes_from_answers( $poll_id ), 'type' => '%d' ) );
						}
					}
					$response = __( 'Success', 'shivs_poll' );
				}
				else {
					if ( intval( $total_votes ) < 0 && $type == 'votes' )
						$response = __( 'Invalid Number Of Total Votes', 'shivs_poll' ) . '!';
					if ( intval( $total_answers ) < 0 && $type == 'answers' )
						$response = __( 'Invalid Number Of Total Answers', 'shivs_poll' ) . '!';
					else {
						if ( 'votes' == $type ){
							if ( 'yes' == $change_to_all )
								Shivs_Poll_Model::update_all_polls_field( array( 'name' => 'total_votes', 'value' => $total_votes, 'type' => '%d' ) );
							else
								Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'total_votes', 'value' => $total_votes, 'type' => '%d' ) );
						}
						if ( 'answers' == $type ){
							if ( 'yes' == $change_to_all )
								Shivs_Poll_Model::update_all_polls_field( array( 'name' => 'total_answers', 'value' => $total_answers, 'type' => '%d' ) );
							else
								Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'total_answers', 'value' => $total_answers, 'type' => '%d' ) );
						}
						$response = __( 'Success', 'shivs_poll' );
					}
				}
			}
		}
		print '[response]' . $response . '[/response]';
		die();
	}

	public function ajax_do_change_poll_author() {
		global $current_user;
		$poll_id     = intval( $_POST['shivs_poll_id'] );
		$poll_author = intval( $_POST['shivs_poll_author'] );
		$response    = NULL;

		$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
		$poll_details   = $shivs_poll_model->get_current_poll();

		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details ['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) ){
			$response = __( 'You are not allowed to edit this item.', 'shivs_poll' );
		}
		else {
			if ( !wp_verify_nonce( $_POST['shivs-poll-nonce-change-poll-author-' . $poll_id], 'shivs_poll-change-poll-author-action-' . $poll_id ) ){
				$response = __( 'Bad Request!', 'shivs_poll' );
			}
			else {
				if ( intval( $poll_author ) <= 0 )
					$response = __( 'Invalid Poll Author', 'shivs_poll' ) . '!';
				else {
					Shivs_Poll_Model::update_poll_field( $poll_id, array( 'name' => 'poll_author', 'value' => $poll_author, 'type' => '%d' ) );
					$response = __( 'Success', 'shivs_poll' );
				}
			}
		}
		print '[response]' . $response . '[/response]';
		die();
	}

	public function ajax_do_change_template_author() {
		global $current_user;
		$template_id     = intval( $_POST['shivs_poll_template_id'] );
		$template_author = intval( $_POST['shivs_poll_template_author'] );
		$response        = NULL;

		$template_details = Shivs_Poll_Model::get_poll_template_from_database_by_id( $template_id );

		if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) || $template_details ['template_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) ){
			$response = __( 'You are not allowed to edit this item.', 'shivs_poll' );
		}
		else {
			if ( !wp_verify_nonce( $_POST['shivs-poll-nonce-change-poll-template-author-' . $template_id], 'shivs_poll-change-poll-template-author-action-' . $template_id ) ){
				$response = __( 'Bad Request!', 'shivs_poll' );
			}
			else {
				if ( intval( $template_author ) <= 0 )
					$response = __( 'Invalid Template Author', 'shivs_poll' ) . '!';
				else {
					Shivs_Poll_Model::update_template_field( $template_id, array( 'name' => 'template_author', 'value' => $template_author, 'type' => '%d' ) );
					$response = __( 'Success', 'shivs_poll' );
				}
			}
		}
		print '[response]' . $response . '[/response]';
		die();
	}

	public function ajax_show_change_votes_number_answer() {
		global $current_user;
		$answer_id = intval( $_GET['answer_id'] );

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$answer_details = Shivs_Poll_Model::get_poll_answer_by_id( $answer_id );
		$shivs_poll_model = new Shivs_Poll_Model ( $answer_details['poll_id'] );
		$poll_details   = $shivs_poll_model->get_current_poll();
		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details ['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) )
			wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
	?>
	<div id="shivs-poll-change-votes">
		<form id="shivs-poll-change-answer-no-votes-form">
			<table class="links-table" cellspacing="0">
				<tbody>
					<tr><td colspan="2" align="center"><b><?php echo $answer_details['answer']; ?></b></td></tr>
					<tr><td colspan="2" align="center" id="shivs-poll-change-no-votes-error" class="error-message"></td></tr>
					<tr id="shivs-poll-manual-change-no-votes">
						<td>
							<label class="shivs_poll_answer_no_votes_label" for="shivs-poll-answer-no-votes"><?php _e( 'New Number Of Votes', 'shivs_poll' ); ?>
								:</label></td>
						<td>
							<input id="shivs-poll-answer-no-votes" type="text" value="<?php echo $answer_details['votes']; ?>" name="shivs_poll_answer_votes"/>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="checkbox" name="shivs_poll_update_answers_with_logs" value="yes" id="shivs-poll-update-answers-with-logs" onclick="if ( jQuery(this).prop('checked') ) jQuery( '#shivs-poll-manual-change-no-votes').hide(); else  jQuery( '#shivs-poll-manual-change-no-votes').show();"/>
							<label for="shivs-poll-update-answers-with-logs"><?php _e( 'Update According To Logs', 'shivs_poll' ); ?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="checkbox" name="shivs_poll_change_to_all_poll_answers" value="yes" id="shivs-poll-change-to-all-poll-answers" onclick="if ( jQuery(this).prop('checked') )return confirm('<?php _e( 'Are You Sure You Want To Change To All Poll Answers?', 'shivs_poll' ); ?>');"/>
							<label for="shivs-poll-change-to-all-poll-answers"><?php _e( 'Change To All Poll Answers', 'shivs_poll' ); ?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="button" class="button-primary" value="<?php _e( 'Save', 'shivs_poll' ) ?>" onclick="shivs_poll_do_change_votes_number_answer( '<?php echo $answer_details['id']; ?>' )"/>
							<input type="hidden" name="shivs_poll_answer_id" value="<?php echo $answer_details['id']; ?>"/>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'shivs_poll-change-votes-number-answer-action-' . $answer_id, 'shivs-poll-nonce-change-votes-number-answer-' . $answer_id, false ); ?>
		</form>
	</div>
	<?php
		die();
	}

	public function ajax_show_change_total_number_poll() {
		global $current_user;
		$poll_id = intval( $_GET['poll_id'] );
		$type    = $_GET['type'];

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
		$poll_details   = $shivs_poll_model->get_current_poll();
		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_details ['poll_author'] != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) )
			wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
	?>
	<div id="shivs-poll-change-total">
		<form id="shivs-poll-change-poll-total-no-form">
			<table class="links-table" cellspacing="0">
				<tbody>
					<tr><td colspan="2" align="center"><b><?php echo $poll_details['name']; ?></b></td></tr>
					<tr><td colspan="2" align="center" id="shivs-poll-change-total-no-error" class="error-message"></td></tr>
					<?php if ( 'votes' == $type ){ ?>
						<tr id="shivs-poll-manual-change-no-votes">
							<td>
								<label class="shivs_poll_total_votes_label" for="shivs-poll-total-votes"><?php _e( 'New Number Of Poll Total Votes', 'shivs_poll' ); ?>
									:</label></td>
							<td>
								<input id="shivs-poll-total-votes" type="text" value="<?php echo $poll_details['total_votes']; ?>" name="shivs_poll_total_votes"/>
							</td>
						</tr>
						<?php } ?>
					<?php if ( 'answers' == $type ){ ?>
						<tr id="shivs-poll-manual-change-no-votes">
							<td>
								<label class="shivs_poll_total_answers_label" for="shivs-poll-total-answers"><?php _e( 'New Number Of Poll Total Answers', 'shivs_poll' ); ?>
									:</label></td>
							<td>
								<input id="shivs-poll-total-answers" type="text" value="<?php echo $poll_details['total_answers']; ?>" name="shivs_poll_total_answers"/>
							</td>
						</tr>
						<?php } ?>
					<tr>
						<td colspan="2">
							<input type="checkbox" name="shivs_poll_update_poll_with_logs" value="yes" id="shivs-poll-update-poll-with-logs" onclick="if ( jQuery(this).prop('checked') ) { jQuery( '#shivs-poll-manual-change-no-votes').hide(); jQuery('#shivs-poll-update-poll-with-answers').attr('checked', false)} else { if ( ! jQuery('#shivs-poll-update-poll-with-answers').prop('checked') ) jQuery( '#shivs-poll-manual-change-no-votes').show();} "/>
							<label for="shivs-poll-update-poll-with-logs"><?php _e( 'Update According To Logs', 'shivs_poll' ); ?></label>
						</td>
					</tr>
					<?php if ( 'votes' == $type ){ ?>
						<tr>
							<td colspan="2">
								<input type="checkbox" name="shivs_poll_update_poll_with_answers" value="yes" id="shivs-poll-update-poll-with-answers" onclick="if ( jQuery(this).prop('checked') ) {jQuery( '#shivs-poll-manual-change-no-votes').hide(); jQuery('#shivs-poll-update-poll-with-logs').attr('checked', false ); } else { if ( ! jQuery('#shivs-poll-update-poll-with-logs').prop('checked') ) jQuery( '#shivs-poll-manual-change-no-votes').show(); }"/>
								<label for="shivs-poll-update-poll-with-answers"><?php _e( 'Update According To Answers', 'shivs_poll' ); ?></label>
							</td>
						</tr>
						<?php } ?>
					<tr>
						<td colspan="2">
							<input type="checkbox" name="shivs_poll_change_to_all" value="yes" id="shivs-poll-change-to-all" onclick="if ( jQuery(this).prop('checked') )return confirm('<?php _e( 'Are You Sure You Want To Change To All Polls?', 'shivs_poll' ); ?>');"/>
							<label for="shivs-poll-change-to-all"><?php _e( 'Change To All Polls', 'shivs_poll' ); ?></label>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="button" class="button-primary" value="<?php _e( 'Save', 'shivs_poll' ) ?>" onclick="shivs_poll_do_change_total_number_poll( '<?php echo $poll_details['id']; ?>', '<?php echo $type; ?>')"/>
							<input type="hidden" name="shivs_poll_id" value="<?php echo $poll_details['id']; ?>"/>
							<input type="hidden" name="shivs_poll_type" value="<?php echo $type; ?>"/>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'shivs_poll-change-total-number-poll-action-' . $poll_id, 'shivs-poll-nonce-change-total-number-poll-' . $poll_id, false ); ?>
		</form>
	</div>
	<?php
		die();
	}

	public function ajax_show_change_poll_author() {
		global $current_user;
		$poll_id = intval( $_GET['poll_id'] );

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$shivs_poll_model = new Shivs_Poll_Model ( $poll_id );
		$poll_details   = $shivs_poll_model->get_current_poll();
		$poll_author    = get_user_by( 'id', $poll_details ['poll_author'] );
		if ( ( !$this->current_user_can( 'edit_own_polls' ) || $poll_author->ID != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls' ) ) )
			wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
	?>
	<div id="shivs-poll-change-poll-author">
		<form id="shivs-poll-change-poll-author-form">
			<table class="links-table" cellspacing="0">
				<tbody>
					<tr><td colspan="2" align="center"><b><?php echo $poll_details['name']; ?></b></td></tr>
					<tr><td colspan="2" align="center" id="shivs-poll-change-poll-author-error" class="error-message"></td></tr>
					<tr id="shivs-poll-manual-change-no-votes">
						<td>
							<label class="shivs_poll_total_votes_label" for="shivs-poll-author"><?php _e( 'New Poll Author', 'shivs_poll' ); ?>
								:</label></td>
						<td>
							<?php
								$blogusers = get_users( 'blog_id=' . $GLOBALS['blog_id'] . '&orderby=nicename&order=ASC' );
							?>
							<select id="shivs-poll-author-select" name="shivs_poll_author">
								<?php
									foreach ( $blogusers as $user ) {
										echo '<option ' . selected( $poll_author->ID, $user->ID, false ) . ' value="' . $user->ID . '">' . $user->user_nicename . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="button" class="button-primary" value="<?php _e( 'Save', 'shivs_poll' ) ?>" onclick="shivs_poll_do_change_poll_author( '<?php echo $poll_details['id']; ?>')"/>
							<input type="hidden" name="shivs_poll_id" value="<?php echo $poll_details['id']; ?>"/>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'shivs_poll-change-poll-author-action-' . $poll_id, 'shivs-poll-nonce-change-poll-author-' . $poll_id, false ); ?>
		</form>
	</div>
	<?php
		die();
	}

	public function ajax_show_change_template_author() {
		global $current_user;
		$template_id = intval( $_GET['template_id'] );

		require_once( $this->_config->plugin_inc_dir . '/shivs_poll_model.php' );
		$template_details = Shivs_Poll_Model::get_poll_template_from_database_by_id( $template_id );
		$template_author  = get_user_by( 'id', $template_details ['template_author'] );
		if ( ( !$this->current_user_can( 'edit_own_polls_templates' ) || $template_author->ID != $current_user->ID ) && ( !$this->current_user_can( 'edit_polls_templates' ) ) )
			wp_die( __( 'You are not allowed to edit this item.', 'shivs_poll' ) );
	?>
	<div id="shivs-poll-change-template-author">
		<form id="shivs-poll-change-template-author-form">
			<table class="links-table" cellspacing="0">
				<tbody>
					<tr><td colspan="2" align="center"><b><?php echo $template_details['name']; ?></b></td></tr>
					<tr><td colspan="2" align="center" id="shivs-poll-change-template-author-error" class="error-message"></td></tr>
					<tr>
						<td>
							<label for="shivs-template-author"><?php _e( 'New Template Author', 'shivs_poll' ); ?>
								:</label></td>
						<td>
							<?php
								$blogusers = get_users( 'blog_id=' . $GLOBALS['blog_id'] . '&orderby=nicename&order=ASC' );
							?>
							<select id="shivs-poll-template-author-select" name="shivs_poll_template_author">
								<?php
									foreach ( $blogusers as $user ) {
										echo '<option ' . selected( $template_author->ID, $user->ID, false ) . ' value="' . $user->ID . '">' . $user->user_nicename . '</option>';
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="button" class="button-primary" value="<?php _e( 'Save', 'shivs_poll' ) ?>" onclick="shivs_poll_do_change_template_author( '<?php echo $template_details['id']; ?>')"/>
							<input type="hidden" name="shivs_poll_template_id" value="<?php echo $template_details['id']; ?>"/>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'shivs_poll-change-poll-template-author-action-' . $template_id, 'shivs-poll-nonce-change-poll-template-author-' . $template_id, false ); ?>
		</form>
	</div>
	<?php
		die();
	}
	/**
	* End Ajax section
	*/

	/* start tinymce */
	function load_editor_functions( $hook ) {
		global $post;

		if ( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'page-new.php' || $hook == 'page.php' ){
			$shivs_poll_editor_config = array( 'dialog_url' => wp_nonce_url( admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) . '?action=shivs_poll_editor', 'shivs-poll-editor' ), 'dialog_html_url' => wp_nonce_url( admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) . '?action=shivs_poll_html_editor', 'shivs-poll-html-editor' ), 'name' => __( 'Shivs Poll', 'shivs_poll' ), 'title' => __( 'Insert Poll', 'shivs_poll' ), 'prompt_insert_poll_id' => __( 'Please insert the poll ID:\n\n', 'shivs_poll' ), 'prompt_insert_again_poll_id' => __( 'Error: Poll Id must be numeric!\n\nPlease insert the poll ID Again:\n\n', 'shivs_poll' ) );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'shivs-poll-editor-functions', "{$this->_config->plugin_url}/tinymce/shivs-poll-editor-functions.js", 'jquery', $this->_config->version, true );
			wp_localize_script( 'shivs-poll-editor-functions', 'shivs_poll_editor_config', $shivs_poll_editor_config );
		}
	}

	function register_button( $buttons ) {
		array_push( $buttons, "separator", "shivspoll" );
		return $buttons;
	}

	function add_plugin( $plugin_array ) {
		$plugin_array ['shivspoll'] = "{$this->_config->plugin_url}/tinymce/shivs-poll-editor.js";
		return $plugin_array;
	}

	function my_shivs_poll_button( $hook ) {
		if ( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'page-new.php' || $hook == 'page.php' ){
			if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ){
				return;
			}

			if ( get_user_option( 'rich_editing' ) == 'true' ){
				add_filter( 'mce_external_plugins', array( &$this, 'add_plugin' ) );
				add_filter( 'mce_buttons', array( &$this, 'register_button' ) );
			}
		}
	}
	/**
	* end tinymce
	*/

	private function current_user_can( $capability = '' ) {

		global $current_user;

		get_currentuserinfo();

		$user_roles = $current_user->roles;

		$user_role  = array_shift( $user_roles );

		$capabilities_roles = array(
			'manage_polls_options' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'manage_polls_bans' => array( 'administrator' => true, 'editor' => false, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'delete_polls' => array( 'administrator' => true, 'editor' => false, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'delete_own_polls' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, ),
			'edit_polls' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'edit_own_polls' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'clone_polls' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'clone_own_polls' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'view_polls_logs' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'view_own_polls_logs' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'view_polls_results' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'view_own_polls_results' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'reset_polls_stats' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'reset_own_polls_stats' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'delete_polls_logs' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'delete_own_polls_logs' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'edit_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'edit_own_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'delete_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'delete_own_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'clone_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => false, 'contributor' => false, 'subscriber' => false ),
			'clone_own_polls_templates' => array( 'administrator' => true, 'editor' => true, 'author' => true, 'contributor' => false, 'subscriber' => false ),
			'become_pro' => array( 'administrator' => false, 'editor' => false, 'author' => false, 'contributor' => false, 'subscriber' => false )
			 );

		if ( isset ( $capabilities_roles [$capability] [$user_role] ) )
			return $capabilities_roles [$capability] [$user_role];
		return false;
	}
}

?>