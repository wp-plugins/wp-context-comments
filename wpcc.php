<?php

/*

Plugin Name: WP Context Comments
Version: 0.4.6
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
		'wpcc_re_characters',
		__( 'CSS Selectors', 'wordpress' ),
		'wpcc_re_characters_render',
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

function wpcc_re_characters_render(  ) {

	$options = get_option( 'wpcc_settings' );

	if(strlen($options['wpcc_re_characters']) == 0){
		$options['wpcc_re_characters'] = '.:!?';
	}

	?>
	<input type='text' name='wpcc_settings[wpcc_re_characters]' value='<?php echo $options['wpcc_re_characters']; ?>'>
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

add_action( 'comment_form_logged_in_after', 'wpcc_comment_additional_fields' );
add_action( 'comment_form_after_fields', 'wpcc_comment_additional_fields' );

function wpcc_comment_additional_fields () {
	echo '<p id="contextstring" style="max-width: 500px;"></p>';
	echo '<input id="context" name="context" type="hidden"/>';
	echo '<input id="type" name="type" type="hidden" value="wpcc"/>';
}

add_action( 'comment_post', 'wpcc_save_comment_meta_data' );
function wpcc_save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['context'] ) ) && ( $_POST['context'] != '') ){
		$context = wp_filter_nohtml_kses($_POST['context']);
		add_comment_meta( $comment_id, 'context', $context );
	}
	if ( ( isset( $_POST['type'] ) ) && ( $_POST['type'] != '') ){
		$type = wp_filter_nohtml_kses($_POST['type']);
		add_comment_meta( $comment_id, 'type', $type );
	}
}

add_filter( 'comment_text', 'wpcc_modify_comment');
function wpcc_modify_comment( $text ){

  	$plugin_url_path = WP_PLUGIN_URL;

	if( $context = get_comment_meta( get_comment_ID(), 'context', true ) ) {
		$context = '<p class="context-at-comment"><b><i>' . esc_attr( $context ) . '</i></b></p>';
		$text = $context . $text;
	}

    return $text;
}

function wpcc_footer() {
	$base_path = plugin_dir_url( __FILE__ );

	$url = home_url(add_query_arg(array()));
	$postid = url_to_postid($url);

	if($postid != 0){
		$comment_args = array(
			'comment_notes_before' => '',
			'comment_notes_after' => '',

		);
		echo '<div id="add-comment" class="h">';
		comment_form($comment_args, $postid);
		echo '</div>';
		echo '<div id="add-comment-btn" class="h" data-add-comment></div>';
		echo '<div id="view-comment" class="h"><ol class="comment-list"></ol><button id="close-comment">'.__( 'Schliessen', 'wordpress' ).'</button></div>';
	}
}

add_action('wp_footer', 'wpcc_footer');

// le action

add_action('init', 'wpcc_init', 5, 0);
function wpcc_init() {

	$base_path = plugin_dir_url( __FILE__ );

	$url = home_url(add_query_arg(array()));
	$postid = url_to_postid($url);

	$args = array(
		'post_id' => $postid,
		'meta_query' => array(
			'key'   => 'type',
			'value' => 'wpcc'
		)
	);
	$comments = get_comments($args);
	$comments_merged = array();

	$options = get_option( 'wpcc_settings' );
	$anon = 'false';

	if($options['wpcc_anon_commenting'] == 1){
		$anon = 'true';
	}

	if(strlen($options['wpcc_re_characters']) == 0){
		$options['wpcc_re_characters'] = '.:!?';
	}

	foreach($comments as $comment):
		$comment->context = get_comment_meta($comment->comment_ID, 'context');
		$comment->type = get_comment_meta($comment->comment_ID, 'type');
	endforeach;

	foreach($comments as $key => $comment):
		if(strlen($comment->context[0]) == 0){
			continue;
		}

		$k = $comment->context[0];
		$hash = hash('md5', $k);

		if(!isset($comments_merged[$hash])){

			$re_base = preg_quote($k);
			$parts = explode(' ', $re_base);
			$re = '('.implode('\\s*?(?:<\/?[^>]*?>)?\\s*?', $parts).')';

			$comments_merged[$hash] = array(
				'context' => $k,
				're' => $re,
				'count' => 0,
				'ids' => ''
			);
		}

		$comments_merged[$hash]['count']++;

		if(strlen($comments_merged[$hash]['ids']) > 0){
			$comments_merged[$hash]['ids'] .= ','.$comment->comment_ID;
		} else {
			$comments_merged[$hash]['ids'] = $comment->comment_ID;
		}
	endforeach;

	wp_enqueue_script('selecting', $base_path . 'js/selecting.min.js');
	wp_enqueue_script('wpcc', $base_path . 'js/wpcc.js', array('jquery', 'selecting'));

	if($postid != 0){

		wp_localize_script( 'wpcc', 'wpccparams', array(
			'postid'       => $postid,
			'comments'     => json_encode($comments_merged),
			'logged_in'    => is_user_logged_in(),
			'selectors'    => $options['wpcc_css_selectors'],
			're_chars'     => $options['wpcc_re_characters'],
			'anon' 		   => $anon,
			'admin_url'    => admin_url()
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

function wpcc_modify_content($content) {

	$postid = $GLOBALS['post']->ID;

	if ($postid != 0) {

		$args = array(
			'post_id' => $postid
		);
		$comments = get_comments($args);
		$comments_merged = array();

		foreach($comments as $comment):
			$comment->context = get_comment_meta($comment->comment_ID, 'context');
			$comment->type = get_comment_meta($comment->comment_ID, 'type');
		endforeach;

		foreach($comments as $key => $comment){

			if(strlen($comment->context[0]) == 0){
				continue;
			}

			$k = $comment->context[0];

		    if(!isset($comments_merged[$k])){
		        $comments_merged[$k] = array(
		            'count' => 0,
		            'ids' => ''
		        );
		    }

		    $comments_merged[$k]['count']++;

		    if(strlen($comments_merged[$k]['ids']) > 0){
		        $comments_merged[$k]['ids'] .= ','.$comment->comment_ID;
		    } else {
		        $comments_merged[$k]['ids'] = $comment->comment_ID;
		    }
		}

		foreach($comments_merged as $key => $comment){

			$re_base = preg_quote($key);
			$parts = array_filter(preg_split('/[\s\W]/', $re_base));
			$re = '/('.implode('[\\s\\W]*?(?:<\/?[^>]*?>)?[\\s\\W]*?', $parts).')/';

			$content = preg_replace($re, '$1<span data-id="'.$comments_merged[$key]['ids'].'" class="comment c'.$comments_merged[$key]['count'].'"></span>', $content);
		}
	}

	return $content;
}
add_filter( 'the_content', 'wpcc_modify_content' );

// edit the edit screen
add_action( 'add_meta_boxes_comment', 'wpcc_extend_comment_add_meta_box' );
function wpcc_extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Context' ), 'wpcc_extend_comment_meta_box', 'comment', 'normal', 'high' );
}

function wpcc_extend_comment_meta_box ( $comment ) {

    $context = get_comment_meta( $comment->comment_ID, 'context', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );

    ?>

    <p>
        <label for="context"><?php _e( 'Context' ); ?></label>
        <input type="text" name="context" value="<?php echo esc_attr( $context ); ?>" class="widefat" />
    </p>

    <?php
}

add_action( 'edit_comment', 'wpcc_extend_comment_edit_metafields' );

function wpcc_extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

	if ( ( isset( $_POST['context'] ) ) && ( $_POST['context'] != '') ) :
		$context = wp_filter_nohtml_kses($_POST['context']);
		update_comment_meta( $comment_id, 'context', $context );
	else :
		delete_comment_meta( $comment_id, 'context');
	endif;

}
