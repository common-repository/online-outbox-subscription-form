<?php
/*
Plugin Name: Online Outbox Subscription Form
Plugin URI: http://www.onlineoutbox.com/
Description: The Online Outbox plugin allows you to easily add a subscription form to your WordPress site, to grow your list of email subscribers of your Email Newsletters, Mass Email Marketing messages, Email Autoresponders, Email Surveys, Optin Emails, Email Marketing Statistics.
Author: Your Design Online
Version: 1.5.3
Author URI: http://www.yourdesignonline.com/
*/
/* Copyright  2010 OnlineOutbox.com <info@yourdesignonline.com> */
/* Much thanks to <a href="http://wordpress.org/extend/plugins/profile/takayukister" target="_blank">Takayuki Miyoshi</a>, developer of <a href="http://wordpress.org/extend/plugins/contact-form-7/" target="_blank">Contact Form 7</a> plugin, which served as a foundation for futhered development. */

define( 'OOSF_VERSION', '1.5.3' );
define( 'OOSF_MOD_VERSION', '1.5.3' );

if ( ! defined( 'OOSF_PLUGIN_BASENAME' ) )
	define( 'OOSF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'OOSF_PLUGIN_NAME' ) )
	define( 'OOSF_PLUGIN_NAME', trim( dirname( OOSF_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'OOSF_PLUGIN_DIR' ) )
	define( 'OOSF_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . OOSF_PLUGIN_NAME );

if ( ! defined( 'OOSF_PLUGIN_URL' ) )
	define( 'OOSF_PLUGIN_URL', WP_PLUGIN_URL . '/' . OOSF_PLUGIN_NAME );

if ( ! defined( 'OOSF_PLUGIN_MODULES_DIR' ) )
	define( 'OOSF_PLUGIN_MODULES_DIR', OOSF_PLUGIN_DIR . '/modules' );

if ( ! defined( 'OOSF_LOAD_JS' ) )
	define( 'OOSF_LOAD_JS', true );

if ( ! defined( 'OOSF_LOAD_CSS' ) )
	define( 'OOSF_LOAD_CSS', true );

if ( ! defined( 'OOSF_AUTOP' ) )
	define( 'OOSF_AUTOP', true );

if ( ! defined( 'OOSF_USE_PIPE' ) )
	define( 'OOSF_USE_PIPE', true );

/* If you or your client hate to see about donation, set this value false. */
if ( ! defined( 'OOSF_SHOW_DONATION_LINK' ) )
	define( 'OOSF_SHOW_DONATION_LINK', false );

if ( ! defined( 'OOSF_ADMIN_READ_CAPABILITY' ) )
	define( 'OOSF_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'OOSF_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'OOSF_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );



global $oosf_options;
$oosf_options = get_option('oosf_options');

if(!get_option('oosf_options')) oosf_mrt_mkarry();

require_once OOSF_PLUGIN_DIR . '/settings.php';

function oosf_mrt_mkarry() {

	$oosf_options = array(
	'oosf_username'=> '',
	'oosf_password'=> '',
	'oosf_api_token' => '',
	'oosf_api_key_valid' => 0,
	);

	add_option('oosf_options',$oosf_options);

}


function oosf_activation_notice(){

	global $oosf_options;

	if(function_exists('admin_url')){

		echo '<div class="error fade" style="background-color:#FFFFCC; border-color:#FF9900"><p><strong>Online Outbox Subscribe Form must be configured. Go to <a href="' . admin_url( 'admin.php?page=online-outbox-subscribe-form/admin/admin-settings.php' ) . '">the admin page</a> to enable and configure the plugin.</strong></p></div>';

	} else {
		
		echo '<div class="error fade" style="background-color:red;"><p><strong>All in One SEO Pack must be configured. Go to <a href="' . get_option('siteurl') . 'admin.php?page=online-outbox-subscribe-form/admin/admin-settings.php' . '">the admin page</a> to enable and configure the plugin.</strong></p></div>';

	}

}

if( $oosf_options['oosf_api_key_valid']!='1' && $_SERVER['argv'][0] == 'page=online-outbox-subscribe-form/admin/admin-settings.php'){

	add_action( 'admin_notices', 'oosf_activation_notice');

}



?>