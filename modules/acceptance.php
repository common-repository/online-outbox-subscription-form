<?php
/**
** A base module for [acceptance]
**/

/* Shortcode handler */

oosf_add_shortcode( 'acceptance', 'oosf_acceptance_shortcode_handler', true );

function oosf_acceptance_shortcode_handler( $tag ) {

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
	$tabindex_att = '';

	$class_att .= ' oosf-acceptance';

	foreach ( $options as $option ) {
		if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$id_att = $matches[1];

		} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];

		} elseif ( 'invert' == $option ) {
			$class_att .= ' oosf-invert';

		} elseif ( preg_match( '%^tabindex:(\d+)$%', $option, $matches ) ) {
			$tabindex_att = (int) $matches[1];

		}
	}

	if ( $id_att )
		$atts .= ' id="' . trim( $id_att ) . '"';

	if ( $class_att )
		$atts .= ' class="' . trim( $class_att ) . '"';

	if ( '' !== $tabindex_att )
		$atts .= sprintf( ' tabindex="%d"', $tabindex_att );

	$default_on = (bool) preg_grep( '/^default:on$/i', $options );

	$checked = $default_on ? ' checked="checked"' : '';

	$html = '<input type="checkbox" name="' . $name . '" value="1"' . $atts . $checked . ' />';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_acceptance', 'oosf_acceptance_validation_filter', 10, 2 );

function oosf_acceptance_validation_filter( $result, $tag ) {
	if ( ! oosf_acceptance_as_validation() )
		return $result;

	$name = $tag['name'];

	if ( empty( $name ) )
		return $result;

	$options = (array) $tag['options'];

	$value = $_POST[$name] ? 1 : 0;

	$invert = (bool) preg_grep( '%^invert$%', $options );

	if ( $invert && $value || ! $invert && ! $value ) {
		$result['valid'] = false;
		$result['reason'][$name] = oosf_get_message( 'accept_terms' );
	}

	return $result;
}


/* Acceptance filter */

add_filter( 'oosf_acceptance', 'oosf_acceptance_filter' );

function oosf_acceptance_filter( $accepted ) {
	$fes = oosf_scan_shortcode( array( 'type' => 'acceptance' ) );

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$options = (array) $fe['options'];

		if ( empty( $name ) )
			continue;

		$value = $_POST[$name] ? 1 : 0;

		$invert = (bool) preg_grep( '%^invert$%', $options );

		if ( $invert && $value || ! $invert && ! $value )
			$accepted = false;
	}

	return $accepted;
}

add_filter( 'oosf_form_class_attr', 'oosf_acceptance_form_class_attr' );

function oosf_acceptance_form_class_attr( $class ) {
	if ( oosf_acceptance_as_validation() )
		return $class . ' oosf-acceptance-as-validation';

	return $class;
}

function oosf_acceptance_as_validation() {
	if ( ! $subscribe_form = oosf_get_current_subscribe_form() )
		return false;

	$settings = $subscribe_form->additional_setting( 'acceptance_as_validation', false );

	foreach ( $settings as $setting ) {
		if ( in_array( $setting, array( 'on', 'true', '1' ) ) )
			return true;
	}

	return false;
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_acceptance', 35 );

function oosf_add_tag_generator_acceptance() {
	oosf_add_tag_generator( 'acceptance', __( 'Acceptance', 'oosf' ),
		'oosf-tg-pane-acceptance', 'oosf_tg_pane_acceptance' );
}

function oosf_tg_pane_acceptance( &$subscribe_form ) {
?>
<div id="oosf-tg-pane-acceptance" class="hidden">
<form action="">
<table>
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
<td colspan="2">
<br /><input type="checkbox" name="default:on" class="option" />&nbsp;<?php echo esc_html( __( "Make this checkbox checked by default?", 'oosf' ) ); ?>
<br /><input type="checkbox" name="invert" class="option" />&nbsp;<?php echo esc_html( __( "Make this checkbox work inversely?", 'oosf' ) ); ?>
<br /><span style="font-size: smaller;"><?php echo esc_html( __( "* That means visitor who accepts the term unchecks it.", 'oosf' ) ); ?></span>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'oosf' ) ); ?><br /><input type="text" name="acceptance" class="tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>