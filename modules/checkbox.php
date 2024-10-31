<?php
/**
** A base module for [checkbox], [checkbox*], and [radio]
**/

/* Shortcode handler */

oosf_add_shortcode( 'checkbox', 'oosf_checkbox_shortcode_handler', true );
oosf_add_shortcode( 'checkbox*', 'oosf_checkbox_shortcode_handler', true );
oosf_add_shortcode( 'radio', 'oosf_checkbox_shortcode_handler', true );

function oosf_checkbox_shortcode_handler( $tag ) {
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

	$label_first = false;
	$use_label_element = false;

	if ( 'checkbox*' == $type )
		$class_att .= ' oosf-validates-as-required';

	if ( 'checkbox' == $type || 'checkbox*' == $type )
		$class_att .= ' oosf-checkbox';

	if ( 'radio' == $type )
		$class_att .= ' oosf-radio';

	foreach ( $options as $option ) {
		if ( preg_match( '%^id:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$id_att = $matches[1];

		} elseif ( preg_match( '%^class:([-0-9a-zA-Z_]+)$%', $option, $matches ) ) {
			$class_att .= ' ' . $matches[1];

		} elseif ( preg_match( '/^default:([0-9_]+)$/', $option, $matches ) ) {
			$defaults = explode( '_', $matches[1] );

		} elseif ( preg_match( '%^label[_-]?first$%', $option ) ) {
			$label_first = true;

		} elseif ( preg_match( '%^use[_-]?label[_-]?element$%', $option ) ) {
			$use_label_element = true;

		} elseif ( preg_match( '%^tabindex:(\d+)$%', $option, $matches ) ) {
			$tabindex_att = (int) $matches[1];

		}
	}

	$multiple = false;
	$exclusive = (bool) preg_grep( '%^exclusive$%', $options );

	if ( 'checkbox' == $type || 'checkbox*' == $type ) {
		$multiple = ! $exclusive;
	} else { // radio
		$exclusive = false;
	}

	if ( $exclusive )
		$class_att .= ' oosf-exclusive-checkbox';

	if ( $id_att )
		$atts .= ' id="' . trim( $id_att ) . '"';

	if ( $class_att )
		$atts .= ' class="' . trim( $class_att ) . '"';

	$html = '';

	$input_type = rtrim( $type, '*' );

	$posted = oosf_is_posted();

	foreach ( $values as $key => $value ) {
		$checked = false;

		if ( $posted ) {
			if ( $multiple && in_array( esc_sql( $value ), (array) $_POST[$name] ) )
				$checked = true;
			if ( ! $multiple && $_POST[$name] == esc_sql( $value ) )
				$checked = true;
		} else {
			if ( in_array( $key + 1, (array) $defaults ) )
				$checked = true;
		}

		$checked = $checked ? ' checked="checked"' : '';

		if ( '' !== $tabindex_att ) {
			$tabindex = sprintf( ' tabindex="%d"', $tabindex_att );
			$tabindex_att += 1;
		} else {
			$tabindex = '';
		}

		if ( isset( $labels[$key] ) )
			$label = $labels[$key];
		else
			$label = $value;

		if ( $label_first ) { // put label first, input last
			$item = '<span class="oosf-list-item-label">' . esc_html( $label ) . '</span>&nbsp;';
			$item .= '<input type="' . $input_type . '" name="' . $name . ( $multiple ? '[]' : '' ) . '" value="' . esc_attr( $value ) . '"' . $checked . $tabindex . ' />';
		} else {
			$item = '<input type="' . $input_type . '" name="' . $name . ( $multiple ? '[]' : '' ) . '" value="' . esc_attr( $value ) . '"' . $checked . $onclick . ' />';
			$item .= '&nbsp;<span class="oosf-list-item-label">' . esc_html( $label ) . '</span>';
		}

		if ( $use_label_element )
			$item = '<label>' . $item . '</label>';

		$item = '<span class="oosf-list-item">' . $item . '</span>';
		$html .= $item;
	}

	$html = '<span' . $atts . '>' . $html . '</span>';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_checkbox', 'oosf_checkbox_validation_filter', 10, 2 );
add_filter( 'oosf_validate_checkbox*', 'oosf_checkbox_validation_filter', 10, 2 );
add_filter( 'oosf_validate_radio', 'oosf_checkbox_validation_filter', 10, 2 );

function oosf_checkbox_validation_filter( $result, $tag ) {
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

	if ( 'checkbox*' == $type ) {
		if ( empty( $_POST[$name] ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = oosf_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_checkbox_and_radio', 30 );

function oosf_add_tag_generator_checkbox_and_radio() {
	oosf_add_tag_generator( 'checkbox', __( 'Checkboxes', 'oosf' ),
		'oosf-tg-pane-checkbox', 'oosf_tg_pane_checkbox' );

	oosf_add_tag_generator( 'radio', __( 'Radio buttons', 'oosf' ),
		'oosf-tg-pane-radio', 'oosf_tg_pane_radio' );
}

function oosf_tg_pane_checkbox( &$subscribe_form ) {
	oosf_tg_pane_checkbox_and_radio( 'checkbox' );
}

function oosf_tg_pane_radio( &$subscribe_form ) {
	oosf_tg_pane_checkbox_and_radio( 'radio' );
}

function oosf_tg_pane_checkbox_and_radio( $type = 'checkbox' ) {
	if ( 'radio' != $type )
		$type = 'checkbox';

?>
<div id="oosf-tg-pane-<?php echo $type; ?>" class="hidden">
<form action="">
<table>
<?php if ( 'checkbox' == $type ) : ?>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'oosf' ) ); ?></td></tr>
<?php endif; ?>

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
<br /><input type="checkbox" name="label_first" class="option" />&nbsp;<?php echo esc_html( __( 'Put a label first, a checkbox last?', 'oosf' ) ); ?>
<br /><input type="checkbox" name="use_label_element" class="option" />&nbsp;<?php echo esc_html( __( 'Wrap each item with <label> tag?', 'oosf' ) ); ?>
<?php if ( 'checkbox' == $type ) : ?>
<br /><input type="checkbox" name="exclusive" class="option" />&nbsp;<?php echo esc_html( __( 'Make checkboxes exclusive?', 'oosf' ) ); ?>
<?php endif; ?>
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