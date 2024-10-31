<?php


function oosf_admin_has_edit_cap() {
	return current_user_can( OOSF_ADMIN_READ_WRITE_CAPABILITY );
}

add_action( 'admin_menu', 'oosf_admin_add_pages', 9 );

function oosf_admin_add_pages() {

	if ( isset( $_POST['oosf-save'] ) && oosf_admin_has_edit_cap() ) {
		$id = $_POST['oosf-id'];
		check_admin_referer( 'oosf-save_' . $id );

		if ( ! $subscribe_form = oosf_subscribe_form( $id ) ) {
			$subscribe_form = new OOSF_SubscribeForm();
			$subscribe_form->initial = true;
		}

		$title = trim( $_POST['oosf-title'] );
		$form = trim( $_POST['oosf-form'] );

		$mail = array(
			'subject' => trim( $_POST['oosf-mail-subject'] ),
			'sender' => trim( $_POST['oosf-mail-sender'] ),
			'body' => trim( $_POST['oosf-mail-body'] ),
			'recipient' => trim( $_POST['oosf-mail-recipient'] ),
			'additional_headers' => trim( $_POST['oosf-mail-additional-headers'] ),
			'attachments' => trim( $_POST['oosf-mail-attachments'] ),
			'use_html' =>
				isset( $_POST['oosf-mail-use-html'] ) && 1 == $_POST['oosf-mail-use-html']
		);
		$mail_2 = array(
			'active' =>
				isset( $_POST['oosf-mail-2-active'] ) && 1 == $_POST['oosf-mail-2-active'],
			'subject' => trim( $_POST['oosf-mail-2-subject'] ),
			'sender' => trim( $_POST['oosf-mail-2-sender'] ),
			'body' => trim( $_POST['oosf-mail-2-body'] ),
			'recipient' => trim( $_POST['oosf-mail-2-recipient'] ),
			'additional_headers' => trim( $_POST['oosf-mail-2-additional-headers'] ),
			'attachments' => trim( $_POST['oosf-mail-2-attachments'] ),
			'use_html' =>
				isset( $_POST['oosf-mail-2-use-html'] ) && 1 == $_POST['oosf-mail-2-use-html']
		);

		$messages = $subscribe_form->messages;
		foreach ( oosf_messages() as $key => $arr ) {
			$field_name = 'oosf-message-' . strtr( $key, '_', '-' );
			if ( isset( $_POST[$field_name] ) )
				$messages[$key] = trim( $_POST[$field_name] );
		}

		$additional_settings = trim( $_POST['oosf-additional-settings'] );

		$query = array();
		$query['message'] = ( $subscribe_form->initial ) ? 'created' : 'saved';

		$subscribe_form->title = $title;
		$subscribe_form->form = $form;
		$subscribe_form->mail = $mail;
		$subscribe_form->mail_2 = $mail_2;
		$subscribe_form->messages = $messages;
		$subscribe_form->additional_settings = $additional_settings;
		$subscribe_form->list_id = $_POST['oosf-list-id'];
		
		$subscribe_form->save();

		$query['subscribeform'] = $subscribe_form->id;
		$redirect_to = oosf_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_POST['oosf-copy'] ) && oosf_admin_has_edit_cap() ) {
		$id = $_POST['oosf-id'];
		check_admin_referer( 'oosf-copy_' . $id );

		$query = array();

		if ( $subscribe_form = oosf_subscribe_form( $id ) ) {
			$new_subscribe_form = $subscribe_form->copy();
			$new_subscribe_form->save();

			$query['subscribeform'] = $new_subscribe_form->id;
			$query['message'] = 'created';
		} else {
			$query['subscribeform'] = $subscribe_form->id;
		}

		$redirect_to = oosf_admin_url( $query );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_POST['oosf-delete'] ) && oosf_admin_has_edit_cap() ) {
		$id = $_POST['oosf-id'];
		check_admin_referer( 'oosf-delete_' . $id );

		if ( $subscribe_form = oosf_subscribe_form( $id ) )
			$subscribe_form->delete();

		$redirect_to = oosf_admin_url( array( 'message' => 'deleted' ) );
		wp_redirect( $redirect_to );
		exit();
	} elseif ( isset( $_GET['oosf-create-table'] ) ) {
		check_admin_referer( 'oosf-create-table' );

		$query = array();

		if ( ! oosf_table_exists() && current_user_can( 'activate_plugins' ) ) {
			oosf_install();
			if ( oosf_table_exists() ) {
				$query['message'] = 'table_created';
			} else {
				$query['message'] = 'table_not_created';
			}
		}

		wp_redirect( oosf_admin_url( $query ) );
		exit();
	}

	add_menu_page( __( 'Online Outbox Subscribe Form', 'oosf' ), __( 'Online Outbox', 'oosf' ),
		OOSF_ADMIN_READ_CAPABILITY, 'oosf', 'oosf_admin_management_page' );
		
		
	add_submenu_page( 'oosf', __( 'Edit Subscription Forms', 'oosf' ), __( 'Edit', 'oosf' ),
		OOSF_ADMIN_READ_CAPABILITY, 'oosf', 'oosf_admin_management_page' );
		
		
	add_submenu_page( 'oosf', __( 'Settings', 'oosf' ), __( 'Settings', 'oosf' ),
		OOSF_ADMIN_READ_CAPABILITY, 'oosf-settings', 'oosf_settings_management_page' );
		
}


