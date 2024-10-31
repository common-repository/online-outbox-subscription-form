<?php

function oosf_add_tag_generator( $name, $title, $elm_id, $callback, $options = array() ) {
	global $oosf_tag_generators;

	$name = trim( $name );
	if ( '' == $name )
		return false;

	if ( ! is_array( $oosf_tag_generators ) )
		$oosf_tag_generators = array();

	$oosf_tag_generators[$name] = array(
		'title' => $title,
		'content' => $elm_id,
		'options' => $options );

	if ( is_callable( $callback ) )
		add_action( 'oosf_admin_footer', $callback );

	return true;
}

function oosf_print_tag_generators() {
	global $oosf_tag_generators;

	$output = array();

	foreach ( (array) $oosf_tag_generators as $name => $tg ) {
		$pane = "		" . esc_js( $name ) . ": { ";
		$pane .= "title: '" . esc_js( $tg['title'] ) . "'";
		$pane .= ", content: '" . esc_js( $tg['content'] ) . "'";

		foreach ( (array) $tg['options'] as $option_name => $option_value ) {
			if ( is_int( $option_value ) )
				$pane .= ", $option_name: $option_value";
			else
				$pane .= ", $option_name: '" . esc_js( $option_value ) . "'";
		}

		$pane .= " }";

		$output[] = $pane;
	}

	echo implode( ",\n", $output ) . "\n";
}

?>