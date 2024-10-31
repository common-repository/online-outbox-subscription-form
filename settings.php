<?php

function oosf_plugin_path( $path = '' ) {
	return path_join( OOSF_PLUGIN_DIR, trim( $path, '/' ) );
}

function oosf_plugin_url( $path = '' ) {

	return plugins_url( $path, OOSF_PLUGIN_BASENAME );
}

function oosf_admin_url( $query = array() ) {

	global $plugin_page;

	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );

}

function oosf_table_exists( $table = 'subscribeforms' ) {

	global $wpdb, $oosf;

	if ( 'subscribeforms' != $table )
		return false;

	if ( ! $table = $oosf->{$table} )
		return false;

	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) == strtolower( $table );
}

function oosf() {
	global $wpdb, $oosf;

	if ( is_object( $oosf ) )
		return;

	$oosf = (object) array(
		'subscribeforms' => $wpdb->prefix . "oo_subscribe_form",
		'processing_within' => '',
		'widget_count' => 0,
		'unit_count' => 0,
		'global_unit_count' => 0 );
}

oosf();

require_once OOSF_PLUGIN_DIR . '/includes/functions.php';
require_once OOSF_PLUGIN_DIR . '/includes/formatting.php';
require_once OOSF_PLUGIN_DIR . '/includes/pipe.php';
require_once OOSF_PLUGIN_DIR . '/includes/shortcodes.php';
require_once OOSF_PLUGIN_DIR . '/includes/classes.php';
require_once OOSF_PLUGIN_DIR . '/includes/taggenerator.php';

if ( is_admin() )
	require_once OOSF_PLUGIN_DIR . '/admin/admin.php';
else
	require_once OOSF_PLUGIN_DIR . '/includes/controller.php';

function oosf_subscribe_forms() {
	global $wpdb, $oosf;

	return $wpdb->get_results( "SELECT oosf_unit_id as id, title FROM $oosf->subscribeforms" );
}

add_action( 'plugins_loaded', 'oosf_set_request_uri', 9 );

function oosf_set_request_uri() {
	global $oosf_request_uri;

	$oosf_request_uri = add_query_arg( array() );
}

function oosf_get_request_uri() {
	global $oosf_request_uri;

	return (string) $oosf_request_uri;
}

/* Loading modules */

add_action( 'plugins_loaded', 'oosf_load_modules', 1 );

function oosf_load_modules() {
	$dir = OOSF_PLUGIN_MODULES_DIR;

	if ( ! ( is_dir( $dir ) && $dh = opendir( $dir ) ) )
		return false;

	while ( ( $module = readdir( $dh ) ) !== false ) {
		if ( substr( $module, -4 ) == '.php' )
			include_once $dir . '/' . $module;
	}
}

/* L10N */

// add_action( 'init', 'oosf_load_plugin_textdomain' );

function oosf_load_plugin_textdomain() {
	load_plugin_textdomain( 'oosf', false, 'online-outbox-subscription-form/languages' );
}
?>