<?php

/**
 * Update poll meta field based on poll ID
 * @param  int    $poll_id    [Poll ID]
 * @param  string $meta_key   [Meta key]
 * @param  mixed  $meta_value [Meta value]
 * @param  string $prev_value [Parameter used to differentiate same key on meta fields and poll ID]
 * @return bool               [False on failure]
 */
function update_shivs_poll_meta( $poll_id, $meta_key, $meta_value, $prev_value = '' ) {
    return update_metadata( 'shivs_poll', $poll_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Add poll data field to a poll
 * @param int     $poll_id    [Poll ID]
 * @param string  $meta_key   [Meta key]
 * @param mixed   $meta_value [Meta value]
 * @param boolean $unique     [Default is false]
 * @return bool               [False on failure]
 */
function add_shivs_poll_meta( $poll_id, $meta_key, $meta_value, $unique = false ) {
    return add_metadata( 'shivs_poll', $poll_id, $meta_key, $meta_value, $unique );
}

function delete_shivs_poll_meta( $poll_id, $meta_key, $meta_value = '' ) {
    return delete_metadata( 'shivs_poll', $poll_id, $meta_key, $meta_value );
}

function get_shivs_poll_meta( $poll_id, $key = '', $single = false ) {
    return get_metadata( 'shivs_poll', $poll_id, $key, $single );
}

function delete_shivs_poll_meta_by_key( $poll_meta_key ) {
    return delete_metadata( 'shivs_poll', NULL, $poll_meta_key, '', true );
}

function update_shivs_poll_answer_meta( $poll_answer_id, $meta_key, $meta_value, $prev_value = '' ) {
    return update_metadata( 'shivs_poll_answer', $poll_answer_id, $meta_key, $meta_value, $prev_value );
}

function add_shivs_poll_answer_meta( $poll_answer_id, $meta_key, $meta_value, $unique = false ) {
    return add_metadata( 'shivs_poll_answer', $poll_answer_id, $meta_key, $meta_value, $unique );
}

function delete_shivs_poll_answer_meta( $poll_answer_id, $meta_key, $meta_value = '' ) {
    return delete_metadata( 'shivs_poll_answer', $poll_answer_id, $meta_key, $meta_value );
}

function get_shivs_poll_answer_meta( $poll_answer_id, $key = '', $single = false ) {
    return get_metadata( 'shivs_poll_answer', $poll_answer_id, $key, $single );
}

function delete_shivs_poll_answer_meta_by_key( $poll_answer_meta_key ) {
    return delete_metadata( 'shivs_poll_answer', NULL, $poll_answer_meta_key, '', true );
}

function get_human_time( $date ) {
    $t_time = get_the_time( __( 'Y/m/d g:i:s A' ) );
    $m_time = $date;
    $time   = get_post_time( 'G', true, $post );

    $time_diff = time() - $time;

    if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ){
        $h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
    }
    else {
        $h_time = mysql2date( __( 'Y/m/d' ), $m_time );
    }
}

function shivs_poll_set_html_content_type() {
    return 'text/html';
}

function shivs_poll_dump( $str ) {
    print "<pre>";
    print_r( $str );
    print "</pre>";
}

function shivs_poll_kses( $string ) {
    $pt = array(
        'a'   => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array()
        ),
        'img' => array(
            'src'   => array(),
            'title' => array(),
            'style' => array()
        ),
        'br'  => array()
    );
    return wp_kses( $string, $pt );
}

?>