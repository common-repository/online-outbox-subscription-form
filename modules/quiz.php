<?php
/**
** A base module for [quiz]
**/

/* Shortcode handler */

oosf_add_shortcode( 'quiz', 'oosf_quiz_shortcode_handler', true );

function oosf_quiz_shortcode_handler( $tag ) {
	if ( ! is_array( $tag ) )
		return '';

	$type = $tag['type'];
	$name = $tag['name'];
	$options = (array) $tag['options'];
	$pipes = $tag['pipes'];

	if ( empty( $name ) )
		return '';

	$atts = '';
	$id_att = '';
	$class_att = '';
	$size_att = '';
	$maxlength_att = '';
	$tabindex_att = '';

	$class_att .= ' oosf-quiz';

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

	if ( is_a( $pipes, 'OOSF_Pipes' ) && ! $pipes->zero() ) {
		$pipe = $pipes->random_pipe();
		$question = $pipe->before;
		$answer = $pipe->after;
	} else {
		// default quiz
		$question = '1+1=?';
		$answer = '2';
	}

	$answer = oosf_canonicalize( $answer );

	$html = '<span class="oosf-quiz-label">' . esc_html( $question ) . '</span>&nbsp;';
	$html .= '<input type="text" name="' . $name . '"' . $atts . ' />';
	$html .= '<input type="hidden" name="_oosf_quiz_answer_' . $name . '" value="' . wp_hash( $answer, 'oosf_quiz' ) . '" />';

	$validation_error = oosf_get_validation_error( $name );

	$html = '<span class="oosf-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';

	return $html;
}


/* Validation filter */

add_filter( 'oosf_validate_quiz', 'oosf_quiz_validation_filter', 10, 2 );

function oosf_quiz_validation_filter( $result, $tag ) {
	$type = $tag['type'];
	$name = $tag['name'];

	$answer = oosf_canonicalize( $_POST[$name] );
	$answer_hash = wp_hash( $answer, 'oosf_quiz' );
	$expected_hash = $_POST['_oosf_quiz_answer_' . $name];
	if ( $answer_hash != $expected_hash ) {
		$result['valid'] = false;
		$result['reason'][$name] = oosf_get_message( 'quiz_answer_not_correct' );
	}

	return $result;
}


/* Ajax echo filter */

add_filter( 'oosf_ajax_onload', 'oosf_quiz_ajax_refill' );
add_filter( 'oosf_ajax_json_echo', 'oosf_quiz_ajax_echo_filter' );

function oosf_quiz_ajax_echo_filter( $items ) {
	if ( ! is_array( $items ) )
		return $items;

	$fes = oosf_scan_shortcode( array( 'type' => 'quiz' ) );

	if ( empty( $fes ) )
		return $items;

	$refill = array();

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$pipes = $fe['pipes'];

		if ( empty( $name ) )
			continue;

		if ( is_a( $pipes, 'OOSF_Pipes' ) && ! $pipes->zero() ) {
			$pipe = $pipes->random_pipe();
			$question = $pipe->before;
			$answer = $pipe->after;
		} else {
			// default quiz
			$question = '1+1=?';
			$answer = '2';
		}

		$answer = oosf_canonicalize( $answer );

		$refill[$name] = array( $question, wp_hash( $answer, 'oosf_quiz' ) );
	}

	if ( ! empty( $refill ) )
		$items['quiz'] = $refill;

	return $items;
}


/* Messages */

add_filter( 'oosf_messages', 'oosf_quiz_messages' );

function oosf_quiz_messages( $messages ) {
	return array_merge( $messages, array( 'quiz_answer_not_correct' => array(
		'description' => __( "Sender doesn't enter the correct answer to the quiz", 'oosf' ),
		'default' => __( 'Your answer is not correct.', 'oosf' )
	) ) );
}


/* Tag generator */

add_action( 'admin_init', 'oosf_add_tag_generator_quiz', 40 );

function oosf_add_tag_generator_quiz() {
	oosf_add_tag_generator( 'quiz', __( 'Quiz', 'oosf' ),
		'oosf-tg-pane-quiz', 'oosf_tg_pane_quiz' );
}

function oosf_tg_pane_quiz( &$subscribe_form ) {
?>
<div id="oosf-tg-pane-quiz" class="hidden">
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
<td><code>size</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="size" class="numeric oneline option" /></td>

<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'oosf' ) ); ?>)<br />
<input type="text" name="maxlength" class="numeric oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Quizzes', 'oosf' ) ); ?><br />
<textarea name="values"></textarea><br />
<span style="font-size: smaller"><?php echo esc_html( __( "* quiz|answer (e.g. 1+1=?|2)", 'oosf' ) ); ?></span>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'oosf' ) ); ?><br /><input type="text" name="quiz" class="tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>