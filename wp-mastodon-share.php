<?php

/**
 * Plugin Name: Mastodon Share
 * Plugin URI: https://github.com/kernox/mastoshare-wp
 * Description: Share WordPress posts on a mastodon instance.
 * Version: 1.3
 * Author: Hellexis
 * Author URI: https://github.com/kernox
 * Text Domain: wp-mastodon-share
 * Domain Path: /languages
 */


require_once 'client.php';

class Mastoshare
{
	public function __construct(){
		add_action( 'plugins_loaded', array($this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array($this, 'configuration_page' ) );
		add_action( 'save_post', array($this, 'toot_post' ) );
		add_action( 'admin_notices', array($this, 'admin_notices' ) );
		add_action( 'add_meta_boxes', array($this, 'add_metabox' ) );
		add_action( 'tiny_mce_before_init', array($this, 'tinymce_before_init' ) );
		add_action( 'publish_future_post', array($this, 'toot_scheduled_post') );
	}

	/**
	 * Init
	 *
	 * Plugin initialization
	 *
	 * @return void
	 */
	public function init(){
		$plugin_dir = basename( dirname( __FILE__ ) );
		load_plugin_textdomain( 'wp-mastodon-share', false, $plugin_dir . '/languages' );

		if(isset($_GET['code'])){
			$code = $_GET['code'];
			$client_id = get_option('mastoshare-client-id');
			$client_secret = get_option('mastoshare-client-secret');

			if(!empty($code) && !empty($client_id) && !empty($client_secret))
			{
				echo 'Authentification, please wait ...';
				update_option( 'mastoshare-token', 'nada' );

				$instance = get_option( 'mastoshare-instance' );
				$client = new Client($instance);
				$token = $client->get_bearer_token($client_id, $client_secret, $code, get_admin_url());
				
				if(isset($token->error)){
					print_r($token);
					//TODO: Propper error message 
					update_option(
						'mastoshare-notice',
						serialize(
							array(
								'message' => '<strong>Mastodon Share</strong> : ' . __( "Can't log you in.", 'wp-mastodon-share' ) .
									'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $token->error_description . '</p>',
									'class' => 'error',
								)
							)
						);
						unset($token);
						update_option('mastoshare-token', '');
				}else{
					update_option('mastoshare-client-id', '');
					update_option('mastoshare-client-secret', '');
					update_option('mastoshare-token', $token->access_token);
				}
				$redirect_url = get_admin_url().'options-general.php?page=wp-mastodon-share';
			}
			else
			{
				//Probably hack or bad refresh, redirect to homepage
				$redirect_url = home_url();
			}

			wp_redirect($redirect_url);
			exit;
		}
	}

	/**
	 * Enqueue_scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		global $pagenow;

		$infos = get_plugin_data(__FILE__);

		if ( in_array( $pagenow, [ 'post-new.php', 'post.php' ] ) ) {

			$plugin_url = plugin_dir_url( __FILE__ );
			wp_enqueue_script( 'toot_editor', $plugin_url . 'js/toot_editor.js', array(), $infos['Version'], true );
		}
	}

	/**
	 * Configuration_page
	 *
	 * Add the configuration page menu
	 *
	 * @return void
	 */
	public function configuration_page() {
		add_options_page(
			'Mastodon Share',
			'Mastodon Share',
			'install_plugins',
			'wp-mastodon-share',
			array($this, 'show_configuration_page')
		);
	}

	/**
	 * Show_configuration_page
	 *
	 * Content of the configuration page
	 *
	 * @throws Exception The exception.
	 * @return void
	 */
	public function show_configuration_page() {

		wp_enqueue_style('mastoshare-configuration', plugin_dir_url(__FILE__).'style.css');

		if( isset( $_GET['disconnect'] ) ) {
			update_option( 'mastoshare-token' , '');
		}elseif( isset( $_GET['testToot'] ) ) {
			$this->sendTestToot();
		}

		$token = get_option( 'mastoshare-token' );

		if ( isset( $_POST['save'] ) ) {

			$is_valid_nonce = wp_verify_nonce( $_POST['_wpnonce'], 'mastoshare-configuration' );

			if ( $is_valid_nonce ) {
				$instance = esc_url( $_POST['instance'] );
				$message = stripslashes($_POST['message']);

				$client = new Client($instance);
				$redirect_url = get_admin_url();
				$auth_url = $client->register_app($redirect_url);


				update_option('mastoshare-client-id', $client->get_client_id());
				update_option('mastoshare-client-secret', $client->get_client_secret());

				update_option( 'mastoshare-instance', $instance );
				update_option( 'mastoshare-message', sanitize_textarea_field( $message ) );
				update_option( 'mastoshare-mode', sanitize_text_field( $_POST['mode'] ) );
				update_option( 'mastoshare-toot-size', (int) $_POST['size'] );

				$account = $client->verify_credentials($token);

				if( isset( $account->error ) ){
					echo '<meta http-equiv="refresh" content="0; url=' . $auth_url . '" />';
					echo 'Redirect to '.$instance;
					exit;
				}

				//Inform user that save was successfull
				update_option(
					'mastoshare-notice',
					serialize(
						array(
						'message' => '<strong>Mastodon Share</strong> : ' . __( 'Configuration successfully saved !', 'wp-mastodon-share' ),
						'class' => 'success',
						)
					)
				);
		
				$this->admin_notices();
	

			}
		}

		$instance = get_option( 'mastoshare-instance' );

		if( !empty( $token ) ) {
			$client = new Client($instance);
			$account = $client->verify_credentials($token);
		}

		$message = get_option( 'mastoshare-message', "[title]\n\n[excerpt]\n\n[permalink]\n\n[tags]" );
		$mode = get_option( 'mastoshare-mode', 'public' );
		$toot_size = get_option( 'mastoshare-toot-size', 500 );

		include 'form.tpl.php';
	}

	/**
	 * Toot_post
	 * Post the toot
	 *
	 * @param int $id The post ID.
	 * @return void
	 */
	public function toot_post( $id ) {

		$post = get_post( $id );

		$thumb_url = get_the_post_thumbnail_url($id);

		$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );

		$toot_on_mastodon_option = false;

		if( isset( $_POST['toot_on_mastodon'] ) ) {
			$toot_on_mastodon_option = ( 'on' === $_POST['toot_on_mastodon'] );
		}

		if ( $toot_on_mastodon_option ) {

			$message = stripslashes($_POST['mastoshare_toot']);

			if ( ! empty( $message ) ) {

				//Save the toot, for scheduling
				if($post->post_status == 'future') {
					update_post_meta($id, 'mastoshare-toot', $message);

					if ( $thumb_url ) {

						$thumb_path = str_replace( get_site_url(), get_home_path(), $thumb_url );
						update_post_meta($id, 'mastoshare-toot-thumbnail', $thumb_path);
					}

					update_option(
						'mastoshare-notice',
						serialize(
							array(
								'message' => '<strong>Mastodon Share</strong> : ' . __( 'Toot saved for schedule !', 'wp-mastodon-share' ),
								'class' => 'info',
							)
						)
					);
				} else if($post->post_status !== 'draft') {
					$instance = get_option( 'mastoshare-instance' );
					$access_token = get_option('mastoshare-token');
					$mode = get_option( 'mastoshare-mode', 'public' );

					$client = new Client($instance, $access_token);

					if ( $thumb_url ) {

						$thumb_path = str_replace( get_site_url(), get_home_path(), $thumb_url );
						$attachment = $client->create_attachment( $thumb_path );

						if(is_object($attachment))
						{
							$media = $attachment->id;
						}
					}

					$toot = $client->postStatus($message, $mode, $media);

					update_post_meta( $id, 'mastoshare-post-status', 'off' );

					add_action('admin_notices', 'mastoshare_notice_toot_success');
					if ( isset( $toot->error ) ) {
						update_option(
							'mastoshare-notice',
							serialize(
								array(
									'message' => '<strong>Mastodon Share</strong> : ' . __( 'Sorry, can\'t send toot !', 'wp-mastodon-share' ) .
									'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $toot->error . '</p>',
									'class' => 'error',
								)
							)
						);
					} else {
						update_option(
							'mastoshare-notice',
							serialize(
								array(
									'message' => '<strong>Mastodon Share</strong> : ' . __( 'Toot successfully sent !', 'wp-mastodon-share' ). ' <a href="'.$toot->url.'" target="_blank">'. __('View Toot', 'wp-mastodon-share') .'</a>',
									'class' => 'success',
								)
							)
						);
						//Save the toot url for syndication
						update_post_meta($id, 'mastoshareshare-lastSuccessfullTootURL',$toot->url);
					}
				}

			}
		}
	}

