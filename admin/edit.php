<?php



/* No table warning */
if ( ! oosf_table_exists() ) {
	if ( current_user_can( 'activate_plugins' ) ) {
		$create_table_link_url = oosf_admin_url( array( 'oosf-create-table' => 1 ) );
		$create_table_link_url = wp_nonce_url( $create_table_link_url, 'oosf-create-table' );
		$message = sprintf(
			__( '<strong>The database table for Online Outbox Subscription Form does not exist.</strong> You must <a href="%s">create the table</a> for it to work.', 'oosf' ),
			$create_table_link_url );
	} else {
		$message = __( "<strong>The database table for Online Outbox Subscription does not exist.</strong>", 'oosf' );
	}
?>
	<div class="wrap">
	<?php screen_icon( 'edit-pages' ); ?>
	<h2><?php echo esc_html( __( 'Online Outbox Subscription Form', 'oosf' ) ); ?></h2>
	<div id="message" class="updated fade">
	<p><?php echo $message; ?></p>
	</div>
	</div>
<?php
	return;
}

?><div class="wrap oosf">

<?php screen_icon( 'edit-pages' ); ?>

<h2><?php echo esc_html( __( 'Online Outbox Subscription Form', 'oosf' ) ); ?></h2>

<?php do_action_ref_array( 'oosf_admin_before_subsubsub', array( &$sf ) ); ?>

<?php if ( isset( $updated_message ) ) : ?>
<div id="message" class="updated fade"><p><?php echo $updated_message; ?></p></div>
<?php endif; ?>
	

<ul class="subsubsub">
<?php
$first = array_shift( $subscribe_forms );
if ( ! is_null( $first ) ) : ?>
<li><a href="<?php echo oosf_admin_url( array( 'subscribeform' => $first->id ) ); ?>"<?php if ( $first->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $first->title ); ?></a></li>
<?php endif;
foreach ( $subscribe_forms as $v ) : ?>
<li>| <a href="<?php echo oosf_admin_url( array( 'subscribeform' => $v->id ) ); ?>"<?php if ( $v->id == $current ) echo ' class="current"'; ?>><?php echo esc_html( $v->title ); ?></a></li>
<?php endforeach; ?>

<?php if ( oosf_admin_has_edit_cap() ) : ?>
<li class="addnew"><a class="thickbox<?php if ( $unsaved ) echo ' current'; ?>" href="#TB_inline?height=300&width=400&inlineId=oosf-lang-select-modal"><?php echo esc_html( __( 'Add new', 'oosf' ) ); ?></a></li>
<?php endif; ?>
</ul>

<br class="clear" />

<?php if ( $sf ) : ?>
<?php $disabled = ( oosf_admin_has_edit_cap() ) ? '' : ' disabled="disabled"'; ?>

<form method="post" action="<?php echo oosf_admin_url( array( 'subscribeform' => $current ) ); ?>" id="oosf-admin-form-element">
	<?php if ( oosf_admin_has_edit_cap() ) wp_nonce_field( 'oosf-save_' . $current ); ?>
	<input type="hidden" id="oosf-id" name="oosf-id" value="<?php echo $current; ?>" />

	<table class="widefat">
	<tbody>
	<tr>
	<td scope="col">
	<div style="position: relative;">
		<input type="text" id="oosf-title" name="oosf-title" size="40" value="<?php echo esc_attr( $sf->title ); ?>"<?php echo $disabled; ?> />

		<?php if ( ! $unsaved ) : ?>
		<p class="tagcode">
			<?php echo esc_html( __( "Copy this code and paste it into your post, page or text widget content.", 'oosf' ) ); ?><br />

			<input type="text" id="subscribe-form-anchor-text" onfocus="this.select();" readonly="readonly" />
		</p>
		<?php endif; ?>

		<?php if ( oosf_admin_has_edit_cap() ) : ?>
		<div class="save-subscribe-form">
			<input type="submit" class="button button-highlighted" name="oosf-save" value="<?php echo esc_attr( __( 'Save', 'oosf' ) ); ?>" />
		</div>
		<?php endif; ?>

		<?php if ( oosf_admin_has_edit_cap() && ! $unsaved ) : ?>
		<div class="actions-link">
			<?php $copy_nonce = wp_create_nonce( 'oosf-copy_' . $current ); ?>
			<input type="submit" name="oosf-copy" class="copy" value="<?php echo esc_attr( __( 'Copy', 'oosf' ) ); ?>"
			<?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; return true;\""; ?> />
			|

			<?php $delete_nonce = wp_create_nonce( 'oosf-delete_' . $current ); ?>
			<input type="submit" name="oosf-delete" class="delete" value="<?php echo esc_attr( __( 'Delete', 'oosf' ) ); ?>"
			<?php echo "onclick=\"if (confirm('" .
				esc_js( __( "You are about to delete this subscription form.\n  'Cancel' to stop, 'OK' to delete.", 'oosf' ) ) .
				"')) {this.form._wpnonce.value = '$delete_nonce'; return true;} return false;\""; ?> />
		</div>
		<?php endif; ?>
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php do_action_ref_array( 'oosf_admin_after_general_settings', array( &$sf ) ); ?>

