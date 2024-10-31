<?php

add_action( 'init', 'oosf_init_switch', 11 );

require( OOSF_PLUGIN_DIR . '/onlineoutbox_api.class.php');

$oo_api = new OO_Api();

function oosf_init_switch() {
	if ( 'GET' == $_SERVER['REQUEST_METHOD'] && isset( $_GET['_oosf_is_ajax_call'] ) ) {
		oosf_ajax_onload();
		exit();
	} elseif ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['_oosf_is_ajax_call'] ) ) {
		oosf_ajax_json_echo();
		exit();
	} elseif ( isset( $_POST['_oosf'] ) ) {
		oosf_process_nonajax_submitting();
	}
}

function oosf_ajax_onload() {
	global $oosf_subscribe_form;

	$echo = '';

	if ( isset( $_GET['_oosf'] ) ) {
		$id = (int) $_GET['_oosf'];

		if ( $oosf_subscribe_form = oosf_subscribe_form( $id ) ) {
			$items = apply_filters( 'oosf_ajax_onload', array() );
			$oosf_subscribe_form = null;
		}
	}

	$echo = json_encode( $items );

	if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	}
}

function oosf_ajax_json_echo() {
	global $oosf_subscribe_form, $oo_api;

	$echo = '';

	if ( isset( $_POST['_oosf'] ) ) {
		$id = (int) $_POST['_oosf'];
		$unit_tag = $_POST['_oosf_unit_tag'];

		if ( $oosf_subscribe_form = oosf_subscribe_form( $id ) ) {
			$validation = $oosf_subscribe_form->validate();

			$items = array(
				'mailSent' => false,
				'into' => '#' . $unit_tag,
				'captcha' => null );

			$items = apply_filters( 'oosf_ajax_json_echo', $items );

			if ( ! $validation['valid'] ) { // Validation error occured
				$invalids = array();
				foreach ( $validation['reason'] as $name => $reason ) {
					$invalids[] = array(
						'into' => 'span.oosf-form-control-wrap.' . $name,
						'message' => $reason );
				}

				$items['message'] = oosf_get_message( 'validation_error' );
				$items['invalids'] = $invalids;

			} elseif ( ! $oosf_subscribe_form->accepted() ) { // Not accepted terms
				$items['message'] = oosf_get_message( 'accept_terms' );

			} elseif ( $oosf_subscribe_form->akismet() ) { // Spam!
				$items['message'] = oosf_get_message( 'akismet_says_spam' );
				$items['spam'] = true;

			// } elseif ( $oosf_subscribe_form->mail() ) {
			} elseif ( $oo_api->AddSubscriberToList() ) {

				$items['mailSent'] = true;
				$items['message'] = oosf_get_message( 'subscription_add_ok' );
				$on_sent_ok = $oosf_subscribe_form->additional_setting( 'on_sent_ok', false );

				if ( ! empty( $on_sent_ok ) ) {
					$on_sent_ok = array_map( 'oosf_strip_quote', $on_sent_ok );
				} else {
					$on_sent_ok = null;
				}
				$items['onSentOk'] = $on_sent_ok;

				do_action_ref_array( 'oosf_subscriber_added', array( &$oosf_subscribe_form ) );

			} else {
				$items['message'] = oosf_get_message( 'subscription_add_ng' );
			}

			// remove upload files
			foreach ( (array) $oosf_subscribe_form->uploaded_files as $name => $path ) {
				@unlink( $path );
			}

			$oosf_subscribe_form = null;
		}
	}

	$echo = json_encode( $items );

	if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	} else {
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		echo '<textarea>' . $echo . '</textarea>';
	}
}

function oosf_process_nonajax_submitting() {
	global $oosf_subscribe_form, $oosf_options, $oo_api;

	if ( ! isset($_POST['_oosf'] ) )
		return;

	$id = (int) $_POST['_oosf'];

	if ( $oosf_subscribe_form = oosf_subscribe_form( $id ) ) {
		$validation = $oosf_subscribe_form->validate();

		if ( ! $validation['valid'] ) {
			$_POST['_oosf_validation_errors'] = array( 'id' => $id, 'messages' => $validation['reason'] );
		} elseif ( ! $oosf_subscribe_form->accepted() ) { // Not accepted terms
			$_POST['_oosf_mail_sent'] = array( 'id' => $id, 'ok' => false, 'message' => oosf_get_message( 'accept_terms' ) );
		} elseif ( $oosf_subscribe_form->akismet() ) { // Spam!
			$_POST['_oosf_mail_sent'] = array( 'id' => $id, 'ok' => false, 'message' => oosf_get_message( 'akismet_says_spam' ), 'spam' => true );
		// } elseif ( $oosf_subscribe_form->mail() ) {
		} elseif ( $oo_api->AddSubscriberToList() ) {
			
			$_POST['_oosf_subscriber_added'] = array( 'id' => $id, 'ok' => true, 'message' => oosf_get_message( 'subscription_add_ok' ) );

			do_action_ref_array( 'oosf_mail_sent', array( &$oosf_subscribe_form ) );

			$oosf_subscribe_form->clear_post();
		} else {
			$_POST['_oosf_subscriber_added'] = array( 'id' => $id, 'ok' => false, 'message' => oosf_get_message( 'subscription_add_ng' ) );
		}

		// remove upload files
		foreach ( (array) $oosf_subscribe_form->uploaded_files as $name => $path ) {
			@unlink( $path );
		}

		$oosf_subscribe_form = null;
	}
}