	/**
	 * Toot_scheduled_post
	 * @param  integer $post_id
	 */
	public function toot_scheduled_post($post_id) {

		$instance = get_option( 'mastoshare-instance' );
		$access_token = get_option('mastoshare-token');
		$mode = get_option( 'mastoshare-mode', 'public' );

		$message = get_post_meta($post_id, 'mastoshare-toot', true);

		$thumb_url = get_the_post_thumbnail_url($post_id);
		$thumb_path = get_post_meta($post_id, 'mastoshare-toot-thumbnail', true);

		$client = new Client($instance, $access_token);

		if ( $thumb_url ) {

			$attachment = $client->create_attachment( $thumb_path );

			if(is_object($attachment))
			{
				$media = $attachment->id;
			}
		}

		$toot = $client->postStatus($message, $mode, $media);
	}

	/**
	 * Admin_notices
	 * Show the notice (error or info)
	 *
	 * @return void
	 */
	public function admin_notices() {

		$notice = unserialize( get_option( 'mastoshare-notice' ) );

		if ( is_array( $notice ) ) {
			echo '<div class="notice notice-' . sanitize_html_class( $notice['class'] ) . ' is-dismissible"><p>' . $notice['message'] . '</p></div>';
			update_option( 'mastoshare-notice', null );
		}
	}

