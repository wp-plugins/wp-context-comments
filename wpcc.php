<?php

/*

Plugin Name: WP Context Comments
Version: 0.2.3
Plugin URI: https://github.com/thgie/wpcc
Description: A plug-in to attach a comment to inline text - Medium style.
Author: Adrian Demleitner
Author URI: http://thgie.ch
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

*/

// le setting

add_action( 'admin_menu', 'wpcc_add_admin_menu' );
add_action( 'admin_init', 'wpcc_settings_init' );


function wpcc_add_admin_menu(  ) {

	add_options_page( 'WPCC', 'WPCC', 'manage_options', 'wpcc', 'wpcc_options_page' );

}


function wpcc_settings_init(  ) {

	register_setting( 'pluginPage', 'wpcc_settings' );

	add_settings_section(
		'wpcc_pluginPage_section',
		__( 'My relationships with my cats has saved me from a deadly, pervasive ignorance. -  William S. Burroughs', 'wordpress' ),
		'wpcc_settings_section_callback',
		'pluginPage'
	);

	add_settings_field(
		'wpcc_css_selectors',
		__( 'CSS Selectors', 'wordpress' ),
		'wpcc_css_selectors_render',
		'pluginPage',
		'wpcc_pluginPage_section'
	);

	add_settings_field(
		'wpcc_custom_css',
		__( 'Custom CSS', 'wordpress' ),
		'wpcc_custom_css_render',
		'pluginPage',
		'wpcc_pluginPage_section'
	);

	// add_settings_field(
	// 	'wpcc_anon_commenting',
	// 	__( 'Allow Anonymous Commenting', 'wordpress' ),
	// 	'wpcc_anon_commenting_render',
	// 	'pluginPage',
	// 	'wpcc_pluginPage_section'
	// );

}


function wpcc_css_selectors_render(  ) {

	$options = get_option( 'wpcc_settings' );
	?>
	<input type='text' name='wpcc_settings[wpcc_css_selectors]' value='<?php echo $options['wpcc_css_selectors']; ?>'>
	<?php

}

function wpcc_custom_css_render(  ) {

	$options = get_option( 'wpcc_settings' );
	?>
	<textarea cols='40' rows='5' name='wpcc_settings[wpcc_custom_css]'><?php echo $options['wpcc_custom_css']; ?></textarea>
	<?php

}


function wpcc_anon_commenting_render(  ) {

	$options = get_option( 'wpcc_settings' );
	?>
	<input type='checkbox' name='wpcc_settings[wpcc_anon_commenting]' <?php checked( $options['wpcc_anon_commenting'], 1 ); ?> value='1'>
	<?php

}



function wpcc_settings_section_callback(  ) {

	echo '<h4>Set Settings as follows:</h4>';
	echo '<ul>';
	echo '<li>- With CSS Selectors you can limit the commentable sections.</li>';
	echo '<li>- Custom CSS enables you to overwrite the default styles.</li>';
	// echo '<li>- And anonymous commenting...</li>';
	echo '</ul>';

}


function wpcc_options_page(  ) {

	?>
	<form action='options.php' method='post'>

		<h2>WP Context Comments</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}
// le action

add_action('init', 'wpcc_init', 5, 0);
function wpcc_init() {

	$base_path = plugin_dir_url( __FILE__ );

	$url = home_url(add_query_arg(array()));
	$postid = url_to_postid($url);

	$args = array(
		'post_id' => $postid
	);
	$comments = get_comments($args);
	$options = get_option( 'wpcc_settings' );
	$anon = 'false';

	if($options['wpcc_anon_commenting'] == 1){
		$anon = 'true';
	}

	foreach($comments as $comment):
		$comment->context = get_comment_meta($comment->comment_ID, 'context');
	endforeach;

	wp_enqueue_script('rangy-core', $base_path . 'js/rangy/rangy-core.js');
	wp_enqueue_script('rangy-classapplier', $base_path . 'js/rangy/rangy-classapplier.js');
	wp_enqueue_script('rangy-highlighter', $base_path . 'js/rangy/rangy-highlighter.js');
	wp_enqueue_script('rangy-textrange', $base_path . 'js/rangy/rangy-textrange.js');

	wp_enqueue_script('wpcc', $base_path . 'js/wpcc.js', array('jquery', 'rangy-core'));
	if($postid != 0){
		wp_localize_script( 'wpcc', 'wpccparams', array(
			'postid'    => $postid,
			'comments'  => json_encode($comments),
			'logged_in' => is_user_logged_in(),
			'selectors' => $options['wpcc_css_selectors'],
			'anon' 		=> $anon,
			'admin_url' => admin_url()
		));
	} else {
		wp_localize_script( 'wpcc', 'wpccparams', array(
			'postid' => $postid
		));
	}
	wp_enqueue_style('wpcc', $base_path . 'css/wpcc.css');

	if(strlen($options['wpcc_custom_css']) > 0){
		wp_add_inline_style('wpcc', $options['wpcc_custom_css']);
	}

}

function wpcc_etherify() {

	$valid_id = intval($_POST['id']);
	if(!$valid_id){
		echo 'invalid id';
		wp_die();
	}

	$content = sanitize_text_field($_POST['content']);

	$data = array(
		'comment_post_ID' => $valid_id,
		'comment_content' => $content,
		'user_id' => get_current_user_id()
	);

	$comment_id = wp_new_comment($data);

	if((isset($_POST['context'])) && ($_POST['context'] != '')) {
		$context = sanitize_text_field($_POST['context']);
		add_comment_meta( $comment_id, 'context', $context );
		add_comment_meta( $comment_id, 'type', 'wpcc' );
	}

	wp_notify_postauthor($comment_id);

	echo 'success';
	wp_die();
}

add_action( 'wp_ajax_wpcc_etherify', 'wpcc_etherify' );
add_action( 'wp_ajax_nopriv_wpcc_etherify', 'wpcc_etherify' );

function wpcc_solidify() {

	if((isset( $_POST['id'])) && ($_POST['id'] != '')) {

		$valid_id = intval($_POST['id']);
		if(!$valid_id){
			echo 'invalid id';
			wp_die();
		}

		$args = array(
			'post_id' => $valid_id
		);
		$comments = get_comments($args);

		foreach($comments as $comment):
			$comment->context = get_comment_meta($comment->comment_ID, 'context');
		endforeach;

		echo json_encode($comments);
	}

	wp_die();
}

add_action( 'wp_ajax_wpcc_solidify', 'wpcc_solidify' );
add_action( 'wp_ajax_nopriv_wpcc_solidify', 'wpcc_solidify' );
