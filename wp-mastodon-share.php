<?php

/**
 * Plugin Name: Mastodon Share
 * Plugin URI: https://github.com/kernox/mastoshare-wp
 * Description: Share WordPress posts on a mastodon instance.
 * Version: 0.8
 * Author: Hellexis
 * Author URI: https://github.com/kernox
 * Text Domain: wp-mastodon-share
 * Domain Path: /languages
 */

require_once 'tootophp/autoload.php';

add_action( 'admin_menu', 'mastoshare_configuration_page' );
add_action( 'save_post', 'mastoshare_toot_post' );
add_action( 'admin_notices', 'mastoshare_admin_notices' );
add_action( 'post_submitbox_misc_actions', 'mastoshare_add_publish_meta_options' );
add_action( 'plugins_loaded', 'mastoshare_init' );
add_action( 'add_meta_boxes', 'mastoshare_add_metabox' );
add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );

/**
 * Mastoshare_init
 *
 * Plugin initialization
 *
 * @return void
 */
function mastoshare_init() {
	$plugin_dir = basename( dirname( __FILE__ ) );
	load_plugin_textdomain( 'wp-mastodon-share', false, $plugin_dir . '/languages' );
}

/**
 * Enqueue_scripts
 *
 * @return void
 */
function enqueue_scripts() {

	global $pagenow;

	if ( in_array( $pagenow, [ 'post-new.php', 'post.php' ] ) ) {

		$plugin_url = plugin_dir_url( __FILE__ );
		wp_enqueue_script( 'toot_editor', $plugin_url . 'js/toot_editor.js', [ 'jquery' ], null, true );
	}
}

/**
 * Mastoshare_configuration_page
 *
 * Add the configuration page menu
 *
 * @return void
 */
function mastoshare_configuration_page() {
	add_options_page(
		'Mastodon Share',
		'Mastodon Share',
		'install_plugins',
		'wp-mastodon-share',
		'mastoshare_show_configuration_page'
	);
}

/**
 * Mastoshare_show_configuration_page
 *
 * Content of the configuration page
 *
 * @throws Exception The exception.
 * @return void
 */
function mastoshare_show_configuration_page() {

	if ( isset( $_POST['save'] ) ) {

		$is_valid_nonce = wp_verify_nonce( $_POST['_wpnonce'], 'mastoshare-configuration' );

		if ( $is_valid_nonce ) {
			$instance = get_option( 'mastoshare-instance' );
			$message = $_POST['message'];
			$token = sanitize_key( $_POST['token'] );

			$tooto_php = new TootoPHP\TootoPHP( $instance );
			$app = $tooto_php->registerApp( 'Mastodon Share for WP', 'http://www.github.com/kernox' );

			if ( get_option( 'mastoshare-token' ) !== $token ) {
				$app->registerAccessToken( trim( $token ) );

				// Force the token fetch.
				$profile = $app->getUser();
			}

			update_option( 'mastoshare-message', sanitize_textarea_field( $message ) );
			update_option( 'mastoshare-token', $token );
			update_option( 'mastoshare-mode', sanitize_text_field( $_POST['mode'] ) );
			update_option( 'mastoshare-toot-size', (int) $_POST['size'] );
		}
	}

	$instance = get_option( 'mastoshare-instance' );
	$token = get_option( 'mastoshare-token' );
	$message = get_option( 'mastoshare-message', '[title] - [excerpt] - [permalink]' );
	$mode = get_option( 'mastoshare-mode', 'public' );
	$toot_size = get_option( 'mastoshare-toot-size', 500 );

	if ( isset( $_POST['obtain_key'] ) ) {

		$tootophp_json = plugin_dir_path( __FILE__ ) . 'tootophp/tootophp.json';
		if( file_exists( $tootophp_json ) ) {
			unlink( $tootophp_json );
		}

		$is_valid_nonce = wp_verify_nonce( $_POST['_wpnonce'], 'instance-access-key' );

		if ( $is_valid_nonce ) {
			$instance = esc_url( $_POST['instance'] );
			$instance = parse_url( $instance )['host'];

			update_option( 'mastoshare-instance', $instance );

			$tooto_php = new TootoPHP\TootoPHP( $instance );

			// Setting up your App name and your website !
			$app = $tooto_php->registerApp( 'Mastodon Share for WP', 'http://www.github.com/kernox' );
			if ( $app === false ) {
				throw new Exception( 'Problem during register app' );
			}

			$auth_url = $app->getAuthUrl();
			echo '<script>window.open("' .  $auth_url  . '")</script>';
		}
	}

	include 'form.tpl.php';
}