<?php if ( oosf_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2"><?php echo esc_html( __( 'Form', 'oosf' ) ); ?></th></tr></thead>

	<tbody>
	<tr>

	<td scope="col" style="width: 50%;">
	<div><textarea id="oosf-form" name="oosf-form" cols="100" rows="20"><?php echo esc_html( $sf->form ); ?></textarea></div>
	</td>

	<td scope="col" style="width: 50%;">
		<div id="taggenerator"></div>
	<div style="width:250px; margin-top:10px">


		<p>Select which of your Online Outbox lists users submitting this form will be added to:</p>

		<select name="oosf-list-id" id="oosf-list-id" onchange="show_visible_fields(this.value)">
			<? foreach($oo_user_lists as $list_id => $list_array): ?>
				<option value="<?= $list_id ?>" <?= $sf->list_id == $list_id ? 'selected="selected"' : '' ?>><?= $list_array['list_name'] ?></option>
			<? endforeach; ?>
		</select>

		<? foreach($oo_user_lists as $list_id => $list_array): ?>
		<div id="visible-fields-list-id-<?= $list_id ?>" class="visible-fields">
			<p>The following are available fields from list '<strong><?= $list_array['list_name'] ?></strong>'. You <strong>must</strong> name the fields in the form these fields for the data to be submitted correctly.</p>
			
			<p><strong>Main Fields</strong></p>

			<? foreach($list_array['visiblefields'] as $field_name): ?>
				<?= $field_name ?><br />
			<? endforeach; ?>
				
			<? if($list_array['custom_fields']): ?>
			

				<br /><p><strong>Custom Fields</strong></p>
				
				<p>Note: For custom fields, the field name must be the id of the custom field instead of the field name as compared to the main fields.</p>

				<table border="0" cellspacing="0">
				<tr>
					<th>Custom Field Id</th>
					<th>Custom Field Name</th>
				</tr>
				<? foreach($list_array['custom_fields'] as $custom_field): ?>
				<tr>
					<td><?= $custom_field['fieldid'] ?></td>
					<td><?= $custom_field['name'] ?></td>
				</tr>
				<? endforeach; ?>
				</table>
				<br />
			<? endif; ?>

		</div>
		<? endforeach; ?>
	
		<script type="text/javascript">
		/* <![CDATA[ */

		<? foreach($oo_user_lists as $list_id => $list_array): ?>

			<? if($list_array['visiblefields']): ?>

				var list_<?= $list_id ?>_visible_fields = {
					<? $i = 1; foreach($list_array['visiblefields'] as $field_name): ?>
					<?= $i ?>: '<?= $field_name ?>'<?= $i < count($list_array['visiblefields']) ? ',' : '' ?>
				<? $i++; endforeach; ?>
				
					};

			<? endif; ?>

			<? if($list_array['custom_fields']): ?>

				var list_<?= $list_id ?>_custom_fields = {
					<? $i = 1; foreach($list_array['custom_fields'] as $custom_field): ?>
					<?= $custom_field['fieldid'] ?>: '<?= $custom_field['name'] ?>'<?= $i < count($list_array['custom_fields']) ? ',' : '' ?>
				<? $i++; endforeach; ?>
				
					};

			<? endif; ?>
		
		<? endforeach; ?>

			function load_custom_fields() {

				var selected_list = jQuery('#oosf-list-id').val();
				
				visible_field_array_name = eval("list_" + selected_list + "_visible_fields");
				custom_field_array_name = eval("list_" + selected_list + "_custom_fields");

				jQuery.each(visible_field_array_name, function(key, value) {
					jQuery('#oo_field_gen_select').append("<option value='" + value + "'>" + value + '<\/option>');
				});

				jQuery.each(custom_field_array_name, function(key, value) {
					jQuery('#oo_field_gen_select').append("<option value='" + key + "'>" + value + " (ID: " + key + ')<\/option>');
				});

			}



				function show_visible_fields(list_id) {

					if(list_id) {

						jQuery('div.visible-fields').hide();
						jQuery('div#visible-fields-list-id-' + list_id).show();
						
						load_custom_fields();

					}

				}

				show_visible_fields( jQuery('#oosf-list-id').val() );

		/* ]]> */
		</script>

	</div>
	</td>

	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'oosf_admin_after_form', array( &$sf ) ); ?>

