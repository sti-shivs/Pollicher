<?php

class Shivs_Poll_Widget extends WP_Widget {
	
	function Shivs_Poll_Widget() {
		$widget_options	= array(
			'classname'	=> 'Shivs Poll Widget',
			'description'	=> 'Shivs Poll Polls'
		);
		parent::WP_Widget('shivs_poll_widget', 'Shivs Polls', $widget_options );
	}

	function widget( $args, $instance ) {

		extract ( $args, EXTR_SKIP );

		$title				= ( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Shivs Poll Widget', 'shivs_poll' );
		$poll_id			= ( $instance['poll_id'] ) ? intval( $instance['poll_id'] ) : -1;
		$tr_id				= ( $instance['tr_id'] ) ? $instance['tr_id'] : '';
		$poll_unique_id		= uniqid( '_yp' );

		if ( -99 == $poll_id )
			return '';

		$pro_options		= get_option( 'shivs_poll_pro_options' );

		require_once( SHIVS_POLL_INC.'/shivs_poll_model.php');

		$shivs_poll_model	= new SHIVS_POLL_MODEL( $poll_id );
		$shivs_poll_model->set_unique_id( $poll_unique_id );
		$poll_id			= $shivs_poll_model->poll['id'];
		
		$answers			= SHIVS_POLL_MODEL::get_poll_answers( $poll_id, array( 'default', 'other') );

		$shivs_poll_answers	= array();

		if ( count( $answers ) > 0 ) {
			foreach ( $answers as $answer ) {
				$shivs_poll_answers[]	= array( 'id' => $answer['id'], 'value' => html_entity_decode( (string) $answer['answer'], ENT_QUOTES, 'UTF-8'), 'type' => $answer['type'] );
			}
		}
		
		$template			= $shivs_poll_model->return_poll_html( array( 'tr_id' => $tr_id, 'location' => 'widget', 'load_css' => true, 'load_js' => true ) );

		if ( 'yes' == $shivs_poll_model->poll_options['use_default_loading_image'] )
			$loading_image_src	= SHIVS_POLL_URL.'/images/loading36x36.gif';
		else
			$loading_image_src	= $shivs_poll_model->poll_options['loading_image_url'];

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'shivs-poll-jquery-popup-windows', SHIVS_POLL_URL . "/js/jquery.popupWindow.js",array( 'jquery' ), SHIVS_POLL_VERSION, true);
		wp_enqueue_style( 'shivs-poll-public', SHIVS_POLL_URL."/css/shivs-poll-public.css", array(), SHIVS_POLL_VERSION );
		wp_enqueue_script( 'shivs-poll-widget-user-defined_'.$poll_id.$poll_unique_id, add_query_arg( array( 'id' => $poll_id, 'location' => 'widget', 'unique_id' => $poll_unique_id ), admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http')).'?action=shivs_poll_load_js' ), array( 'jquery' ), SHIVS_POLL_VERSION, true);
		wp_enqueue_script( 'shivs-poll-public', SHIVS_POLL_URL.'/js/shivs-poll-public.js', array(), SHIVS_POLL_VERSION, true );
		wp_enqueue_script( 'shivs-poll-json2', SHIVS_POLL_URL."/js/shivs-poll-json2.js", array(), SHIVS_POLL_VERSION, true );
		wp_enqueue_script( 'shivs-poll-jquery-base64', SHIVS_POLL_URL."/js/shivs-poll-jquery.base64.min.js", array(), SHIVS_POLL_VERSION, true );
		
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
			'shivs_poll_version'              => SHIVS_POLL_VERSION,
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
		wp_localize_script( 'shivs-poll-public', 'shivs_poll_public_config_'.$poll_id.$poll_unique_id, $shivs_poll_public_config );
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo do_shortcode($template);
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {

		var_dump( $new_instance );

		if ( ! isset( $new_instance['doSave'] ) )
			return false;

		if ( 'yes' != $new_instance['doSave'] )
			return false;

		$instance				= $old_instance;
		$instance['title']		= strip_tags($new_instance['title']);
		$instance['poll_id']	= intval($new_instance['poll_id']);
		$instance['tr_id']		= $new_instance['tr_id'];

		return $instance;
	}

	function form( $instance ) {

		$instance 	= wp_parse_args( (array) $instance, array('title' => __('Shivs Polls', 'shivs_poll'), 'poll_id' => -99) );
		
		$title		= esc_attr( $instance['title'] );
		
		$poll_id	= intval( $instance['poll_id'] );
		
		$tr_id		= $instance['tr_id'];
		
		global $wpdb;
		
		require_once( SHIVS_POLL_INC.'/shivs_poll_model.php');
		
		$shivs_polls	= Shivs_Poll_Model::get_shivs_polls_filter_search( 'id', 'asc' );
	?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">
			<span>Title:</span>
			<input id="<?php echo $this->get_field_id('title'); ?>"
				name="<?php echo $this->get_field_name('title'); ?>"
				value="<?php echo $title ?>" />
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('poll_id'); ?>">
			<span>Poll to Display:</span>
			<select id="<?php echo $this->get_field_id('poll_id'); ?>" name="<?php echo $this->get_field_name('poll_id'); ?>" class="widefat">
				<option value="-99"<?php selected(-99, $poll_id); ?>><?php _e('Do NOT Display Poll (Disable)', 'shivs-poll'); ?></option>
				<option value="-3"<?php selected(-3, $poll_id); ?>><?php _e('Display Random Poll', 'shivs-poll'); ?></option>
				<option value="-2"<?php selected(-2, $poll_id); ?>><?php _e('Display Latest Poll', 'shivs-poll'); ?></option>
				<option value="-1"<?php selected(-1, $poll_id); ?>><?php _e('Display Current Active Poll', 'shivs-poll'); ?></option>
				<?php
					if( count( $shivs_polls ) > 0 ) {
						foreach( $shivs_polls as $poll ) {
						?>
						<option value="<?php echo $poll['id']; ?>"<?php selected($poll['id'], $poll_id); ?>><?php echo esc_attr( $poll['name'] ); ?></option>
						<?php
						}
					}
				?>
			</select>
		</label>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('tr_id'); ?>">
			<span>Tracking ID:</span>
			<input id="<?php echo $this->get_field_id('tr_id'); ?>"
				name="<?php echo $this->get_field_name('tr_id'); ?>"
				value="<?php echo $tr_id ?>" />
		</label>
	</p>
	<input type="hidden" id="<?php echo $this->get_field_id('doSave'); ?>" name="<?php echo $this->get_field_name('doSave'); ?>" value="yes" />
	<?php
	}
}

?>