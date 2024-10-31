<?php
/**
** A base module for [response]
**/

/* Shortcode handler */

oosf_add_shortcode( 'response', 'oosf_response_shortcode_handler' );

function oosf_response_shortcode_handler( $tag ) {
	if ( $subscribe_form = oosf_get_current_subscribe_form() ) {
		$subscribe_form->responses_count += 1;
		return $subscribe_form->form_response_output();
	}
}

?>