add_action( 'the_post', 'oosf_the_post' );

function oosf_the_post() {
	global $oosf;

	$oosf->processing_within = 'p' . get_the_ID();
	$oosf->unit_count = 0;
}

add_action( 'loop_end', 'oosf_loop_end' );

function oosf_loop_end() {
	global $oosf;

	$oosf->processing_within = '';
}

add_filter( 'widget_text', 'oosf_widget_text_filter', 9 );

function oosf_widget_text_filter( $content ) {
	global $oosf;

	$oosf_widget_count += 1;
	$oosf_processing_within = 'w' . $oosf->widget_count;
	$oosf_unit_count = 0;

	$regex = '/\[\s*oo-subscribe-form\s+(\d+(?:\s+.*)?)\]/';
	$content = preg_replace_callback( $regex, 'oosf_widget_text_filter_callback', $content );

	$oosf->processing_within = '';
	return $content;
}

function oosf_widget_text_filter_callback( $matches ) {
	return do_shortcode( $matches[0] );
}

add_shortcode( 'oo-subscribe-form', 'oosf_subscribe_form_tag_func' );

function oosf_subscribe_form_tag_func( $atts ) {
	global $oosf, $oosf_subscribe_form;

	if ( is_feed() )
		return '[oo-subscribe-form]';

	if ( is_string( $atts ) )
		$atts = explode( ' ', $atts, 2 );

	$atts = (array) $atts;

	$id = (int) array_shift( $atts );

	if ( ! ( $oosf_subscribe_form = oosf_subscribe_form( $id ) ) )
		return '[oo-subscribe-form 404 "Not Found"]';

	if ( $oosf->processing_within ) { // Inside post content or text widget
		$oosf->unit_count += 1;
		$unit_count = $oosf->unit_count;
		$processing_within = $oosf->processing_within;

	} else { // Inside template

		if ( ! isset( $oosf->global_unit_count ) )
			$oosf->global_unit_count = 0;

		$oosf->global_unit_count += 1;
		$unit_count = 1;
		$processing_within = 't' . $oosf->global_unit_count;
	}

	$unit_tag = 'oosf-f' . $id . '-' . $oosf_processing_within . '-o' . $oosf_unit_count;
	$oosf_subscribe_form->unit_tag = $unit_tag;

	$form = $oosf_subscribe_form->form_html();

	$oosf_subscribe_form = null;

	return $form;
}

add_action( 'wp_head', 'oosf_head' );

function oosf_head() {
	// Cached?
	if ( oosf_script_is() && defined( 'WP_CACHE' ) && WP_CACHE ) :
?>
<script type="text/javascript">
//<![CDATA[
var _oosf = { cached: 1 };
//]]>
</script>
<?php
	endif;
}

if ( OOSF_LOAD_JS )
	add_action( 'wp_print_scripts', 'oosf_enqueue_scripts' );

function oosf_enqueue_scripts() {
	// jquery.form.js originally bundled with WordPress is out of date and deprecated
	// so we need to deregister it and re-register the latest one
	wp_deregister_script( 'jquery-form' );
	wp_register_script( 'jquery-form', oosf_plugin_url( 'jquery.form.js' ),
		array( 'jquery' ), '2.47', true );

	$in_footer = true;
	if ( 'header' === OOSF_LOAD_JS )
		$in_footer = false;

	wp_enqueue_script( 'online-outbox-subscription-form', oosf_plugin_url( 'scripts.js' ),
		array('jquery', 'jquery-form'), OOSF_VERSION, $in_footer );
	do_action( 'oosf_enqueue_scripts' );
}

function oosf_script_is() {
	return wp_script_is( 'online-outbox-subscription-form' );
}

if ( OOSF_LOAD_CSS )
	add_action( 'wp_print_styles', 'oosf_enqueue_styles' );

function oosf_enqueue_styles() {
	wp_enqueue_style( 'online-outbox-subscription-form', oosf_plugin_url( 'styles.css' ),
		array(), OOSF_VERSION, 'all' );

	if ( 'rtl' == get_bloginfo( 'text_direction' ) ) {
		wp_enqueue_style( 'online-outbox-subscription-form-rtl', oosf_plugin_url( 'styles-rtl.css' ),
			array(), OOSF_VERSION, 'all' );
	}
	do_action( 'oosf_enqueue_styles' );
}

function oosf_style_is() {
	return wp_style_is( 'online-outbox-subscription-form' );
}

?>