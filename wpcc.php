<?php

/*

Plugin Name: WP Context Comments
Version: 0.2
Plugin URI: https://github.com/thgie/wpcc
Description: A plug-in to attach a comment to inline text - Medium style.
Author: Adrian Demleitner
Author URI: http://thgie.ch
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

*/

add_action('init', 'wpcc_init', 5, 0);
function wpcc_init() {

	$base_path = plugin_dir_url( __FILE__ );

	global $post;
	$postid = url_to_postid( "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] );

	$args = array(
		'post_id' => $postid
	);
	$comments = get_comments($args);

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
			'admin_url' => admin_url()
		));
	} else {
		wp_localize_script( 'wpcc', 'wpccparams', array(
			'postid' => $postid
		));
	}
	wp_enqueue_style('wpcc', $base_path . 'css/wpcc.css');

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
