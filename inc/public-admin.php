<?php

class Shivs_Poll_Public_Admin extends Shivs_Poll_Plugin {

	protected function init() {
		$this->add_action( 'init', 'load_translation_file', 1 );
        $this->add_filter( 'the_content', 'shivs_poll_do_shortcode_the_content_filter', 1 );
		$this->add_action( 'init', 'public_loader', 1 );
		$this->add_action( 'widgets_init', 'widget_init' );
		$this->add_filter( 'widget_text', 'do_shortcode');
		$this->add_action( 'init', 'shivs_poll_setup_schedule');
		$this->add_action( 'shivs_poll_hourly_event', 'shivs_poll_do_scheduler' );
	}

	public function shivs_poll_setup_schedule() {

		$schedule_timestamp	= wp_next_scheduled( 'shivs_poll_hourly_event', array() );

		$shivs_poll_options	= get_option( 'shivs_poll_options', false );

		if ( 'yes' == $shivs_poll_options['start_scheduler'] ) {
			if ( ! $schedule_timestamp ) {
				wp_schedule_event( strtotime( substr( current_time( 'mysql'), 0, 14 ).'00:01' ), 'hourly', 'shivs_poll_hourly_event', array() );
			}
		}
		else {
			wp_unschedule_event( $schedule_timestamp, 'shivs_poll_hourly_event', array() );
		}
	}

	public function shivs_poll_do_scheduler() {

		require_once ($this->_config->plugin_inc_dir . '/shivs_poll_model.php');

		$shivs_polls = Shivs_Poll_Model::get_shivs_polls_fields ( array( 'id' ) );

		if ( count( $shivs_polls ) > 0 ) {

			foreach( $shivs_polls as $shivs_poll_id ) {

				$shivs_poll_options	= Shivs_Poll_Model::get_poll_options_by_id( $shivs_poll_id['id'] );
				
				if ( 'yes' == $shivs_poll_options['schedule_reset_poll_stats'] ) {

					if ( $shivs_poll_options['schedule_reset_poll_date'] <= current_time( 'timestamp' ) ) {

						$unit_multiplier = 0;

						if ( 'hour' == strtolower( trim( $shivs_poll_options['schedule_reset_poll_recurring_unit'] ) ) )
							$unit_multiplier = 60 * 60;

						if ( 'day' == strtolower( trim( $shivs_poll_options['schedule_reset_poll_recurring_unit'] ) ) )
							$unit_multiplier = 60 * 60 * 24;

						$next_reset_date = $shivs_poll_options['schedule_reset_poll_date'] + intval( $shivs_poll_options['schedule_reset_poll_recurring_value'] ) * $unit_multiplier;

						if ( $next_reset_date <= current_time( 'timestamp' ) ) {
							$next_reset_date = strtotime( substr( current_time( 'mysql'), 0, 11 ) . substr( date('Y-m-d H:i:s', $shivs_poll_options['schedule_reset_poll_date'] ), 11, 2 ) . ':00:00' ) + intval( $shivs_poll_options['schedule_reset_poll_recurring_value'] ) * $unit_multiplier;	
						} 

						$poll_options = get_shivs_poll_meta( $shivs_poll_id['id'], 'options', true );

						$poll_options['schedule_reset_poll_date'] = $next_reset_date; 

						update_shivs_poll_meta( $shivs_poll_id['id'], 'options', $poll_options ); 

						Shivs_Poll_Model::reset_votes_for_poll ( $shivs_poll_id['id'] );  
					}
				}
			}
		}
	}

	public function load_translation_file() {
		$plugin_path = $this->_config->plugin_dir . '/' . $this->_config->languages_dir;
		load_plugin_textdomain( 'shivs_poll', false, $plugin_path );
	}

	public function do_shortcode( $content ) {
		return do_shortcode( $content );
	}

	public function public_loader() {
		add_shortcode( 'shivs_poll', array( &$this, 'shivs_poll_shortcode_function' ) );
		add_shortcode( 'shivs_poll_archive', array( &$this, 'shivs_poll_archive_shortcode_function' ) );
	}