add_action( 'admin_print_styles', 'oosf_admin_enqueue_styles' );

function oosf_admin_enqueue_styles() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'oosf' != $plugin_page )
		return;

	wp_enqueue_style( 'thickbox' );

	wp_enqueue_style( 'online-outbox-subscription-form-admin', oosf_plugin_url( 'admin/styles.css' ),
		array(), OOSF_VERSION, 'all' );

	if ( 'rtl' == get_bloginfo( 'text_direction' ) ) {
		wp_enqueue_style( 'online-outbox-subscription-form-admin-rtl',
			oosf_plugin_url( 'admin/styles-rtl.css' ), array(), OOSF_VERSION, 'all' );
	}
}

add_action( 'admin_print_scripts', 'oosf_admin_enqueue_scripts' );

function oosf_admin_enqueue_scripts() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'oosf' != $plugin_page )
		return;

	wp_enqueue_script( 'thickbox' );

	wp_enqueue_script( 'oosf-admin-taggenerator', oosf_plugin_url( 'admin/taggenerator.js' ),
		array( 'jquery' ), OOSF_VERSION, true );

	wp_enqueue_script( 'oosf-admin', oosf_plugin_url( 'admin/scripts.js' ),
		array( 'jquery', 'oosf-admin-taggenerator' ), OOSF_VERSION, true );
	wp_localize_script( 'oosf-admin', '_oosfL10n', array(
		'generateTag' => __( 'Generate Tag', 'oosf' ),
		'show' => __( "Show", 'oosf' ),
		'hide' => __( "Hide", 'oosf' ) ) );
}

add_action( 'admin_footer', 'oosf_admin_footer' );

function oosf_admin_footer() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || 'oosf' != $plugin_page )
		return;

?>
<script type="text/javascript">
/* <![CDATA[ */
var _oosf = {
	pluginUrl: '<?php echo oosf_plugin_url(); ?>',
	tagGenerators: {
<?php oosf_print_tag_generators(); ?>
	}
};
/* ]]> */
</script>
<?php
}

function oosf_admin_management_page() {
	global $oosf_options; $oo_user_lists;

	$subscribe_forms = oosf_subscribe_forms();

	$unsaved = false;

	if ( ! isset( $_GET['subscribeform'] ) )
		$_GET['subscribeform'] = '';

	if ( 'new' == $_GET['subscribeform'] ) {
		$unsaved = true;
		$current = -1;
		$sf = oosf_subscribe_form_default_pack( isset( $_GET['locale'] ) ? $_GET['locale'] : '' );
	} elseif ( $sf = oosf_subscribe_form( $_GET['subscribeform'] ) ) {
		$current = (int) $_GET['subscribeform'];
	} else {
		$first = reset( $subscribe_forms ); // Returns first item
		$current = $first->id;
		$sf = oosf_subscribe_form( $current );
	}

	
	if($oosf_options['oosf_api_key_valid'] == '1') {

		require(OOSF_PLUGIN_DIR . '/onlineoutbox_api.class.php');

		$oo_api = new OO_Api($oosf_options['oosf_username'], $oosf_options['oosf_api_token']);

		// valid oosf user

		// get user's oo subscribe forms

		$oo_lists = $oo_api->getLists();


		if($oo_lists !== FALSE) {


			foreach($oo_lists as $item) { 

				// get custom fields

				$list_custom_fields = $oo_api->GetCustomFields( array('list_id' => $item['listid']) );

				if($list_custom_fields !== FALSE) {

					foreach($list_custom_fields as $custom_field) {

						$custom_fields[] = $custom_field;
						$custom_field_ids[] = $custom_field['fieldid'];
							
					}

				}

				$exclude_fields_array = array(
					'subscribedate',
					'format',
					'status',
					'confirmed'
				);

				// unset some of the necessary fields
				$temp_visiblefields = explode(',', $item['visiblefields']);

				foreach($temp_visiblefields as $field) {
				
					if(!in_array($field, $exclude_fields_array)) { 
						
						if(is_numeric($field)) { 
							if(in_array($field, $custom_field_ids)) { unset($field); }
						}

						if(isset($field)) { $visiblefields[] = $field; }
						
					}

						
				}

				$oo_user_lists[$item['listid']] = array(
					'list_name' => $item['name'],
					'visiblefields' => $visiblefields,
					'custom_field_ids' => $custom_field_ids,
					'custom_fields' => $custom_fields,
				); 
				
				unset($custom_field_ids);
				unset($visible_fields);
				unset($temp_visiblefields);
				unset($custom_fields);
				unset($visiblefields);

			} // end foreach

		}

	}

	if(($oosf_options['oosf_api_key_valid'] == '1' && isset($oo_lists) && !empty($subscribe_forms)) || $_GET['subscribeform'] == 'new') {

		require_once OOSF_PLUGIN_DIR . '/admin/edit.php';

	} elseif($oosf_options['oosf_api_key_valid'] == '1' && !isset($oo_user_lists)) {

		$error_message = 'We have successfully connected to your Online Outbox account but you have no active contact lists. You must add a contact list to your account to configure any subscription forms.';

	} elseif($oosf_options['oosf_api_key_valid'] == '1' && empty($subscribe_forms)) {

		echo '<p>You have not configured any forms. Click <a href="'
		 . bloginfo('home') . 'admin.php?subscribeform=new&page=oosf">here</a> to configure your first one.</p>';

	} else {

		$error_message = 'You must configure Online Outbox before you can add subscribe forms. Click <a href="admin.php?page=oosf-settings">here</a> to go to the settings page to enable the Online Outbox subscription plugin.';


	}
	
	if(isset($error_message)) { require_once OOSF_PLUGIN_DIR . '/admin/admin-panel-error.php'; }
}