	/**
	 * Add_metabox
	 *
	 * @return void
	 */
	public function add_metabox() {
		add_meta_box(
			'mastoshare_metabox',
			__( 'Toot editor', 'wp-mastodon-share' ),
			array($this, 'metabox'),
			['post', 'page'],
			'side',
			'high'
		);
	}

	/**
	 * Metabox
	 *
	 * @param WP_Post $post the current post.
	 * @return void
	 */
	public function metabox( $post ) {

		$id = $post->ID;
		$toot_size = (int) get_option( 'mastoshare-toot-size', 500 );

		$message = get_option( 'mastoshare-message' );


		$status = get_post_meta( $post->ID, 'mastoshare-post-status', true );

		$checked = ( ! $status ) ? 'checked' : '';

		echo '<textarea id="mastoshare_toot" name="mastoshare_toot" maxlength="' . $toot_size . '" style="width:100%; min-height:320px; resize:none">Loading, please wait ...</textarea>'.
		'<textarea id="mastoshare_toot_template" style="display:none">' . $message . '</textarea>' .
		'<p>' . __( 'Chars', 'wp-mastodon-share' ) . ': <span id="toot_current_size">?</span> / <span id="toot_limit_size">?</span></p>';

		echo '<div style="margin: 20px 0;"><input ' . $checked . ' type="checkbox" name="toot_on_mastodon" id="toot_on_mastodon">' .
		'<label for="toot_on_mastodon">' . __( 'Toot on Mastodon', 'wp-mastodon-share' ) . '</label></div>';
	}

	public function tinymce_before_init($init_array){
		$init_array['setup'] = file_get_contents(plugin_dir_path(__FILE__).'/js/tinymce_config.js');
		return $init_array;
	}

	private function sendTestToot(){
		$instance = get_option( 'mastoshare-instance' );
		$access_token = get_option('mastoshare-token');
		$mode = get_option( 'mastoshare-mode', 'public' );

		$client = new Client($instance, $access_token);
		//TODO: Add propper message
		$message=__("This is my first post with mastodon auto share",'wp-mastodon-share');
		$media=null;
		$toot = $client->postStatus($message, $mode, $media);
		
		if ( isset( $toot->error ) ) {
			update_option(
				'mastoshare-notice',
				serialize(
					array(
						'message' => '<strong>Mastodon Share</strong> : ' . __( 'Sorry, can\'t send toot !', 'wp-mastodon-share' ) .
						'<p><strong>' . __( 'Instance message', 'wp-mastodon-share' ) . '</strong> : ' . $toot->error . '</p>',
						'class' => 'error',
					)
				)
			);
		} else {
			update_option(
				'mastoshare-notice',
				serialize(
					array(
						'message' => '<strong>Mastodon Share</strong> : ' . __( 'Toot successfully sent !', 'wp-mastodon-share' ). ' <a href="'.$toot->url.'" target="_blank">'. __('View Toot', 'wp-mastodon-share') .'</a>',
						'class' => 'success',
					)
				)
			);
		}
		$this->admin_notices();
	}
}

$mastoshare = new Mastoshare();