	public function shivs_poll_archive_shortcode_function() {
		$template = '';
		$shivs_poll_page = 1;
		$big = 99999;

		if ( isset( $_REQUEST['shivs_poll_page'] ) )
			$shivs_poll_page	= $_REQUEST['shivs_poll_page'];

		$general_default_options	= get_option( 'shivs_poll_options', false );

		require_once( $this->_config->plugin_inc_dir.'/shivs_poll_model.php');

		$archive = SHIVS_POLL_MODEL::get_archive_polls( 'archive_order', 'asc', ( intval( $shivs_poll_page ) - 1)  * intval( $general_default_options['archive_polls_per_page'] ), intval( $general_default_options['archive_polls_per_page'] ) );
		
		$total_archive = ceil( count( SHIVS_POLL_MODEL::get_archive_polls( 'archive_order', 'asc', 0, $big ) ) / intval( $general_default_options['archive_polls_per_page'] ) );
		
		if ( count( $archive ) > 0 ) {
			foreach( $archive as $poll ) {
				$template	.= $this->return_shivs_poll( $poll['id'] );
			}
		}
		
		$args = array(
			'base'         => remove_query_arg( 'shivs_poll_page', $_SERVER['REQUEST_URI'] ).'%_%',
			'format'       => '?shivs_poll_page=%#%',
			'total'        => $total_archive,
			'current'      => max( 1, $shivs_poll_page ),
			'prev_next'    => True,
			'prev_text'    => __('&laquo; Previous', 'shivs_poll'),
			'next_text'    => __('Next &raquo;', 'shivs_poll')
		);
		return $template.paginate_links( $args );
	}