/**
 * Undocumented function
 *
 * @param WP_Post $post The post.
 * @return void
 */
function mastoshare_add_publish_meta_options( $post ) {

	$status = get_post_meta( $post->ID, 'mastoshare-post-status', true );

	$checked = ( ! $status ) ? 'checked' : '';

	echo '<div class="misc-pub-section misc-pub-section-last">' .
	'<input ' . $checked . ' type="checkbox" name="toot_on_mastodon" id="toot_on_mastodon">' .
	'<label for="toot_on_mastodon">' . __( 'Toot on Mastodon', 'wp-mastodon-share' ) . '</label>' .
	'</div>';
}

/**
 * Mastoshare_toot_post
 * Post the toot
 *
 * @param int $id The post ID.
 * @return void
 */
function mastoshare_toot_post( $id ) {

	$post = get_post( $id );

	$thumb_url = get_the_post_thumbnail_url($id);

	$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );

	$toot_on_mastodon_option = false;

	if( isset( $_POST['toot_on_mastodon'] ) ) {
		$toot_on_mastodon_option = ( 'on' === $_POST['toot_on_mastodon'] );
	}

	if ( 'publish' === $post->post_status && $toot_on_mastodon_option ) {

		$message = stripslashes($_POST['mastoshare_toot']);

		if ( ! empty( $message ) ) {
			$instance = get_option( 'mastoshare-instance' );

			$tooto_php = new TootoPHP\TootoPHP( $instance );
			$app = $tooto_php->registerApp( 'Mastodon Share for WP', 'http://www.github.com/kernox' );

			$mode = get_option( 'mastoshare-mode', 'public' );

			$medias = array();

			if ( $thumb_url ) {

				$thumb_path = str_replace( get_site_url(), get_home_path(), $thumb_url );

				$attachment = $app->createAttachement( $thumb_path );

				$media = $attachment['id'];
			}

			$toot = $app->postStatus( $message, $mode, $media );

			update_post_meta( $post->ID, 'mastoshare-post-status', 'off' );

			if ( isset( $toot['error'] ) ) {
				update_option(
					'mastoshare-notice',
					serialize(
						array(
							'message' => '<strong>Mastodon Share</strong> : ' . __( 'Sorry, can\'t send toot !', 'wp-mastodon-share' ) .
							'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $toot['error'] . '</p>',
							'class' => 'error',
						)
					)
				);
			} else {
				update_option(
					'mastoshare-notice',
					serialize(
						array(
							'message' => '<strong>Mastodon Share</strong> : ' . __( 'Toot successfully sent !', 'wp-mastodon-share' ),
							'class' => 'success',
						)
					)
				);
			}
		}
	}
}

/**
 * Mastoshare_admin_notices
 * Show the notice (error or info)
 *
 * @return void
 */
function mastoshare_admin_notices() {

	$notice = unserialize( get_option( 'mastoshare-notice' ) );

	if ( is_array( $notice ) ) {
		echo '<div class="notice notice-' . sanitize_html_class( $notice['class'] ) . ' is-dismissible"><p>' . $notice['message'] . '</p></div>';
		update_option( 'mastoshare-notice', null );
	}
}

/**
 * Mastoshare_add_metabox
 *
 * @return void
 */
function mastoshare_add_metabox() {
	add_meta_box( 'mastoshare_metabox', __( 'Toot editor', 'wp-mastodon-share' ), 'mastoshare_metabox', ['post', 'page'], 'side', 'low' );
}

/**
 * Mastoshare_metabox
 *
 * @param WP_Post $post the current post.
 * @return void
 */
function mastoshare_metabox( $post ) {

	$id = $post->ID;
	$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );

	$message = get_option( 'mastoshare-message' );

	echo '<textarea id="mastoshare_toot" name="mastoshare_toot" maxlength="' . $toot_size . '" style="width:100%; min-height:320px; resize:none"></textarea>'.
	'<textarea id="mastoshare_toot_template" style="display:none">' . $message . '</textarea>' .
	'<p>' . __( 'Chars', 'wp-mastodon-share' ) . ': <span id="toot_current_size">?</span> / <span id="toot_limit_size">?</p>' .
	'<input type="hidden" id="post_type" value="'.$post->post_type.'">';
}
