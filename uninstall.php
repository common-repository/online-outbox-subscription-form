<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function oosf_delete_plugin() {
	global $wpdb;

	delete_option( 'oosf' );

	$table_name = $wpdb->prefix . "online-outbox-subscription-form";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

oosf_delete_plugin();

?>