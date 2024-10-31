<?php
/**
** A base module for [select] and [select*]
**/

/* Shortcode handler */

oosf_add_shortcode( 'select', 'oosf_select_shortcode_handler', true );
oosf_add_shortcode( 'select*', 'oosf_select_shortcode_handler', true );

function oosf_select_shortcode_handler( $tag ) {
	if ( ! is_array( $tag ) )
		return '';

	$type = $tag['type'];
	$name = $tag['name'];
	$options = (array) $tag['options'];
	$values = (array) $tag['values'];
	$labels = (array) $tag['labels'];

	if ( empty( $name ) )
		return '';

	$atts = '';
	$id_att = '';
	$class_att = '';
	$tabindex_att = '';

	$defaults = array();

	$class_att .= ' oosf-select';

	if ( 'select*' == $type )
		$class_att .= ' oosf-validates-as-required';

	foreach ( $options as $option ) {
		if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$id_att = $matches[1];

		} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];

		} elseif ( preg_match( '/^default:([0-9_]+)$/', $option, $matches ) ) {
			$defaults = explode( '_', $matches[1] );

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

	$multiple = (bool) preg_grep( '%^multiple$%', $options );
	$include_blank = (bool) preg_grep( '%^include_blank$%', $options );

	$empty_select = empty( $values );
	if ( $empty_select || $include_blank ) {
		array_unshift( $labels, '---' );
		array_unshift( $values, '---' );
	}

	$html = '';

	$posted = oosf_is_posted();

	foreach ( $values as $key => $value ) {
		$selected = false;

		if ( $posted ) {
			if ( $multiple && in_array( esc_sql( $value ), (array) $_POST[$name] ) )
				$selected = true;
			if ( ! $multiple && $_POST[$name] == esc_sql( $value ) )
				$selected = true;
			if ( ! $empty_select && in_array( $key + 1, (array) $defaults ) )
				$selected = true;
		}

		$selected = $selected ? ' selected="selected"' : '';

		if ( isset( $labels[$key] ) )
			$label = $labels[$key];
		else
			$label = $value;

		$html .= '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $label ) . '</option>';
	}

	if ( $multiple )
		$atts .= ' multiple="multiple"';

	$html = '<select name="' . $name . ( $multiple ? '[]' : '' ) . '"' . $atts . '>' . $html . '</select>';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_select', 'oosf_select_validation_filter', 10, 2 );
add_filter( 'oosf_validate_select*', 'oosf_select_validation_filter', 10, 2 );

function oosf_select_validation_filter( $result, $tag ) {
	$type = $tag['type'];
	$name = $tag['name'];
	$values = $tag['values'];

	if ( is_array( $_POST[$name] ) ) {
		foreach ( $_POST[$name] as $key => $value ) {
			$value = stripslashes( $value );
			if ( ! in_array( $value, (array) $values ) ) // Not in given choices.
				unset( $_POST[$name][$key] );
		}
	} else {
		$value = stripslashes( $_POST[$name] );
		if ( ! in_array( $value, (array) $values ) ) //  Not in given choices.
			$_POST[$name] = '';
	}

	if ( 'select*' == $type ) {
		if ( empty( $_POST[$name] ) ||
			! is_array( $_POST[$name] ) && '---' == $_POST[$name] ||
			is_array( $_POST[$name] ) && 1 == count( $_POST[$name] ) && '---' == $_POST[$name][0] ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_menu', 25 );

function oosf_add_tag_generator_menu() {
	oosf_add_tag_generator( 'menu', __( 'Drop-down menu', 'oosf' ),
		'oosf-tg-pane-menu', 'oosf_tg_pane_menu' );
}

function oosf_tg_pane_menu( &$subscribe_form ) {
?>
<div id="oosf-tg-pane-menu" class="hidden">
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
<td><?php echo esc_html( __( 'Choices', 'oosf' ) ); ?><br />
<textarea name="values"></textarea><br />
<span style="font-size: smaller"><?php echo esc_html( __( "* One choice per line.", 'oosf' ) ); ?></span>
</td>

<td>
<br /><input type="checkbox" name="multiple" class="option" />&nbsp;<?php echo esc_html( __( 'Allow multiple selections?', 'oosf' ) ); ?>
<br /><input type="checkbox" name="include_blank" class="option" />&nbsp;<?php echo esc_html( __( 'Insert a blank item as the first option?', 'oosf' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'oosf' ) ); ?><br /><input type="text" name="select" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'oosf' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>