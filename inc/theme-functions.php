<?php

function return_shivs_poll( $id = -1, $tr_id = '', $offset = 0 ) {
	global $shivs_poll_public_admin;
	print $shivs_poll_public_admin->return_shivs_poll( $id, $tr_id, $offset );
}

function return_shivs_poll_archive( ) {
	global $shivs_poll_public_admin;
	print $shivs_poll_public_admin->shivs_poll_archive_shortcode_function( );
}

?>