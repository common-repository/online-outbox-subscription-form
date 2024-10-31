<?php

function oosf_messages() {
	$messages = array(
		/*
		'mail_sent_ok' => array(
			'description' => __( "Sender's message was sent successfully", 'oosf' ),
			'default' => __( 'Your message was sent successfully. Thanks.', 'oosf' )
		),
		'mail_sent_ng' => array(
			'description' => __( "Sender's message was failed to send", 'oosf' ),
			'default' => __( 'Failed to send your message. Please try later or contact administrator by other way.', 'oosf' )
		),
		*/
		'subscription_add_ok' => array(
			'description' => __(  'Subscriber successfully added to list.', 'oosf'),
			'default' => __(  'You have successfully been added to our email list.', 'oosf'),
		),
		'subscription_add_ng' => array(
			'description' =>  __( 'Subscriber failed to be added to list.', 'oosf'),
			'default' => __(  'Failed too add you to our email list.', 'oosf'),
		),
		'akismet_says_spam' => array(
			'description' => __( "Akismet judged the sending activity as spamming", 'oosf' ),
			'default' => __( 'Failed to send your message. Please try later or contact administrator by other way.', 'oosf' )
		),
		'validation_error' => array(
			'description' => __( "Validation errors occurred", 'oosf' ),
			'default' => __( 'Validation errors occurred. Please confirm the fields and submit it again.', 'oosf' )
		),
		'accept_terms' => array(
			'description' => __( "There is a field of term that sender is needed to accept", 'oosf' ),
			'default' => __( 'Please accept the terms to proceed.', 'oosf' )
		),
		'invalid_email' => array(
			'description' => __( "Email address that sender entered is invalid", 'oosf' ),
			'default' => __( 'Email address seems invalid.', 'oosf' )
		),
		'invalid_required' => array(
			'description' => __( "There is a field that sender is needed to fill in", 'oosf' ),
			'default' => __( 'Please fill the required field.', 'oosf' )
		)
	);

	return apply_filters( 'oosf_messages', $messages );
}

function oosf_default_form_template() {
	$template =
		'<p>' . __( 'Your Name', 'oosf' ) . ' ' . __( '(required)', 'oosf' ) . '<br />' . "\n"
		. '    [text* your-name] </p>' . "\n\n"
		. '<p>' . __( 'Your Email', 'oosf' ) . ' ' . __( '(required)', 'oosf' ) . '<br />' . "\n"
		. '    [email* your-email] </p>' . "\n\n"
		// . '<p>' . __( 'Subject', 'oosf' ) . '<br />' . "\n"
		// . '    [text your-subject] </p>' . "\n\n"
		// . '<p>' . __( 'Your Message', 'oosf' ) . '<br />' . "\n"
		// . '    [textarea your-message] </p>' . "\n\n"
		. '<p>[submit "' . __( 'Send', 'oosf' ) . '"]</p>';

	return $template;
}

