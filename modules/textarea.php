<?php
/**
** A base module for [textarea] and [textarea*]
**/

/* Shortcode handler */

oosf_add_shortcode( 'textarea', 'oosf_textarea_shortcode_handler', true );
oosf_add_shortcode( 'textarea*', 'oosf_textarea_shortcode_handler', true );

function oosf_textarea_shortcode_handler( $tag ) {
	if ( ! is_array( $tag ) )
		return '';

	$type = $tag['type'];
	$name = $tag['name'];
	$options = (array) $tag['options'];
	$values = (array) $tag['values'];
	$content = $tag['content'];

	if ( empty( $name ) )
		return '';

	$atts = '';
	$id_att = '';
	$class_att = '';
	$cols_att = '';
	$rows_att = '';
	$tabindex_att = '';
	$title_att = '';

	if ( 'textarea*' == $type )
		$class_att .= ' oosf-validates-as-required';

	foreach ( $options as $option ) {
		if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$id_att = $matches[1];

		} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];

		} elseif ( preg_match( '%^([0-9]*)[x/]([0-9]*)$%', $option, $matches ) ) {
			$cols_att = (int) $matches[1];
			$rows_att = (int) $matches[2];

		} elseif ( preg_match( '%^tabindex:(\d+)$%', $option, $matches ) ) {
			$tabindex_att = (int) $matches[1];

		}
	}

	$value = (string) reset( $values );

	if ( ! empty( $content ) )
		$value = $content;

	if ( oosf_script_is() && $value && preg_grep( '%^watermark$%', $options ) ) {
		$class_att .= ' oosf-use-title-as-watermark';
		$title_att .= sprintf( ' %s', $value );
		$value = '';
	}

	if ( oosf_is_posted() )
		$value = stripslashes_deep( $_POST[$name] );

	if ( $id_att )
		$atts .= ' id="' . trim( $id_att ) . '"';

	if ( $class_att )
		$atts .= ' class="' . trim( $class_att ) . '"';

	if ( $cols_att )
		$atts .= ' cols="' . $cols_att . '"';
	else
		$atts .= ' cols="40"'; // default size

	if ( $rows_att )
		$atts .= ' rows="' . $rows_att . '"';
	else
		$atts .= ' rows="10"'; // default size

	if ( '' !== $tabindex_att )
		$atts .= sprintf( ' tabindex="%d"', $tabindex_att );

	if ( $title_att )
		$atts .= sprintf( ' title="%s"', trim( esc_attr( $title_att ) ) );

	$html = '<textarea name="' . $name . '"' . $atts . '>' . esc_html( $value ) . '</textarea>';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_textarea', 'oosf_textarea_validation_filter', 10, 2 );
add_filter( 'oosf_validate_textarea*', 'oosf_textarea_validation_filter', 10, 2 );

function oosf_textarea_validation_filter( $result, $tag ) {
	global $oosf_subscribe_form;

	$type = $tag['type'];
	$name = $tag['name'];

	$_POST[$name] = (string) $_POST[$name];

	if ( 'textarea*' == $type ) {
		if ( '' == $_POST[$name] ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_textarea', 20 );

function oosf_add_tag_generator_textarea() {
	oosf_add_tag_generator( 'textarea', __( 'Text area', 'oosf' ),
		'oosf-tg-pane-textarea', 'oosf_tg_pane_textarea' );
}

function oosf_tg_pane_textarea( &$subscribe_form ) {
?>
<div id="oosf-tg-pane-textarea" class="hidden">
<form action="">
<table>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'oosf' ) ); ?></td></tr>
<tr><td><?php echo esc_html( __( 'Name', 'oosf' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><code>cols</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="cols" class="numeric oneline option" /></td>

<td><code>rows</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="rows" class="numeric oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Default value', 'oosf' ) ); ?> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br /><input type="text" name="values" class="oneline" /></td>

<td>
<br /><input type="checkbox" name="watermark" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as watermark?', 'oosf' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'oosf' ) ); ?><br /><input type="text" name="textarea" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'oosf' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>