<?php if ( oosf_admin_has_edit_cap() && false) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2"><?php echo esc_html( __( 'Mail', 'oosf' ) ); ?></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col" style="width: 50%;">

	<div class="mail-field">
	<label for="oosf-mail-recipient"><?php echo esc_html( __( 'To:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-recipient" name="oosf-mail-recipient" class="wide" size="70" value="<?php echo esc_attr( $sf->mail['recipient'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="oosf-mail-sender"><?php echo esc_html( __( 'From:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-sender" name="oosf-mail-sender" class="wide" size="70" value="<?php echo esc_attr( $sf->mail['sender'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="oosf-mail-subject"><?php echo esc_html( __( 'Subject:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-subject" name="oosf-mail-subject" class="wide" size="70" value="<?php echo esc_attr( $sf->mail['subject'] ); ?>" />
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<label for="oosf-mail-additional-headers"><?php echo esc_html( __( 'Additional headers:', 'oosf' ) ); ?></label><br />
	<textarea id="oosf-mail-additional-headers" name="oosf-mail-additional-headers" cols="100" rows="2"><?php echo esc_html( $sf->mail['additional_headers'] ); ?></textarea>
	</div>

	<div class="mail-field">
	<label for="oosf-mail-attachments"><?php echo esc_html( __( 'File attachments:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-attachments" name="oosf-mail-attachments" class="wide" size="70" value="<?php echo esc_attr( $sf->mail['attachments'] ); ?>" />
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<input type="checkbox" id="oosf-mail-use-html" name="oosf-mail-use-html" value="1"<?php echo ( $sf->mail['use_html'] ) ? ' checked="checked"' : ''; ?> />
	<label for="oosf-mail-use-html"><?php echo esc_html( __( 'Use HTML content type', 'oosf' ) ); ?></label>
	</div>

	</td>
	<td scope="col" style="width: 50%;">

	<div class="mail-field">
	<label for="oosf-mail-body"><?php echo esc_html( __( 'Message body:', 'oosf' ) ); ?></label><br />
	<textarea id="oosf-mail-body" name="oosf-mail-body" cols="100" rows="16"><?php echo esc_html( $sf->mail['body'] ); ?></textarea>
	</div>

	</td>
	</tr>
	</tbody>
	</table>

	<?php do_action_ref_array( 'oosf_admin_after_mail', array( &$sf ) ); ?>

<?php endif; ?>


<?php if ( oosf_admin_has_edit_cap() && false ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col" colspan="2"><?php echo esc_html( __( 'Mail (2)', 'oosf' ) ); ?></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col" colspan="2">
	<input type="checkbox" id="oosf-mail-2-active" name="oosf-mail-2-active" value="1"<?php echo ( $sf->mail_2['active'] ) ? ' checked="checked"' : ''; ?> />
	<label for="oosf-mail-2-active"><?php echo esc_html( __( 'Use mail (2)', 'oosf' ) ); ?></label>
	</td>
	</tr>

	<tr id="mail-2-fields">
	<td scope="col" style="width: 50%;">

	<div class="mail-field">
	<label for="oosf-mail-2-recipient"><?php echo esc_html( __( 'To:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-2-recipient" name="oosf-mail-2-recipient" class="wide" size="70" value="<?php echo esc_attr( $sf->mail_2['recipient'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="oosf-mail-2-sender"><?php echo esc_html( __( 'From:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-2-sender" name="oosf-mail-2-sender" class="wide" size="70" value="<?php echo esc_attr( $sf->mail_2['sender'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="oosf-mail-2-subject"><?php echo esc_html( __( 'Subject:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-2-subject" name="oosf-mail-2-subject" class="wide" size="70" value="<?php echo esc_attr( $sf->mail_2['subject'] ); ?>" />
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<label for="oosf-mail-2-additional-headers"><?php echo esc_html( __( 'Additional headers:', 'oosf' ) ); ?></label><br />
	<textarea id="oosf-mail-2-additional-headers" name="oosf-mail-2-additional-headers" cols="100" rows="2"><?php echo esc_html( $sf->mail_2['additional_headers'] ); ?></textarea>
	</div>

	<div class="mail-field">
	<label for="oosf-mail-2-attachments"><?php echo esc_html( __( 'File attachments:', 'oosf' ) ); ?></label><br />
	<input type="text" id="oosf-mail-2-attachments" name="oosf-mail-2-attachments" class="wide" size="70" value="<?php echo esc_attr( $sf->mail_2['attachments'] ); ?>" />
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<input type="checkbox" id="oosf-mail-2-use-html" name="oosf-mail-2-use-html" value="1"<?php echo ( $sf->mail_2['use_html'] ) ? ' checked="checked"' : ''; ?> />
	<label for="oosf-mail-2-use-html"><?php echo esc_html( __( 'Use HTML content type', 'oosf' ) ); ?></label>
	</div>

	</td>
	<td scope="col" style="width: 50%;">

	<div class="mail-field">
	<label for="oosf-mail-2-body"><?php echo esc_html( __( 'Message body:', 'oosf' ) ); ?></label><br />
	<textarea id="oosf-mail-2-body" name="oosf-mail-2-body" cols="100" rows="16"><?php echo esc_html( $sf->mail_2['body'] ); ?></textarea>
	</div>

	</td>
	</tr>
	</tbody>
	</table>
	
	<?php do_action_ref_array( 'oosf_admin_after_mail_2', array( &$sf ) ); ?>

<?php endif; ?>


<?php if ( oosf_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col"><?php echo esc_html( __( 'Messages', 'oosf' ) ); ?> <span id="message-fields-toggle-switch"></span></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col">
	<div id="message-fields">

<?php foreach ( oosf_messages() as $key => $arr ) :
	$field_name = 'oosf-message-' . strtr( $key, '_', '-' );
?>
	<div class="message-field">
	<label for="<?php echo $field_name; ?>"><em># <?php echo esc_html( $arr['description'] ); ?></em></label><br />
	<input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="wide" size="70" value="<?php echo esc_attr( $sf->messages[$key] ); ?>" />
	</div>

<?php endforeach; ?>

	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'oosf_admin_after_messages', array( &$sf ) ); ?>

<?php if ( oosf_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<thead><tr><th scope="col"><?php echo esc_html( __( 'Additional Settings', 'oosf' ) ); ?> <span id="additional-settings-fields-toggle-switch"></span></th></tr></thead>

	<tbody>
	<tr>
	<td scope="col">
	<div id="additional-settings-fields">
	<textarea id="oosf-additional-settings" name="oosf-additional-settings" cols="100" rows="8"><?php echo esc_html( $sf->additional_settings ); ?></textarea>
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

<?php do_action_ref_array( 'oosf_admin_after_additional_settings', array( &$sf ) ); ?>

<?php if ( oosf_admin_has_edit_cap() ) : ?>

	<table class="widefat" style="margin-top: 1em;">
	<tbody>
	<tr>
	<td scope="col">
	<div class="save-subscribe-form">
	<input type="submit" class="button button-highlighted" name="oosf-save" value="<?php echo esc_attr( __( 'Save', 'oosf' ) ); ?>" />
	</div>
	</td>
	</tr>
	</tbody>
	</table>

<?php endif; ?>

</form>

<?php endif; ?>

</div>

<div id="oosf-lang-select-modal" class="hidden">
<?php
	$available_locales = oosf_l10n();
	$default_locale = get_locale();

	if ( ! isset( $available_locales[$default_locale] ) )
		$default_locale = 'en_US';

?>
<h4><?php echo esc_html( sprintf( __( 'Use the default language (%s)', 'oosf' ), $available_locales[$default_locale] ) ); ?></h4>
<p><a href="<?php echo oosf_admin_url( array( 'subscribeform' => 'new' ) ); ?>" class="button" /><?php echo esc_html( __( 'Add New', 'oosf' ) ); ?></a></p>

<?php unset( $available_locales[$default_locale] ); ?>
<h4><?php echo esc_html( __( 'Or', 'oosf' ) ); ?></h4>
<form action="" method="get">
<input type="hidden" name="page" value="oosf" />
<input type="hidden" name="subscribeform" value="new" />
<select name="locale">
<option value="" selected="selected"><?php echo esc_html( __( '(select language)', 'oosf' ) ); ?></option>
<?php foreach ( $available_locales as $code => $locale ) : ?>
<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $locale ); ?></option>
<?php endforeach; ?>
</select>
<input type="submit" class="button" value="<?php echo esc_attr( __( 'Add New', 'oosf' ) ); ?>" />
</form>
</div>

<?php do_action_ref_array( 'oosf_admin_footer', array( &$sf ) ); ?>