function oosf_default_mail_template() {
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = sprintf( __( 'From: %s', 'oosf' ), '[your-name] <[your-email]>' ) . "\n"
		// . sprintf( __( 'Subject: %s', 'oosf' ), '[your-subject]' ) . "\n\n"
		// . __( 'Message Body:', 'oosf' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This mail is sent via subscription form on %1$s %2$s', 'oosf' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = get_option( 'admin_email' );
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function oosf_default_mail_2_template() {
	$active = false;
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = __( 'Message body:', 'oosf' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This mail is sent via subscription form on %1$s %2$s', 'oosf' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = '[your-email]';
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'active', 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function oosf_default_messages_template() {
	$messages = array();

	foreach ( oosf_messages() as $key => $arr ) {
		$messages[$key] = $arr['default'];
	}

	return $messages;
}

function oosf_is_multisite() { // will be removed when WordPress 2.9 is not supported
	if ( function_exists( 'is_multisite' ) )
		return is_multisite();

	return false;
}

function oosf_is_main_site() { // will be removed when WordPress 2.9 is not supported
	if ( function_exists( 'is_main_site' ) )
		return is_main_site();

	return false;
}
function oosf_upload_dir( $type = false ) {
	global $switched;
	$siteurl = get_option( 'siteurl' );
	$upload_path = trim( get_option( 'upload_path' ) );
	$main_override = oosf_is_multisite() && defined( 'MULTISITE' ) && oosf_is_main_site();
	if ( empty( $upload_path ) ) {
		$dir = WP_CONTENT_DIR . '/uploads';
	} else {
		$dir = $upload_path;

		if ( 'wp-content/uploads' == $upload_path ) {
			$dir = WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $dir, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
			$dir = path_join( ABSPATH, $dir );
		}
	}

	if ( ! $url = get_option( 'upload_url_path' ) ) {
		if ( empty( $upload_path )
		|| ( 'wp-content/uploads' == $upload_path )
		|| ( $upload_path == $dir ) )
			$url = WP_CONTENT_URL . '/uploads';
		else
			$url = trailingslashit( $siteurl ) . $upload_path;
	}

	if ( defined( 'UPLOADS' ) && ! $main_override
	&& ( ! isset( $switched ) || $switched === false ) ) {
		$dir = ABSPATH . UPLOADS;
		$url = trailingslashit( $siteurl ) . UPLOADS;
	}

	if ( oosf_is_multisite() && ! $main_override
	&& ( ! isset( $switched ) || $switched === false ) ) {

		if ( defined( 'BLOGUPLOADDIR' ) )
			$dir = untrailingslashit( BLOGUPLOADDIR );

		$url = str_replace( UPLOADS, 'files', $url );
	}

	$uploads = apply_filters( 'oosf_upload_dir', array( 'dir' => $dir, 'url' => $url ) );

	if ( 'dir' == $type )
		return $uploads['dir'];
	if ( 'url' == $type )
		return $uploads['url'];

	return $uploads;
}

function oosf_l10n() {
	$l10n = array(
		'af' => __( 'Afrikaans', 'oosf' ),
		'sq' => __( 'Albanian', 'oosf' ),
		'ar' => __( 'Arabic', 'oosf' ),
		'bn_BD' => __( 'Bangla', 'oosf' ),
		'bs' => __( 'Bosnian', 'oosf' ),
		'pt_BR' => __( 'Brazilian Portuguese', 'oosf' ),
		'bg_BG' => __( 'Bulgarian', 'oosf' ),
		'ca' => __( 'Catalan', 'oosf' ),
		'zh_CN' => __( 'Chinese (Simplified)', 'oosf' ),
		'zh_TW' => __( 'Chinese (Traditional)', 'oosf' ),
		'hr' => __( 'Croatian', 'oosf' ),
		'cs_CZ' => __( 'Czech', 'oosf' ),
		'da_DK' => __( 'Danish', 'oosf' ),
		'nl_NL' => __( 'Dutch', 'oosf' ),
		'en_US' => __( 'English', 'oosf' ),
		'et' => __( 'Estonian', 'oosf' ),
		'fi' => __( 'Finnish', 'oosf' ),
		'fr_FR' => __( 'French', 'oosf' ),
		'gl_ES' => __( 'Galician', 'oosf' ),
		'ka_GE' => __( 'Georgian', 'oosf' ),
		'de_DE' => __( 'German', 'oosf' ),
		'el' => __( 'Greek', 'oosf' ),
		'he_IL' => __( 'Hebrew', 'oosf' ),
		'hi_IN' => __( 'Hindi', 'oosf' ),
		'hu_HU' => __( 'Hungarian', 'oosf' ),
		'id_ID' => __( 'Indonesian', 'oosf' ),
		'it_IT' => __( 'Italian', 'oosf' ),
		'ja' => __( 'Japanese', 'oosf' ),
		'ko_KR' => __( 'Korean', 'oosf' ),
		'lv' => __( 'Latvian', 'oosf' ),
		'lt_LT' => __( 'Lithuanian', 'oosf' ),
		'mk_MK' => __( 'Macedonian', 'oosf' ),
		'ms_MY' => __( 'Malay', 'oosf' ),
		'ml_IN' => __( 'Malayalam', 'oosf' ),
		'nb_NO' => __( 'Norwegian', 'oosf' ),
		'fa_IR' => __( 'Persian', 'oosf' ),
		'pl_PL' => __( 'Polish', 'oosf' ),
		'pt_PT' => __( 'Portuguese', 'oosf' ),
		'ru_RU' => __( 'Russian', 'oosf' ),
		'ro_RO' => __( 'Romanian', 'oosf' ),
		'sr_RS' => __( 'Serbian', 'oosf' ),
		'sk' => __( 'Slovak', 'oosf' ),
		'sl_SI' => __( 'Slovene', 'oosf' ),
		'es_ES' => __( 'Spanish', 'oosf' ),
		'sv_SE' => __( 'Swedish', 'oosf' ),
		'th' => __( 'Thai', 'oosf' ),
		'tr_TR' => __( 'Turkish', 'oosf' ),
		'uk' => __( 'Ukrainian', 'oosf' ),
		'vi' => __( 'Vietnamese', 'oosf' )
	);

	return $l10n;
}

?>