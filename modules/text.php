<?php
/**
** A base module for [text], [text*], [email], and [email*]
**/

/* Shortcode handler */

oosf_add_shortcode( 'text', 'oosf_text_shortcode_handler', true );
oosf_add_shortcode( 'text*', 'oosf_text_shortcode_handler', true );
oosf_add_shortcode( 'email', 'oosf_text_shortcode_handler', true );
oosf_add_shortcode( 'email*', 'oosf_text_shortcode_handler', true );

function oosf_text_shortcode_handler( $tag ) {
	if ( ! is_array( $tag ) )
		return '';

	$type = $tag['type'];
	$name = $tag['name'];
	$options = (array) $tag['options'];
	$values = (array) $tag['values'];

	if ( empty( $name ) )
		return '';

	$atts = '';
	$id_att = '';
	$class_att = '';
	$size_att = '';
	$maxlength_att = '';
	$tabindex_att = '';
	$title_att = '';

	$class_att .= ' oosf-text';

	if ( 'email' == $type || 'email*' == $type )
		$class_att .= ' oosf-validates-as-email';

	if ( 'text*' == $type || 'email*' == $type )
		$class_att .= ' oosf-validates-as-required';

	foreach ( $options as $option ) {
		if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$id_att = $matches[1];

		} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];

		} elseif ( preg_match( '%^([0-9]*)[/x]([0-9]*)$%', $option, $matches ) ) {
			$size_att = (int) $matches[1];
			$maxlength_att = (int) $matches[2];

		} elseif ( preg_match( '%^tabindex:(\d+)$%', $option, $matches ) ) {
			$tabindex_att = (int) $matches[1];

		}
	}

	$value = (string) reset( $values );

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

	if ( $size_att )
		$atts .= ' size="' . $size_att . '"';
	else
		$atts .= ' size="40"'; // default size

	if ( $maxlength_att )
		$atts .= ' maxlength="' . $maxlength_att . '"';

	if ( '' !== $tabindex_att )
		$atts .= sprintf( ' tabindex="%d"', $tabindex_att );

	if ( $title_att )
		$atts .= sprintf( ' title="%s"', trim( esc_attr( $title_att ) ) );

	$html = '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '"' . $atts . ' />';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_text', 'oosf_text_validation_filter', 10, 2 );
add_filter( 'oosf_validate_text*', 'oosf_text_validation_filter', 10, 2 );
add_filter( 'oosf_validate_email', 'oosf_text_validation_filter', 10, 2 );
add_filter( 'oosf_validate_email*', 'oosf_text_validation_filter', 10, 2 );

function oosf_text_validation_filter( $result, $tag ) {
	$type = $tag['type'];
	$name = $tag['name'];

	$_POST[$name] = trim( strtr( (string) $_POST[$name], "\n", " " ) );

	if ( 'text*' == $type ) {
		if ( '' == $_POST[$name] ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_required' );
		}
	}

	if ( 'email' == $type || 'email*' == $type ) {
		if ( 'email*' == $type && '' == $_POST[$name] ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_required' );
		} elseif ( '' != $_POST[$name] && ! is_email( $_POST[$name] ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_email' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_text_and_email', 15 );

function oosf_add_tag_generator_text_and_email() {
	oosf_add_tag_generator( 'text', __( 'Text field', 'oosf' ),
		'oosf-tg-pane-text', 'oosf_tg_pane_text' );

	oosf_add_tag_generator( 'email', __( 'Email field', 'oosf' ),
		'oosf-tg-pane-email', 'oosf_tg_pane_email' );
}

function oosf_tg_pane_text( &$subscribe_form ) {
	oosf_tg_pane_text_and_email( 'text' );
}

function oosf_tg_pane_email( &$subscribe_form ) {
	oosf_tg_pane_text_and_email( 'email' );
}

function oosf_tg_pane_text_and_email( $type = 'text' ) {
	if ( 'email' != $type )
		$type = 'text';

?>
<div id="oosf-tg-pane-<?php echo $type; ?>" class="hidden">
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
<td><code>size</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="size" class="numeric oneline option" /></td>

<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="maxlength" class="numeric oneline option" /></td>
</tr>

<tr>
<td colspan="2"><?php echo esc_html( __( 'Akismet', 'oosf' ) ); ?> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<?php if ( 'text' == $type ) : ?>
<input type="checkbox" name="akismet:author" class="exclusive option" />&nbsp;<?php echo esc_html( __( "This field requires author's name", 'oosf' ) ); ?><br />
<input type="checkbox" name="akismet:author_url" class="exclusive option" />&nbsp;<?php echo esc_html( __( "This field requires author's URL", 'oosf' ) ); ?>
<?php else : ?>
<input type="checkbox" name="akismet:author_email" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's email address", 'oosf' ) ); ?>
<?php endif; ?>
</td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Default value', 'oosf' ) ); ?> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br /><input type="text" name="values" class="oneline" /></td>

<td>
<br /><input type="checkbox" name="watermark" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as watermark?', 'oosf' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'oosf' ) ); ?><br /><input type="text" name="<?php echo $type; ?>" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'oosf' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>