	public function return_shivs_poll( $id, $tr_id = '', $offset = 0 ) {
		
		$pro_options = get_option( 'shivs_poll_pro_options' );
		
		require_once( $this->_config->plugin_inc_dir.'/shivs_poll_model.php');
		
		$poll_unique_id = uniqid( '_yp' );
		
		$shivs_poll_model = new SHIVS_POLL_MODEL( $id, $offset );
		
		$shivs_poll_model->set_unique_id( $poll_unique_id );

		$id = $shivs_poll_model->poll['id'];

		$answers = SHIVS_POLL_MODEL::get_poll_answers( $id, array( 'default', 'other') );

		$shivs_poll_answers	= array();

		if ( count( $answers ) > 0 ) {
			foreach ( $answers as $answer ) {
				$shivs_poll_answers[]	= array( 'id' => $answer['id'], 'value' => html_entity_decode( (string) $answer['answer'], ENT_QUOTES, 'UTF-8'), 'type' => $answer['type'] );
			}
		}

		if ( ! $shivs_poll_model->poll )
			return '';

		$template = $shivs_poll_model->return_poll_html( array( 'tr_id' => $tr_id, 'location' => 'page', 'load_css' => true, 'load_js' => true ) );
		if ( 'yes' == $shivs_poll_model->poll_options['use_default_loading_image'] )
			$loading_image_src = $this->_config->plugin_url.'/images/loading36x36.gif';
		else
			$loading_image_src = $shivs_poll_model->poll_options['loading_image_url'];
		
		wp_enqueue_style( 'shivs-poll-public', "{$this->_config->plugin_url}/css/shivs-poll-public.css", array(), $this->_config->version );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'shivs-poll-jquery-popup-windows', "{$this->_config->plugin_url}/js/jquery.popupWindow.js", array(), $this->_config->version, true ); 
		wp_enqueue_script( 'shivs-poll-user-defined_'.$id.$poll_unique_id, add_query_arg( array( 'id' => $id, 'location' => 'page', 'unique_id' => $poll_unique_id ), admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')).'?action=shivs_poll_load_js' ), array( 'jquery' ), $this->_config->version, true);
		wp_enqueue_script( 'shivs-poll-public', "{$this->_config->plugin_url}/js/shivs-poll-public.js", array(), $this->_config->version, true );
		wp_enqueue_script( 'shivs-poll-json2', "{$this->_config->plugin_url}/js/shivs-poll-json2.js", array(), $this->_config->version, true );
		wp_enqueue_script( 'shivs-poll-jquery-base64', "{$this->_config->plugin_url}/js/shivs-poll-jquery.base64.min.js", array(), $this->_config->version, true );

		$shivs_poll_public_config_general = array(
			'ajax'	=> array(
				'url'                           => admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')),
				'vote_action'                   => 'shivs_poll_do_vote',
				'shivs_poll_show_vote_options'    => 'shivs_poll_show_vote_options',
				'captcha_action'                => 'shivs_poll_show_captcha',
				'view_results_action'           => 'shivs_poll_view_results',
				'back_to_vote_action'           => 'shivs_poll_back_to_vote',
				'is_wordpress_user_action'      => 'shivs_poll_is_wordpress_user'
			),
			'pro'	=> array(
				'api_key'           => $pro_options['pro_api_key'],
				'pro_user'          => $pro_options['pro_user'],
				'api_server_url'    => $pro_options['pro_api_server_url'],
				'pro_token'         => md5( $_SERVER['HTTP_HOST'] . $pro_options['pro_key'] )
			),
			'shivs_poll_version'              => $this->_config->version,
			'vote_with_wordpress_login_url' => wp_login_url( admin_url('admin-ajax.php?action=shivs_poll_set_wordpress_vote', (is_ssl() ? 'https' : 'http')) ),
			'vote_with_facebook_ajax_url' 	=> admin_url('admin-ajax.php?action=shivs_poll_set_wordpress_vote', (is_ssl() ? 'https' : 'http')),
		);

		$vote_permisions_types	= 0;
		if ( 'quest-only' != $shivs_poll_model->poll_options['vote_permisions'] ) {
			if ( 'yes'	== $shivs_poll_model->poll_options['vote_permisions_wordpress'] )
				$vote_permisions_types += 1;
			if ( 'yes'	== $shivs_poll_model->poll_options['vote_permisions_anonymous'] )
				$vote_permisions_types += 2;
			if ( 'yes'	== $shivs_poll_model->poll_options['vote_permisions_facebook'] && 'yes' == $pro_options['pro_user'] )
				$vote_permisions_types += 4;
		}

		$shivs_poll_public_config = array(
			'poll_options'	=> array(
				'vote_permisions'					=> $shivs_poll_model->poll_options['vote_permisions'],
				'vote_permisions_facebook_label'	=> $shivs_poll_model->poll_options['vote_permisions_facebook_label'],
				'vote_permisions_wordpress_label'	=> $shivs_poll_model->poll_options['vote_permisions_wordpress_label'],
				'vote_permisions_anonymous_label'	=> $shivs_poll_model->poll_options['vote_permisions_anonymous_label'],
				'vote_permisions_types'				=> $vote_permisions_types,
				'share_after_vote'					=> $shivs_poll_model->poll_options['share_after_vote'],
				'share_name'						=> $shivs_poll_model->poll_options['share_name'],
				'share_caption'						=> $shivs_poll_model->poll_options['share_caption'],
				'share_description'					=> $shivs_poll_model->poll_options['share_description'],
				'share_picture'						=> $shivs_poll_model->poll_options['share_picture'],
				'share_question'					=> $shivs_poll_model->poll['question'],
				'share_poll_name'					=> $shivs_poll_model->poll['name'],
				'share_link'						=> $shivs_poll_model->poll_options['poll_page_url'] == '' ? site_url() : $shivs_poll_model->poll_options['poll_page_url'],
				'answers'							=> $shivs_poll_answers,
				'redirect_after_vote'				=> $shivs_poll_model->poll_options['redirect_after_vote'],
				'redirect_after_vote_url'			=> $shivs_poll_model->poll_options['redirect_after_vote_url'],
			),
			'loading_image_src'				=> 	$loading_image_src,
			'loading_image_alt'				=> __( 'Loading', 'shivs_poll'),

		);
		wp_localize_script( 'shivs-poll-public', 'shivs_poll_public_config_general', $shivs_poll_public_config_general );
		wp_localize_script( 'shivs-poll-public', 'shivs_poll_public_config_'.$id.$poll_unique_id, $shivs_poll_public_config );

		return $template;
	}

	public function shivs_poll_shortcode_function ( $atts, $content = NULL ) {
		extract( shortcode_atts( array(
			'id' => -1,
			'tr_id' => '',
			'offset' => 0,
			), $atts ) );
		return $this->return_shivs_poll( $id, $tr_id, $offset );

	}

	public function shivs_poll_do_shortcode_the_content_filter( $content ) {

        global $shortcode_tags;

        // back-up current registered shortcodes and clear them all out
        $orig_shortcode_tags = $shortcode_tags;

        // registered shortcode
        $shortcode_tags      = array();

        // do the shortcode above
        $content = do_shortcode( $content );

        // put back the original shortcodes
        $shortcode_tags = $orig_shortcode_tags;

        return $content;
	}

	public function widget_init(){
		register_widget('Shivs_Poll_Widget');
	}
}

?>