function oosf_settings_management_page() {
	require_once OOSF_PLUGIN_DIR . '/admin/admin-settings.php';
}



/* Install and default settings */

add_action( 'activate_' . OOSF_PLUGIN_BASENAME, 'oosf_install' );

function oosf_install() {
	global $wpdb, $oosf;

	if ( oosf_table_exists() )
		return; // Exists already

	$charset_collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}

	$wpdb->query( "CREATE TABLE IF NOT EXISTS $oosf->subscribeforms (
		oosf_unit_id bigint(20) unsigned NOT NULL auto_increment,
		title varchar(200) NOT NULL default '',
		form text NOT NULL,
		list_id int(6) NULL,
		mail text NOT NULL,
		mail_2 text NOT NULL,
		messages text NOT NULL,
		additional_settings text NOT NULL,
		PRIMARY KEY (oosf_unit_id)) $charset_collate;" );

	if ( ! oosf_table_exists() )
		return false; // Failed to create

	$legacy_data = get_option( 'oosf' );
	if ( is_array( $legacy_data )
		&& is_array( $legacy_data['subscribe_forms'] ) && $legacy_data['subscribe_forms'] ) {
		foreach ( $legacy_data['subscribe_forms'] as $key => $value ) {
			$wpdb->insert( $oosf->subscribeforms, array(
				'oosf_unit_id' => $key,
				'title' => $value['title'],
				// 'form' => maybe_serialize( $value['form'] ),
				'form' => maybe_serialize( oosf_default_form_template() ),
				'mail' => maybe_serialize( $value['mail'] ),
				'mail_2' => maybe_serialize( $value['mail_2'] ),
				'messages' => maybe_serialize( $value['messages'] ),
				'additional_settings' => maybe_serialize( $value['additional_settings'] )
				), array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' ) );
		}
	} else {
		oosf_load_plugin_textdomain();

		$wpdb->insert( $oosf->subscribeforms, array(
			'title' => __( 'Subscription form', 'oosf' ) . ' 1',
			'form' => maybe_serialize( oosf_default_form_template() ),
			'mail' => maybe_serialize( oosf_default_mail_template() ),
			'mail_2' => maybe_serialize ( oosf_default_mail_2_template() ),
			'messages' => maybe_serialize( oosf_default_messages_template() ) ) );
	}
}

/* Misc */

add_filter( 'plugin_action_links', 'oosf_plugin_action_links', 10, 2 );

function oosf_plugin_action_links( $links, $file ) {
	if ( $file != OOSF_PLUGIN_BASENAME )
		return $links;

	$url = oosf_admin_url( array( 'page' => 'oosf' ) );

	$settings_link = '<a href="' . esc_attr( $url ) . '">'
		. esc_html( __( 'Settings', 'oosf' ) ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_action( 'oosf_admin_before_subsubsub', 'oosf_updated_message' );

function oosf_updated_message( &$subscribe_form ) {
	if ( ! isset( $_GET['message'] ) )
		return;

	switch ( $_GET['message'] ) {
		case 'created':
			$updated_message = __( "Subscription form created.", 'oosf' );
			break;
		case 'saved':
			$updated_message = __( "Subscription form saved.", 'oosf' );
			break;
		case 'deleted':
			$updated_message = __( "Subscription form deleted.", 'oosf' );
			break;
		case 'table_created':
			$updated_message = __( "Database table created.", 'oosf' );
			break;
		case 'table_not_created':
			$updated_message = __( "Failed to create database table.", 'oosf' );
			break;
	}

	if ( ! $updated_message )
		return;

?>
<div id="message" class="updated fade"><p><?php echo esc_html( $updated_message ); ?></p></div>
<?php
}
