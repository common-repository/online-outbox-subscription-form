<?php
/**
** Note: This ICL module is obsolete and no longer functioning on this version.
** There is a simpler way for creating contact forms of other languages.
**/

/* Shortcode handler */

oosf_add_shortcode( 'icl', 'icl_oosf_shortcode_handler', true );

function icl_oosf_shortcode_handler( $tag ) {

	if ( ! is_array( $tag ) )
		return '';

	$name = $tag['name'];
	$values = (array) $tag['values'];
	$content = $tag['content'];

	// Just return the content.

	$content = trim( $content );
	if ( ! empty( $content ) )
		return $content;

	$value = trim( $values[0] );
	if ( ! empty( $value ) )
		return $value;

	return '';
}


/* Message dispaly filter */

add_filter( 'oosf_display_message', 'icl_oosf_display_message_filter' );

function icl_oosf_display_message_filter( $message ) {
	$shortcode_manager = new OOSF_ShortcodeManager();
	$shortcode_manager->add_shortcode( 'icl', 'icl_oosf_shortcode_handler', true );

	return $shortcode_manager->do_shortcode( $message );
}


/* Warning message */

add_action( 'oosf_admin_before_subsubsub', 'icl_oosf_display_warning_message' );

function icl_oosf_display_warning_message( &$subscribe_form ) {
	if ( ! $subscribe_form )
		return;

	$has_icl_tags = (bool) $subscribe_form->form_scan_shortcode(
		array( 'type' => array( 'icl' ) ) );

	if ( ! $has_icl_tags ) {
		$messages = (array) $subscribe_form->messages;

	$shortcode_manager = new OOSF_ShortcodeManager();
	$shortcode_manager->add_shortcode( 'icl', create_function( '$tag', 'return null;' ), true );

		foreach ( $messages as $message ) {
			if ( $shortcode_manager->scan_shortcode( $message ) ) {
				$has_icl_tags = true;
				break;
			}
		}
	}

	if ( ! $has_icl_tags )
		return;

	$message = __( "This subscribe form contains [icl] tags, but they are obsolete and no longer functioning on this version of Online Outbox Subscription Form. <a href=\"http://contactform7.com/2009/12/25/contact-form-in-your-language/#Creating_contact_form_in_different_languages\" target=\"_blank\">There is a simpler way for creating contact forms of other languages</a> and you are recommended to use it.", 'oosf' );

	echo '<div class="error"><p><strong>' . $message . '</strong></p></div>';
}

?>