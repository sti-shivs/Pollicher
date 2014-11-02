<?php

/**
 * Plugin Name: Pollicher
 * URI: http://shivs.byethost22.com
 * Description: SHIVS Extention
 * Author: Ephramar Telog
 * Author URL: http://profiles.wordpress.org/krzheiyah
 * Version: 4.9.1
 * Network: false
 */

define( 'SHIVS_POLL_WP_VERSION', '3.3' );
define( 'SHIVS_POLL_VERSION', '4.9.1' );
define( 'SHIVS_POLL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHIVS_POLL_URL', plugins_url( '', __FILE__ ) );
define( 'SHIVS_POLL_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'SHIVS_POLL_PLUGIN_DIR', plugin_basename( dirname( __FILE__ ) ) );
define( 'SHIVS_POLL_INC', SHIVS_POLL_PATH . 'inc' );

require_once( SHIVS_POLL_INC . '/plugin.php' );
require_once( SHIVS_POLL_INC . '/config.php' );
require_once( SHIVS_POLL_INC . '/plugin-functions.php' );
require_once( ABSPATH . WPINC . '/pluggable.php' );
require_once( SHIVS_POLL_INC . '/public-admin.php' );
require_once( SHIVS_POLL_INC . '/widget.php' );
require_once( SHIVS_POLL_INC . '/theme-functions.php' );

global $wpdb;

// SHIVS Poll Tables
$wpdb->shivs_poll_version             = SHIVS_POLL_VERSION;
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

$shivs_poll_current_class = 'Shivs_Poll_';

if ( is_admin() ) {
    $shivs_poll_current_class .= 'Admin';
    require_once( SHIVS_POLL_INC . '/admin.php' );
}
else {
    $shivs_poll_current_class .= 'Public';
    require_once( SHIVS_POLL_INC . '/public.php' );
}

$shivs_poll_config_data = array(
    'plugin_file' => SHIVS_POLL_PLUGIN_FILE,
    'plugin_url' => SHIVS_POLL_URL,
    'plugin_path' => SHIVS_POLL_PATH,
    'plugin_dir' => SHIVS_POLL_PLUGIN_DIR,
    'plugin_inc_dir' => SHIVS_POLL_INC,
    'languages_dir' => 'languages',
    'min_number_of_answers' => 1,
    'min_number_of_customfields' => 0,
    'version' => SHIVS_POLL_VERSION
);

$shivs_poll_public_admin = new Shivs_Poll_Public_Admin ( new Shivs_Poll_Config ( $shivs_poll_config_data ) );
$shivs_poll = new $shivs_poll_current_class ( new Shivs_Poll_Config ( $shivs_poll_config_data ) );

function shivs_poll_uninstall() {

    global $wpdb;

    if ( function_exists( 'is_multisite' ) && is_multisite() ) {
        $old_blog = $wpdb->blogid;

        $blogids  = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );

        foreach ( $blogids as $blog_id ) {
            switch_to_blog( $blog_id );

            delete_option( 'shivs_poll_version' );
            delete_option( 'shivs_poll_options' );
            delete_option( 'shivs_poll_first_install_date' );
            delete_option( 'shivs_poll_admin_notices_donate' );
            delete_option( 'shivs_poll_optin_box_modal_options' );
            delete_option( 'shivs_poll_pro_options' );

            $wpdb->query( "DROP TABLE `" . $wpdb->prefix . "shivs_pollmeta`, `" . $wpdb->prefix . "shivs_polls`, `" . $wpdb->prefix . "shivs_poll_answermeta`, `" . $wpdb->prefix . "shivs_poll_answers`, `" . $wpdb->prefix . "shivs_poll_custom_fields`, `" . $wpdb->prefix . "shivs_poll_logs`, `" . $wpdb->prefix . "shivs_poll_voters`, `" . $wpdb->prefix . "shivs_poll_bans`, `" . $wpdb->prefix . "shivs_poll_templates`, `" . $wpdb->prefix . "shivs_poll_votes_custom_fields`, `" . $wpdb->prefix . "shivs_poll_facebook_users`" );

            $poll_archive_page = get_page_by_path( 'shivs-poll-archive', ARRAY_A );

            if ( $poll_archive_page ) {
                $poll_archive_page_id = $poll_archive_page ['ID'];
                wp_delete_post( $poll_archive_page_id, true );
            }
        } // END foreach

        switch_to_blog( $old_blog );
        return;
    }

    delete_option( 'shivs_poll_version' );
    delete_option( 'shivs_poll_options' );
    delete_option( 'shivs_poll_first_install_date' );
    delete_option( 'shivs_poll_admin_notices_donate' );
    delete_option( 'shivs_poll_optin_box_modal_options' );
    delete_option( 'shivs_poll_pro_options' );

    $wpdb->query( "DROP TABLE `" . $wpdb->prefix . "shivs_pollmeta`, `" . $wpdb->prefix . "shivs_polls`, `" . $wpdb->prefix . "shivs_poll_answermeta`, `" . $wpdb->prefix . "shivs_poll_answers`, `" . $wpdb->prefix . "shivs_poll_custom_fields`, `" . $wpdb->prefix . "shivs_poll_logs`, `" . $wpdb->prefix . "shivs_poll_voters`, `" . $wpdb->prefix . "shivs_poll_bans`, `" . $wpdb->prefix . "shivs_poll_templates`, `" . $wpdb->prefix . "shivs_poll_votes_custom_fields`, `" . $wpdb->prefix . "shivs_poll_facebook_users`" );

    $poll_archive_page = get_page_by_path( 'shivs-poll-archive', ARRAY_A );

    if ( $poll_archive_page ) {
        $poll_archive_page_id = $poll_archive_page ['ID'];
        wp_delete_post( $poll_archive_page_id, true );
    }
} // END shivs_poll_uninstall

?>